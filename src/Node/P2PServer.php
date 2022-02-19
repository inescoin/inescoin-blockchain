<?php

namespace Inescoin\Node;

use Inescoin\Entity\Peer as PeerEntity;
use Inescoin\Helper\BlockHelper;
use Inescoin\Helper\PKI;
use Inescoin\Node\Node;
use Inescoin\Node\Packet;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

class P2PServer {

    private $node;

    private $connector;

    private $network ;

    private $peersconfig = [];
    private $localpeerconfig = [];

    private $peersPersistence = [];
    private $peersPersistenceInputStream = [];
    private $peersPersistenceOutputStream = [];
    private $peersPersistenceKeysOutputStream = [];

    private $peersPersistenceMempoolInputStream = [];
    private $peersPercistenceMempoolOutputStream = [];
    private $peersPersistenceKeysMempoolOutputStream = [];

    private $peers = [];
    private $peersInputStream = [];
    private $peersOutputStream = [];
    private $peersKeysOutputStream = [];

    private $limitConnectedPeers = 9;

    private $isSynced = false;

    private $transferLimit = 50;

    public function __construct(Connector $connector, $network = 'MAINNET')
    {
        $this->keys = PKI::newEcKeys();

        $this->connector = $connector;
        $this->network = $network === 'MAINNET'
            ? Packet::NETWORK_MAINNET
            : Packet::NETWORK_TESTNET;
    }

    public function __invoke(ConnectionInterface $connection): void
    {
        if (!$this->_canConnect()) {
            $connection->close();
            return;
        }

        $connection->on('data', function (string $data) use ($connection): void {

            // var_dump('-------------------------------------');
            // var_dump('-------------- data -----------------');
            // var_dump('-------------------------------------');

            $packet = new Packet($data);

            if ($packet->isValid()) {
                switch ($packet->getType()) {

                    case Packet::HELLO_MOON:
                        $this->handleHelloMoon($packet, $connection);
                        break;

                    case Packet::HELLO_MOON_RESPONSE:
                        $this->handleHelloMoonResponse($packet, $connection);
                        break;

                    case Packet::REQUEST_PEERS:
                        $this->handlePeerList($packet, $connection);
                        break;

                    case Packet::REQUEST_PEERS_RESPONSE:
                        $this->handlePeerListResponse($packet, $connection);
                        break;

                    case Packet::REQUEST_PEER_BLOCK_SYNC:
                        $this->handlePeerBlockSync($packet, $connection);
                        break;

                    case Packet::REQUEST_PEER_BLOCK_SYNC_RESPONSE:
                        $this->handlePeerBlockSyncResponse($packet, $connection);
                        break;

                    // case Packet::BROADCAST_FOR_PERSISTENCE:
                    //     $this->handleBroadcastForPersistence($packet, $connection);
                    //     break;

                    case Packet::BROADCAST_TOP_BLOCK:
                        $this->handleBroadcastTopBlock($packet, $connection);
                        break;

                    case Packet::BROADCAST_TOP_BLOCK_RESPONSE:
                        $this->handleBroadcastTopBlockResponse($packet, $connection);
                        break;

                    case Packet::BROADCAST_MEMORY_TRANSACTION_POOL:
                        $this->handleBroadcastMemoryPoolTransactionResponse($packet, $connection);
                        break;

                    case Packet::BROADCAST_MEMORY_MESSAGE_POOL:
                        $this->handleBroadcastMemoryPoolMessageResponse($packet, $connection);
                        break;

                    default:
                        break;
                }
            } else {
                $connection->close();
            }

            // if (!$connection->getRemoteAddress()) {
            //     $connection->close();
            // }
        });


        $connection->on('close', function () use ($connection): void {
            var_dump('Connection closed: ' . $connection->getRemoteAddress());
            if (isset($this->peers[$connection->getRemoteAddress()])) {
                unset($this->peers[$connection->getRemoteAddress()]);
            }

            if (isset($this->peersPersistence[$connection->getRemoteAddress()])) {
                unset($this->peersPersistence[$connection->getRemoteAddress()]);
            }

            if (count($this->peers) + count($this->peersPersistence) == 0) {
                $this->restartNode();
            }
        });
    }

    public function attachnode(node $node, $peersconfig, $localpeerconfig): void
    {
        var_dump('---------------- attachnode ---------------------');

        if ($this->node !== null) {
            throw new RuntimeException('Node already attached to p2pServer');
        }

        $this->node = $node;
        $this->peersconfig = $peersconfig;
        $this->localpeerconfig = $localpeerconfig;

        $remoteAddresses = $this->node->getBlockchainService()->getBlockchainManager()->getPeer()->getRemoteAddresses();

        if (null !== $remoteAddresses) {
            foreach ($remoteAddresses as $peer) {
                if ($localpeerconfig['host'] !== $peer['host'] || $localpeerconfig['port'] !== $peer['port']) {
                    $this->connect($peer['host'], (int) $peer['port']);
                }
            }
        }

        foreach ($peersconfig as $peer) {
            if ($localpeerconfig['host'] !== $peer['host'] || $localpeerconfig['port'] !== $peer['port']) {
                $this->connect($peer['host'], (int) $peer['port']);
            }
        }
    }

    public function restartNode(): void
    {
        var_dump('---------------- restartNode ---------------------');

        $remoteAddresses = $this->node->getBlockchainService()->getBlockchainManager()->getPeer()->getRemoteAddresses();

        foreach ($remoteAddresses as $peer) {
            if ($this->localpeerconfig['host'] !== $peer['host'] || $this->localpeerconfig['port'] !== $peer['port']) {
                $this->connect($peer['host'], (int) $peer['port']);
            }
        }
    }

    public function connect(string $host, int $port): void
    {
        var_dump("---------------- connect: $host:$port ---------------------");

        $remoteAddress = $host.':'. $port;
        if (isset($this->peers[$remoteAddress])) {
            var_dump($remoteAddress . ' already in peers');
            return;
        }

        if (count($this->peers) >= $this->limitConnectedPeers) {
            var_dump($remoteAddress . ' limitConnectedPeers execeded');
            return;
        }

        $CI = $this;
        $cf = function (ConnectionInterface $connection) use ($host, $port, $CI)   {
            $remoteAddress = $connection->getRemoteAddress();

            // if (isset($CI->peers[$remoteAddress])) {
            //     $connection->close();
            //     return;
            // }

            $this($connection);

            var_dump('===================== ======================');
            var_dump('================== connect =================');
            var_dump('===================== ======================');

            $this->peers[$remoteAddress] = new Peer($connection);
            $this->peers[$remoteAddress]->setLocalConfig($host.':'. $port);

            $this->peers[$remoteAddress]
                ->send(Packet::prepare(
                    $this->network,
                    Packet::HELLO_MOON,
                    $this->getHelloMoonMessage(),
                    $this->getPublicKey()
                ));


        };

        $cf->bindTo($this, $this);

        $this->connector->connect(sprintf('%s:%s', $host, $port))->then($cf);
    }

    // public function connectForSynchro($peer): void
    // {
    //     var_dump('---------------- connectForSynchro ---------------------');

    //     if (isset($this->peers[$peer['host'].':'. $peer['port']])) {
    //         return;
    //     }

    //     if (!$this->_canConnect()) {
    //         return;
    //     }

    //     $CI = $this;
    //     $cf = function (ConnectionInterface $connection) use ($peer, $CI): void   {
    //         $remoteAddress = $connection->getRemoteAddress();

    //         if ($CI->localpeerconfig['host'] === $peer['host'] && $CI->localpeerconfig['port'] === $peer['port']) {
    //             $connection->close();
    //             return;
    //         }

    //         if (isset($CI->peers[$remoteAddress])) {
    //             $connection->close();
    //             return;
    //         }

    //         $this($connection);

    //         $CI->peers[$remoteAddress] = new Peer($connection);
    //         $CI->peers[$remoteAddress]->setPublicKey($peer['publicKey']);

    //         $CI->peers[$remoteAddress]->setLocalConfig($peer['host'].':'. $peer['port']);

    //         $CI->peers[$remoteAddress]
    //             ->send(Packet::prepare(
    //                 $CI->network,
    //                 Packet::REQUEST_PEER_BLOCK_SYNC,
    //                 $CI->getHelloMoonMessage(),
    //                 $CI->getPublicKey()
    //             ));
    //     };

    //     $cf->bindTo($this, $this);

    //     $this->connector->connect(sprintf('%s:%s', $peer['host'], $peer['port']))->then($cf);
    // }

    // public function connectForPersistence($peer): void
    // {
    //     var_dump('---------------- connectForPersistence ---------------------');

    //     if (isset($this->peersPersistence[$peer['host'].':'. $peer['port']])) {
    //         return;
    //     }

    //     if (!$this->_canConnectPersistence()) {
    //         return;
    //     }

    //     $CI = $this;
    //     $cf = function (ConnectionInterface $connection) use ($peer, $CI): void   {
    //         $remoteAddress = $connection->getRemoteAddress();

    //         if ($CI->localpeerconfig['host'] === $peer['host'] && $CI->localpeerconfig['port'] === $peer['port']) {
    //             $connection->close();
    //             return;
    //         }

    //         if (isset($CI->peersPersistence[$remoteAddress])) {
    //             $connection->close();
    //             return;
    //         }

    //         $this($connection);

    //         if (isset($this->peers[$remoteAddress])) {
    //             unset($this->peers[$remoteAddress]);
    //         }

    //         $CI->peersPersistence[$remoteAddress] = new Peer($connection);
    //         $CI->peersPersistence[$remoteAddress]->setPublicKey($peer['publicKey']);
    //         $CI->peersPersistence[$remoteAddress]->setLocalConfig($peer['host'].':'. $peer['port']);

    //         $CI->peersPersistence[$remoteAddress]
    //             ->send(Packet::prepare(
    //                 $this->network,
    //                 Packet::BROADCAST_FOR_PERSISTENCE,
    //                 $this->getHelloMoonMessage(),
    //                 $this->getPublicKey()
    //             ));
    //     };

    //     $cf->bindTo($this, $this);

    //     $this->connector->connect(sprintf('%s:%s', $peer['host'], $peer['port']))->then($cf);
    // }

    public function handleHelloMoon($packet, ConnectionInterface $connection)
    {
        var_dump('---------------- handleHelloMoon ---------------------');

        $peerInfo = $packet->getBody();
        $remoteAddress = $connection->getRemoteAddress();
        $connectedPeers = array_merge($this->getPeersPersistence(), $this->getPeers());

        if (!empty($peerInfo)) {
            $this->savePeer($peerInfo);
            $this->initPeer($connection, $peerInfo);
        }

        $connection->write(Packet::prepare(
            $this->network,
            Packet::HELLO_MOON_RESPONSE,
            $this->getHelloMoonMessage(),
            $this->getPublicKey()
        ));
    }

    public function handleHelloMoonResponse($packet, ConnectionInterface $connection)
    {
        var_dump('---------------- handleHelloMoonResponse ---------------------');

        $remoteAddress = $connection->getRemoteAddress();

        $peerInfo = $packet->getBody();

        $connectedPeers = array_merge($this->getPeersPersistence(), $this->getPeers());

        if (!empty($peerInfo)) {
            $this->savePeer($peerInfo);
            $this->initPeer($connection, $peerInfo);
        }

        $this->write($connection, Packet::REQUEST_PEERS, [
            'peers' => $this->node->getBlockchainService()->getBlockchainManager()->getPeer()->getRemoteAddresses()
        ]);
    }

    public function handlePeerList($packet, ConnectionInterface $connection)
    {
        var_dump('---------------- handlePeerList ---------------------');

        $remoteAddress = $connection->getRemoteAddress();

        if (isset($this->peersOutputStream[$remoteAddress]) && !empty($this->peersOutputStream[$remoteAddress])) {
            $this->write($connection, Packet::REQUEST_PEERS, []);
            return;
        }

        if ($packet->onlyOne()) {
            $body = $packet->getBody();
            $decrypted = $this->decrypt($body, $packet->getPublicKey());

            if ($decrypted) {
                foreach ($decrypted['peers'] as $index => $peer) {
                    $this->savePeer($peer);
                }

                $data = [
                    'peers' => $this->node->getBlockchainService()->getBlockchainManager()->getPeer()->getRemoteAddresses()
                ];

                $this->write($connection, Packet::REQUEST_PEERS_RESPONSE, $data);
            } else {
                $connection->close();
            }

            $this->peersInputStream[$remoteAddress] = [];

        } else if($packet->isFirst()) {
            $this->peersInputStream[$remoteAddress] = [];
            $this->peersInputStream[$remoteAddress][] = $packet;

            $connection->write(Packet::prepare(
                $this->network,
                Packet::REQUEST_PEERS,
                [],
                $this->getPublicKey()
            ));
        } else if($packet->isLast()) {

            $buffer = '';
            foreach ($this->peersInputStream[$remoteAddress] as $packetCache) {
                $buffer .= $packetCache->getBody();
            }

            $buffer .= $packet->getBody();

            $decrypted = $this->decrypt($buffer, $packet->getPublicKey());

            if ($decrypted) {
                foreach ($decrypted['peers'] as $index => $peer) {
                    // $this->node->getBlockchainService()->es->peerService()->index($index, $peer);
                    $this->savePeer($peer);
               }

                $this->write($connection, Packet::REQUEST_PEERS_RESPONSE, [
                    'peers' //$this->node->getBlockchainService()->getBlockchainManager()->getPeer()->getRemoteAddresses()
                ]);
            } else {
                $connection->close();
            }

            $this->peersInputStream[$remoteAddress] = [];
        } else {
            $this->peersInputStream[$remoteAddress][] = $packet;
            $connection->write(Packet::prepare(
                $this->network,
                Packet::REQUEST_PEERS,
                [],
                $this->getPublicKey()
            ));
        }
    }

    public function handlePeerListResponse($packet, ConnectionInterface $connection)
    {
        var_dump('---------------- handlePeerListResponse ---------------------');

        $remoteAddress = $connection->getRemoteAddress();
        // $connection->close();
        if (!isset($this->peers[$remoteAddress])) {
            var_dump('P2pServer::handlePeerListResponse -> error -> peers [' . $remoteAddress . '] not isset');
            return;
        }

        if (isset($this->peersOutputStream[$remoteAddress]) && !empty($this->peersOutputStream[$remoteAddress])) {
            var_dump('P2pServer::handlePeerListResponse -> error -> peersOutputStream [' . $remoteAddress . '] not isset');
            $this->write($connection, Packet::REQUEST_PEERS_RESPONSE, []);
            return;
        }

        if ($packet->onlyOne()) {
            var_dump('P2pServer::handlePeerListResponse -> onlyOne');
            $body = $packet->getBody();
            $decrypted = $this->decrypt($body, $packet->getPublicKey());

            if ($decrypted) {
                foreach ($decrypted['peers'] as $index => $peer) {
                    $this->savePeer($peer);
                }

                // $connection->close();
                $this->_startBlockchainSynchro($connection);
            }

            unset($this->peersInputStream[$remoteAddress]);

        } else if($packet->isFirst()) {
            var_dump('P2pServer::handlePeerListResponse -> isFirst');
            $this->peersInputStream[$remoteAddress] = [];
            $this->peersInputStream[$remoteAddress][] = $packet;

            $connection->write(Packet::prepare(
                $this->network,
                Packet::REQUEST_PEERS_RESPONSE,
                [],
                $this->getPublicKey()
            ));
        } else if($packet->isLast()) {
            var_dump('P2pServer::handlePeerListResponse -> isLast');

            $buffer = '';
            foreach ($this->peersInputStream[$remoteAddress] as $packetCache) {
                $buffer .= $packetCache->getBody();
            }

            $buffer .= $packet->getBody();

            $decrypted = $this->decrypt($buffer, $packet->getPublicKey());

            if ($decrypted) {
                foreach ($decrypted['peers'] as $index => $peer) {
                    // $this->node->getBlockchainService()->es->peerService()->index($index, $peer);
                    $this->savePeer($peer);
                }

                // $connection->close();

                $this->_startBlockchainSynchro($connection);
            }

            $this->peersInputStream[$remoteAddress] = [];
        } else {
            var_dump('P2pServer::handlePeerListResponse -> xxx');
            $this->peersInputStream[$remoteAddress][] = $packet;
            $connection->write(Packet::prepare(
                $this->network,
                Packet::REQUEST_PEERS_RESPONSE,
                [],
                $this->getPublicKey()
            ));
        }
    }

    public function handlePeerBlockSync($packet, ConnectionInterface $connection)
    {
        // var_dump('---------------- handlePeerBlockSync ---------------------');

        $remoteAddress = $connection->getRemoteAddress();

        if (isset($this->peersOutputStream[$remoteAddress]) && !empty($this->peersOutputStream[$remoteAddress])) {
            $this->write($connection, Packet::REQUEST_PEER_BLOCK_SYNC_RESPONSE, []);
            return;
        }

        $peerInfo = $packet->getBody();

        if (!empty($peerInfo)) {
            $this->savePeer($peerInfo);
            $this->initPeer($connection, $peerInfo);
        }

        if (!array_key_exists('height', $peerInfo)) {
            $connection->close();
            return;
        }

        $blockHeight = (int) $peerInfo['height'] + 1;

        $topHeight = $this->node->getBlockchainService()->getTopHeight();

        if ($topHeight === $peerInfo['height']) {
            var_dump('[P2PServer::handlePeerBlockSync] [Sync:Ok] : peer[' . $remoteAddress . '] height at ' . $peerInfo['height'] . ' | local height -> '.$topHeight);
            $this->switchPeerToPersistence($connection);
            return;
        }

        if ($blockHeight > $topHeight) {
            if ($blockHeight > $topHeight + 1) {
                $connection->write(Packet::prepare(
                    $this->network,
                    Packet::REQUEST_PEER_BLOCK_SYNC,
                    $this->getHelloMoonMessage(),
                    $this->getPublicKey()
                ));
            }

            return;
        }

        $toBlock = $blockHeight + $this->transferLimit;
        if ($toBlock > $topHeight) {
            $toBlock = $topHeight;
        }

        var_dump('[P2PServer::handlePeerBlockSync] [Sync:output] : peer[' . $remoteAddress . '] from ' . $blockHeight . ' to ' . $toBlock . ' | local height -> '.$topHeight);
        $blocks = $this->node->getBlockchainService()->getBlockchainManager()->getBlock()->getCompressed($blockHeight, $this->transferLimit);

        $this->write($connection, Packet::REQUEST_PEER_BLOCK_SYNC_RESPONSE, [
            'blocks' => $blocks
        ]);
    }

    public function handlePeerBlockSyncResponse($packet, ConnectionInterface $connection)
    {
        // var_dump('---------------- handlePeerBlockSyncResponse ---------------------');

        $remoteAddress = $connection->getRemoteAddress();

        if ($packet->onlyOne()) {
            $body = $packet->getBody();
            $decrypted = $this->decrypt($body, $packet->getPublicKey());

            if ($decrypted) {
                $blocks = [];

                foreach ($decrypted['blocks'] as $tmpBlock) {
                    if ($newBlock = BlockHelper::decompress($tmpBlock)) {
                        $blocks[] = BlockHelper::fromArrayToBlockModel($newBlock);
                    }
                }

                var_dump('[P2PServer::handlePeerBlockSyncResponse] [Sync:input] : peer[' . $remoteAddress . '] from ' . $blocks[0]->getHeight() . ' to ' . $blocks[count($blocks) - 1]->getHeight());

                if (BlockHelper::extractBlock($blocks, $this->node->getBlockchainService()->getPrefix()) && isset($this->peers[$remoteAddress])) {
                    $this->peers[$remoteAddress]
                        ->send(Packet::prepare(
                            $this->network,
                            Packet::REQUEST_PEER_BLOCK_SYNC,
                            $this->getHelloMoonMessage(),
                            $this->getPublicKey()
                        ));
                } else {
                    $connection->close();
                }

                if (isset($this->peers[$remoteAddress])) {
                    $this->peers[$remoteAddress]->setTopHeight($blocks[count($blocks) - 1]->getHeight());
                }
            }

            $this->peersInputStream[$remoteAddress] = [];

        } else if($packet->isFirst()) {
            $this->peersInputStream[$remoteAddress] = [];
            $this->peersInputStream[$remoteAddress][] = $packet;

            $connection->write(Packet::prepare(
                $this->network,
                Packet::REQUEST_PEER_BLOCK_SYNC,
                [],
                $this->getPublicKey()
            ));
        } else if($packet->isLast()) {
            $buffer = '';
            foreach ($this->peersInputStream[$remoteAddress] as $packetCache) {
                $buffer .= $packetCache->getBody();
            }

            $buffer .= $packet->getBody();

            $decrypted = $this->decrypt($buffer, $packet->getPublicKey());

            if ($decrypted) {
                $blocks = [];
                $lastHeight = 0;
                foreach ($decrypted['blocks'] as $tmpBlock) {
                    if ($newBlock = BlockHelper::decompress($tmpBlock)) {
                        $blocks[] = BlockHelper::fromArrayToBlockModel($newBlock);
                        $lastHeight = $newBlock['height'];
                    }
                }

                var_dump('[P2PServer::handlePeerBlockSyncResponse] [Sync:input] : peer[' . $remoteAddress . '] from ' . $blocks[0]->getHeight() . ' to ' . $blocks[count($blocks) - 1]->getHeight());
                if (BlockHelper::extractBlock($blocks, $this->node->getBlockchainService()->getPrefix())) {
                    if ($this->node->getBlockchainService()->getTopKnowHeight() === $lastHeight) {
                        $this->_startPersitencePeers($remoteAddress, $connection);
                        $this->restartNode();
                    } else {
                        if (isset($this->peers[$remoteAddress])) {
                            $this->peers[$remoteAddress]
                                ->send(Packet::prepare(
                                    $this->network,
                                    Packet::REQUEST_PEER_BLOCK_SYNC,
                                    $this->getHelloMoonMessage(),
                                    $this->getPublicKey()
                                ));
                        } else if (isset($this->peersPersistence[$remoteAddress])) {
                            $this->peersPersistence[$remoteAddress]
                                ->send(Packet::prepare(
                                    $this->network,
                                    Packet::REQUEST_PEER_BLOCK_SYNC,
                                    $this->getHelloMoonMessage(),
                                    $this->getPublicKey()
                                ));
                        } else {
                            $connection->close();
                        }

                        $this->peers[$remoteAddress]->setTopHeight($blocks[count($blocks) - 1]->getHeight());
                    }
                } else {
                    $connection->close();
                }
            } else {
                $connection->write(Packet::prepare(
                    $this->network,
                    Packet::REQUEST_PEER_BLOCK_SYNC,
                    [],
                    $this->getPublicKey()
                ));
            }

            $this->peersInputStream[$remoteAddress] = [];
        } else {
            $this->peersInputStream[$remoteAddress][] = $packet;
            $connection->write(Packet::prepare(
                $this->network,
                Packet::REQUEST_PEER_BLOCK_SYNC,
                [],
                $this->getPublicKey()
            ));

            if (count($this->peersInputStream[$remoteAddress]) > 1000) {
                $this->peersInputStream[$remoteAddress] = [];
            }
        }
    }

    // public function handleBroadcastForPersistence($packet, ConnectionInterface $connection)
    // {
    //     var_dump('---------------- handleBroadcastForPersistence ---------------------');

    //     $remoteAddress = $connection->getRemoteAddress();

    //     if (isset($this->peersOutputStream[$remoteAddress]) && !empty($this->peersOutputStream[$remoteAddress])) {
    //         $this->write($connection, Packet::REQUEST_PEER_BLOCK_SYNC_RESPONSE, []);
    //         return;
    //     }

    //     $peerInfo = $packet->getBody();

    //     if (!empty($peerInfo)) {
    //         // $this->node->getBlockchainService()->es->peerService()->save($peerInfo);
    //         $this->savePeer($peerInfo);
    //         $this->initPeer($connection, $peerInfo);
    //     }

    //     // $connection->close();

    //     $blockHeight = (int) $peerInfo['height'] + 1;

    //     $topHeight = $this->node->getBlockchainService()->getTopHeight();

    //     if ($blockHeight === $topHeight) {
    //         var_dump('[P2PServer::handleBroadcastForPersistence] [Sync:Ok] : peer[' . $remoteAddress . '] height at ' . $peerInfo['height'] . ' | local height at '.$topHeight);
    //     } else if ($blockHeight > $topHeight) {
    //         $connection->write(Packet::prepare(
    //             $this->network,
    //             Packet::REQUEST_PEER_BLOCK_SYNC,
    //             $this->getHelloMoonMessage(),
    //             $this->getPublicKey()
    //         ));
    //     }
    // }

    public function handleBroadcastTopBlock($packet, ConnectionInterface $connection)
    {
        var_dump('---------------- handleBroadcastTopBlock ---------------------');

        $remoteAddress = $connection->getRemoteAddress();

        if (isset($this->peersPercistenceOutputStream[$remoteAddress]) && !empty($this->peersPercistenceOutputStream[$remoteAddress])) {
            $this->write($connection, Packet::REQUEST_PEER_BLOCK_SYNC, []);
            return;
        }

        if ($packet->onlyOne()) {

            $body = $packet->getBody();
            $decrypted = $this->decrypt($body, $packet->getPublicKey());

            if ($decrypted) {
                $blocks = [];
                foreach ($decrypted as $tmpBlock) {
                    if ($newBlock = BlockHelper::decompress($tmpBlock)) {
                        $blocks[] = BlockHelper::fromArrayToBlockModel($newBlock);
                    }
                }

                if (!BlockHelper::extractBlock($blocks, $this->node->getBlockchainService()->getPrefix())) {
                    $connection->write(Packet::prepare(
                        $this->network,
                        Packet::REQUEST_PEER_BLOCK_SYNC,
                        $this->getHelloMoonMessage(),
                        $this->getPublicKey()
                    ));
                }
            }

            $this->peersPersitenceInputStream[$remoteAddress] = [];

        } else if($packet->isFirst()) {

            $this->peersPersitenceInputStream[$remoteAddress] = [];
            $this->peersPersitenceInputStream[$remoteAddress][] = $packet;

            $connection->write(Packet::prepare(
                $this->network,
                Packet::BROADCAST_TOP_BLOCK_RESPONSE,
                [],
                $this->getPublicKey()
            ));

        } else if($packet->isLast()) {

            $buffer = '';
            foreach ($this->peersPersitenceInputStream[$remoteAddress] as $packetCache) {
                $buffer .= $packetCache->getBody();
            }

            $buffer .= $packet->getBody();

            $decrypted = $this->decrypt($buffer, $packet->getPublicKey());

            if ($decrypted) {
                $blocks = [];
                $lastHeight = 0;
                foreach ($decrypted as $tmpBlock) {
                    if ($newBlock = BlockHelper::decompress($tmpBlock)) {
                        $blocks[] = BlockHelper::fromArrayToBlockModel($newBlock);
                        $lastHeight = $newBlock['height'];
                    }
                }

                if (!BlockHelper::extractBlock($blocks, $this->node->getBlockchainService()->getPrefix())) {
                    $connection->write(Packet::prepare(
                        $this->network,
                        Packet::REQUEST_PEER_BLOCK_SYNC,
                        $this->getHelloMoonMessage(),
                        $this->getPublicKey()
                    ));
                }
            }

            $this->peersPersitenceInputStream[$remoteAddress] = [];
        } else {
            $this->peersPersitenceInputStream[$remoteAddress][] = $packet;
            $connection->write(Packet::prepare(
                $this->network,
                Packet::BROADCAST_TOP_BLOCK_RESPONSE,
                [],
                $this->getPublicKey()
            ));
        }
    }

    public function handleBroadcastTopBlockResponse($packet, ConnectionInterface $connection)
    {
        var_dump('---------------- handleBroadcastTopBlockResponse ---------------------');

        $remoteAddress = $connection->getRemoteAddress();

        if (isset($this->peersOutputStream[$remoteAddress]) && !empty($this->peersOutputStream[$remoteAddress])) {
            $this->write($connection, Packet::BROADCAST_TOP_BLOCK, []);
            return;
        }
    }

    public function handleBroadcastMemoryPoolTransactionResponse($packet, ConnectionInterface $connection)
    {
        var_dump('---------------- handleBroadcastMemoryPoolTransactionResponse ---------------------');

        $body = $packet->getBody();
        $decrypted = $this->decrypt($body, $packet->getPublicKey());

        if ($decrypted) {
            if (is_array($decrypted)) {
                $decrypted = $decrypted[0];
            }

            $transactionPool = $this->node->getBlockchainService()->push($decrypted);
            if (!isset($transactionPool['error'])) {
                $this->broadcastMemoryPool($decrypted);
            }
        }
    }

    public function handleBroadcastMemoryPoolMessageResponse($packet, ConnectionInterface $connection)
    {
        var_dump('---------------- handleBroadcastMemoryPoolMessageResponse ---------------------');

        $body = $packet->getBody();
        $decrypted = $this->decrypt($body, $packet->getPublicKey());

        if ($decrypted) {
            if (is_array($decrypted)) {
                $decrypted = $decrypted[0];
            }

            $transactionPool = $this->node->getBlockchainService()->pushMessage($decrypted);
            if (!isset($transactionPool['error'])) {
                $this->broadcastMemoryMessagePool($decrypted);
            }
        }
    }

    public function getPeers(): array
    {
        $peers = [];

        foreach ($this->peers as $peer) {
            if (!in_array($peer->getLocalConfig(), $peers)) {
                $peers[] = $peer->getLocalConfig();
            }
        }

        return $peers;
    }

    public function getPeersPersistence(): array
    {
        $peers = [];

        foreach ($this->peersPersistence as $peer) {
            if (!in_array($peer->getLocalConfig(), $peers)) {
                $peers[] = $peer->getLocalConfig();
            }
        }

        return $peers;
    }

    public function getPublicKey() {
        return $this->keys['publicKey'];
    }

    public function getPrivateKey() {
        return $this->keys['privateKey'];
    }

    public function getKeys() {
        return $this->keys;
    }

    private function getHelloMoonMessage()
    {
        return [
            'height' => $this->node->getBlockchainService()->getTopHeight(),
            'topHeight' => $this->node->getBlockchainService()->getTopHeight(),
            'publicKey' => $this->getPublicKey(),
            'localpeerconfig' => $this->localpeerconfig,
            'remoteAddress' => $this->localpeerconfig['host'] . ':' .  $this->localpeerconfig['port'],
            'host' => $this->localpeerconfig['host'],
            'port' => $this->localpeerconfig['port'],
            'rpcHost' => $this->localpeerconfig['rpcHost'],
            'rpcPort' => $this->localpeerconfig['rpcPort'],
            'peersCount' => count($this->peers),
            'peersPersistence' => $this->getPeersPersistence(),
            'peers' => $this->getPeers(),
            'lastSeen' => (new \DateTimeImmutable())->getTimestamp(),
            'topCumulativeDifficulty' => $this->node->getBlockchainService()->getTopCumulativeDifficulty(),
            'peersOutputStream' => count($this->peersOutputStream),
            'peersInputStream' => count($this->peersInputStream),
            'timestamp' => (new \DateTimeImmutable())->getTimestamp()
        ];
    }

    public function broadcastMinedBlock($block)
    {
        $blocks = [BlockHelper::compress($block)];

        var_dump(' ---------------------------- ' . count($this->peersPersistence) . ' ----------------------------');

        foreach ($this->peersPersistence as $peer) {
            $this->writePersitence($peer->getConnection(), Packet::BROADCAST_TOP_BLOCK, $blocks);
        }
    }

    public function broadcastMemoryPool($data)
    {
        foreach ($this->peersPersistence as $peer) {
            $this->writePersitenceMempool($peer->getConnection(), Packet::BROADCAST_MEMORY_TRANSACTION_POOL, $data);
        }
    }

    public function broadcastMemoryMessagePool($data)
    {
        foreach ($this->peersPersistence as $peer) {
            $this->writePersitenceMempool($peer->getConnection(), Packet::BROADCAST_MEMORY_MESSAGE_POOL, $data);
        }
    }

    public function closeAllConnections()
    {
        foreach ($this->peers as $peer) {
            $peer->close();
        }
    }

    public function encrypt($message, $publicKey)
    {
        if (is_object($message)) {
            $message = [$message];
        }

        if (is_array($message)) {
            $message = serialize($message);
        }

        $encrypted = PKI::ecEncrypt($message, $this->getPrivateKey(), $publicKey);

        return $encrypted;
    }

    public function decrypt($message, $b64PublicKey)
    {
        try {
            $decrypted = PKI::ecDecrypt($message, $this->getPrivateKey(), $b64PublicKey);
        } catch (\Exception $e) {
            return '';
        }

        return @unserialize($decrypted);
    }

    public function write($connection, $type, $message)
    {
        $this->_write($connection, $type, $message);
    }

    public function writePersitence($connection, $type, $message)
    {
        $this->_write($connection, $type, $message, 'peersPercistenceOutputStream', 'peersPersistenceKeysOutputStream', 'peersPersistence');
    }

    public function writePersitenceMempool($connection, $type, $message)
    {
        $this->_write($connection, $type, $message, 'peersPercistenceMempoolOutputStream', 'peersPersistenceKeysMempoolOutputStream', 'peersPersistence');
    }

    private function _write($connection, $type, $message, $property = 'peersOutputStream', $propertyKeys = 'peersPersistenceKeysOutputStream', $group = 'peers')
    {
        $remoteAddress = $connection->getRemoteAddress();

        if (!isset($this->{$group}[$remoteAddress])) {
            $connection->close();
        }

        if (empty($remoteAddress) && !isset($this->{$property}[$remoteAddress]) || empty($this->{$property}[$remoteAddress])) {

            $publicKey = isset($this->peers[$remoteAddress])
                ? $this->peers[$remoteAddress]->getPublicKey()
                : '';

            if (empty($publicKey)) {
                $publicKey = isset($this->peersPersistence[$remoteAddress])
                    ? $this->peersPersistence[$remoteAddress]->getPublicKey()
                    : '';
            }

            if (empty($publicKey)) {
                return;
            }

            $encryptedMessage = $this->encrypt(
                $message,
                $publicKey
            );

            $maxLen = 6000;
            $len = strlen($encryptedMessage);
            $page = ceil($len / $maxLen);

            if($len > $maxLen) {
                $z = 1;
                for ($i = 0; $i < $len; $i += $maxLen) {
                    $pos = '' . $this->_toPosition($z) . '' . $this->_toPosition($page);
                    $this->{$property}[$remoteAddress][$pos] = substr($encryptedMessage, $i, $maxLen);
                    $z++;
                }
            } else {
                $this->{$property}[$remoteAddress]['0000100001'] = $encryptedMessage;
            }

            $this->{$propertyKeys}[$remoteAddress] = array_keys($this->{$property}[$remoteAddress]);
        }

        if (!isset($this->{$property}[$remoteAddress])) {
            return;
        }

        $position = array_shift($this->{$propertyKeys}[$remoteAddress]);
        $stream = isset($this->{$property}[$remoteAddress][$position])
            ? $this->{$property}[$remoteAddress][$position]
            : '';

        if (isset($this->{$property}[$remoteAddress][$position])) {
            unset($this->{$property}[$remoteAddress][$position]);
        }

        $connection->write(Packet::prepare(
            $this->network,
            $type,
            $stream,
            $this->getPublicKey(),
            $position
        ));
    }

    private function _startBlockchainSynchro(ConnectionInterface $connection)
    {
        var_dump('----------- _startBlockchainSynchro ------------');

        $remoteAddress = $connection->getRemoteAddress();
        $topHeight = $this->node->getBlockchainService()->getTopHeight();
        $topPeerHeight = $this->peers[$remoteAddress]->getTopHeight();

        if ($topHeight === $topPeerHeight) {
            var_dump('[P2PServer::_startBlockchainSynchro] [Sync:Ok] : peer[' . $remoteAddress . '] height at ' . $topPeerHeight . ' | local height -> '.$topHeight);
            $this->_startPersitencePeers($remoteAddress, $connection);
            // $connection->close();
            //return;
        } else {
            $this->peers[$connection->getRemoteAddress()]
                ->send(Packet::prepare(
                    $this->network,
                    Packet::REQUEST_PEER_BLOCK_SYNC,
                    $this->getHelloMoonMessage(),
                    $this->getPublicKey()
                ));
        }



        //$this->connectForSynchro($this->peers[$remoteAddress]);
        // $len = count($this->peersOutputStream);
        // $lenPeers = count($this->peers);
        // $canStartSynchro = true;

        // foreach ($this->peersOutputStream as $remoteAddress => $data) {
        //     if (!empty($data)) {
        //         $canStartSynchro = false;
        //     }
        // }

        // if ($this->node->getBlockchainService()->getTopKnowHeight() === $this->node->getBlockchainService()->getTopHeight()) {
        //     $this->_startPersitencePeers();
        //     return;
        // }

        // $peersToConnect = $this->node->getBlockchainService()->getBlockchainManager()->getPeer()->getRemoteAddresses();

        // foreach ($peersToConnect as $peer) {
        //     $this->connectForSynchro($peer);
        // }
    }

    private function _startPersitencePeers($remoteAddress, ConnectionInterface $connection)
    {
        $topHeight = $this->node->getBlockchainService()->getTopHeight();
        $topPeerHeight = $this->peers[$remoteAddress]->getTopHeight();

        var_dump('[P2PServer::_startPersitencePeers] [Sync:Ok] : peer[' . $remoteAddress . '] height at ' . $topPeerHeight . ' | local height -> '.$topHeight);

        if (!$this->_canConnectPersistence()) {
            var_dump('[P2PServer::_startPersitencePeers] [Sync:Ok] Close');
            $connection->close();
            return;
        }

        $this->peers[$connection->getRemoteAddress()]
            ->send(Packet::prepare(
                $this->network,
                Packet::REQUEST_PEER_BLOCK_SYNC,
                $this->getHelloMoonMessage(),
                $this->getPublicKey()
            ));

        $this->switchPeerToPersistence($connection);
        //$this->closeAllConnections();

        // $peersToConnect = $this->node->getBlockchainService()->getBlockchainManager()->getPeer()->getRemoteAddresses();

        // foreach ($peersToConnect as $peer) {
        //     $this->connectForPersistence($peer);
        // }
    }

    private function _toPosition(int $position)
    {
        $return = '00000';
        if ($position > 99999 || $position <= 0) {
            return $return;
        }

        if ($position < 10) {
            $return = '0000' . (string) $position;
        } else if ($position < 100) {
            $return = '000' . (string) $position;
        } else if ($position < 1000) {
            $return = '00' . (string) $position;
        } else if ($position < 10000) {
            $return = '0' . (string) $position;
        } else {
            $return = (string) $position;
        }

        return $return;
    }

    private function _canConnect()
    {
        if (count($this->peers) >= $this->limitConnectedPeers) {
            return false;
        }

        return true;
    }

    private function _canConnectPersistence()
    {
        if (count($this->peersPersistence) >= $this->limitConnectedPeers){
            return false;
        }

        return true;
    }

    public function getLocalPeerConfig()
    {
        return $this->localpeerconfig;
    }

    private function savePeer($peer) {
        $remotes = array_map(function($peer) {
            return $peer['host'] . ':' . $peer['port'];
        }, $this->peersconfig);

        if ($this->localpeerconfig['host'] . ':' . $this->localpeerconfig['port'] !== $peer['remoteAddress'] && !in_array($peer['remoteAddress'], $remotes)) {
            if (!$this->node->getBlockchainService()->getBlockchainManager()->getPeer()->exists($peer['remoteAddress'], 'remoteAddress')) {
                $this->node->getBlockchainService()->getBlockchainManager()->getPeer()->insert($peer);
            }
        }
    }

    private function switchPeerToPersistence($connection)
    {
        if (isset($this->peers[$connection->getRemoteAddress()])) {
            $this->peersPersistence[$connection->getRemoteAddress()] = $this->peers[$connection->getRemoteAddress()];
            unset($this->peers[$connection->getRemoteAddress()]);
        }
    }

    private function initPeer($connection, $peerInfo)
    {


        $this->peers[$connection->getRemoteAddress()] = new Peer($connection);
        $this->peers[$connection->getRemoteAddress()]->setPublicKey($peerInfo['publicKey']);
        $this->peers[$connection->getRemoteAddress()]->setLocalConfig($peerInfo['host'] . ':' . $peerInfo['port']);
        $this->peers[$connection->getRemoteAddress()]->setTopHeight($peerInfo['height']);

        $this->node->getBlockchainService()->setTopKnowHeight($peerInfo['topHeight']);
    }
}
