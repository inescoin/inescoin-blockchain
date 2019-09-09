<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\Node;

use Inescoin\Blockchain;
use Inescoin\Node;
use Inescoin\Block;
use Inescoin\PKI;

use React\Socket\ConnectionInterface;
use React\Socket\Connector;

use RuntimeException;
use DateTimeImmutable;


class P2pServer
{
    private $node;

    private $connector;

    private $network ;

    private $localPeerConfig = [];

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
        if (isset($this->peers[$connection->getRemoteAddress()])) {
            return;
        }

        if (isset($this->peersPersistence[$connection->getRemoteAddress()])) {
            return;
        }

        $connection->on('data', function (string $data) use ($connection): void {

            $packet = new Node\Packet($data);

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

                    case Packet::BROADCAST_FOR_PERSISTENCE:
                        $this->handleBroadcastForPersistence($packet, $connection);
                        break;

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

            if (!$connection->getRemoteAddress()) {
                $connection->close();
            }
        });


        $connection->on('close', function () use ($connection): void {
            if (isset($this->peers[$connection->getRemoteAddress()])) {
                unset($this->peers[$connection->getRemoteAddress()]);
            }

            if (isset($this->peersPersistence[$connection->getRemoteAddress()])) {
                unset($this->peersPersistence[$connection->getRemoteAddress()]);
            }

            if (count($this->peers) + count($this->peersPersistence) == 0) {
                //$this->restartNode();
            }
        });
    }

    public function attachNode(Node $node, $peersConfig, $localPeerConfig): void
    {
        if ($this->node !== null) {
            throw new RuntimeException('Node already attached to p2pServer');
        }

        $this->node = $node;
        $this->localPeerConfig = $localPeerConfig;

        $remoteAddresses = $this->node->getBlockchain()->es->peerService()->getRemoteAddresses();

        foreach ($remoteAddresses as $peer) {
            if ($localPeerConfig['host'] !== $peer['host'] || $localPeerConfig['port'] !== $peer['port']) {
                $this->node->connect($peer['host'], (int) $peer['port']);
            }
        }

        foreach ($peersConfig as $peer) {
            if ($localPeerConfig['host'] !== $peer['host'] || $localPeerConfig['port'] !== $peer['port']) {
                $this->node->connect($peer['host'], (int) $peer['port']);
            }
        }
    }


    public function restartNode(): void
    {

        $remoteAddresses = $this->node->getBlockchain()->es->peerService()->getRemoteAddresses();

        foreach ($remoteAddresses as $peer) {
            if ($this->localPeerConfig['host'] !== $peer['host'] || $this->localPeerConfig['port'] !== $peer['port']) {
                $this->node->connect($peer['host'], (int) $peer['port']);
            }
        }
    }

    public function connect(string $host, int $port): void
    {
        $remoteAddress = $host.':'. $port;
        if (isset($this->peers[$remoteAddress])) {
            return;
        }

        if (count($this->peers) >= $this->limitConnectedPeers) {
            return;
        }

        $CI = $this;
        $cf = function (ConnectionInterface $connection) use ($host, $port, $CI)   {
            $remoteAddress = $connection->getRemoteAddress();

            if (isset($CI->peers[$remoteAddress])) {
                $connection->close();
                return;
            }

            $this($connection);

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

    public function connectForSynchro($peer): void
    {
        if (isset($this->peers[$peer['host'].':'. $peer['port']])) {
            return;
        }

        if (!$this->_canConnect()) {
            return;
        }

        $CI = $this;
        $cf = function (ConnectionInterface $connection) use ($peer, $CI): void   {
            $remoteAddress = $connection->getRemoteAddress();

            if ($CI->localPeerConfig['host'] === $peer['host'] && $CI->localPeerConfig['port'] === $peer['port']) {
                $connection->close();
                return;
            }

            if (isset($CI->peers[$remoteAddress])) {
                $connection->close();
                return;
            }

            $this($connection);

            $CI->peers[$remoteAddress] = new Peer($connection);
            $CI->peers[$remoteAddress]->setPublicKey($peer['publicKey']);

            $CI->peers[$remoteAddress]->setLocalConfig($peer['host'].':'. $peer['port']);

            $CI->peers[$remoteAddress]
                ->send(Packet::prepare(
                    $CI->network,
                    Packet::REQUEST_PEER_BLOCK_SYNC,
                    $CI->getHelloMoonMessage(),
                    $CI->getPublicKey()
                ));
        };

        $cf->bindTo($this, $this);
        $this->connector->connect(sprintf('%s:%s', $peer['host'], $peer['port']))->then($cf);
    }

    public function connectForPersistence($peer): void {
        if (isset($this->peersPersistence[$peer['host'].':'. $peer['port']])) {
            return;
        }

        if (!$this->_canConnectPersistence()) {
            return;
        }

        $CI = $this;
        $cf = function (ConnectionInterface $connection) use ($peer, $CI): void   {
            $remoteAddress = $connection->getRemoteAddress();

            if ($CI->localPeerConfig['host'] === $peer['host'] && $CI->localPeerConfig['port'] === $peer['port']) {
                $connection->close();
                return;
            }

            if (isset($CI->peersPersistence[$remoteAddress])) {
                $connection->close();
                return;
            }

            $this($connection);

            if (isset($this->peers[$remoteAddress])) {
                unset($this->peers[$remoteAddress]);
            }

            $CI->peersPersistence[$remoteAddress] = new Peer($connection);
            $CI->peersPersistence[$remoteAddress]->setPublicKey($peer['publicKey']);

            $CI->peersPersistence[$remoteAddress]->setLocalConfig($peer['host'].':'. $peer['port']);

            $CI->peersPersistence[$remoteAddress]
                ->send(Packet::prepare(
                    $this->network,
                    Packet::BROADCAST_FOR_PERSISTENCE,
                    $this->getHelloMoonMessage(),
                    $this->getPublicKey()
                ));
        };

        $cf->bindTo($this, $this);
        $this->connector->connect(sprintf('%s:%s', $peer['host'], $peer['port']))->then($cf);
    }

    public function handleHelloMoon($packet, ConnectionInterface $connection)
    {
        $peerInfo = $packet->getBody();
        $remoteAddress = $connection->getRemoteAddress();
        $connectedPeers = array_merge($this->node->getPeersPersistence(), $this->node->getPeers());

        if (!empty($peerInfo)) {
            $this->node->getBlockchain()->es->peerService()->save($peerInfo);
            if (!isset($this->peers[$remoteAddress])) {
                $this->peers[$remoteAddress] = new Peer($connection);
                $this->peers[$remoteAddress]->setPublicKey($peerInfo['publicKey']);
                $this->peers[$remoteAddress]->setLocalConfig($peerInfo['localPeerConfig']['host'] . ':' . $peerInfo['localPeerConfig']['port']);
            }
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

        $remoteAddress = $connection->getRemoteAddress();

        $peerInfo = $packet->getBody();

        $connectedPeers = array_merge($this->node->getPeersPersistence(), $this->node->getPeers());

        if (!empty($peerInfo)) {
            $this->node->getBlockchain()->es->peerService()->save($peerInfo);
            if (isset($this->peers[$remoteAddress])) {
                $this->peers[$remoteAddress]->setPublicKey($peerInfo['publicKey']);
                $this->peers[$remoteAddress]->setLocalConfig($peerInfo['localPeerConfig']['host'] . ':' . $peerInfo['localPeerConfig']['port']);
            }

            if (isset($this->peersPersistence[$remoteAddress])) {
                $this->peersPersistence[$remoteAddress]->setPublicKey($peerInfo['publicKey']);
                $this->peersPersistence[$remoteAddress]->setLocalConfig($peerInfo['localPeerConfig']['host'] . ':' . $peerInfo['localPeerConfig']['port']);
            }

            $this->node->getBlockchain()->setTopKnowHeight($peerInfo['topHeight']);
        }

        $this->write($connection, Packet::REQUEST_PEERS, [
            'peers' => $this->node->getBlockchain()->es->peerService()->getRemoteAddresses()
        ]);
    }

    public function handlePeerList($packet, ConnectionInterface $connection)
    {
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
                    $this->node->getBlockchain()->es->peerService()->index($index, $peer);
                }

                $this->write($connection, Packet::REQUEST_PEERS_RESPONSE, [
                    'peers' => $this->node->getBlockchain()->es->peerService()->getRemoteAddresses()
                ]);
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
                    $this->node->getBlockchain()->es->peerService()->index($index, $peer);
                }

                $this->write($connection, Packet::REQUEST_PEERS_RESPONSE, [
                    'peers' => $this->node->getBlockchain()->es->peerService()->getRemoteAddresses()
                ]);
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
        $remoteAddress = $connection->getRemoteAddress();

        if (!isset($this->peers[$remoteAddress])) {
            return;
        }

        if (isset($this->peersOutputStream[$remoteAddress]) && !empty($this->peersOutputStream[$remoteAddress])) {
            $this->write($connection, Packet::REQUEST_PEERS_RESPONSE, []);
            return;
        }

        if ($packet->onlyOne()) {
            $body = $packet->getBody();
            $decrypted = $this->decrypt($body, $packet->getPublicKey());

            if ($decrypted) {
                foreach ($decrypted['peers'] as $index => $peer) {
                    $this->node->getBlockchain()->es->peerService()->index($index, $peer);
                }

                $connection->close();
                $this->_startBlockchainSynchro();
            }

            $this->peersInputStream[$remoteAddress] = [];

        } else if($packet->isFirst()) {
            $this->peersInputStream[$remoteAddress] = [];
            $this->peersInputStream[$remoteAddress][] = $packet;

            $connection->write(Packet::prepare(
                $this->network,
                Packet::REQUEST_PEERS_RESPONSE,
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
                    $this->node->getBlockchain()->es->peerService()->index($index, $peer);
                }

                $connection->close();

                $this->_startBlockchainSynchro();
            }

            $this->peersInputStream[$remoteAddress] = [];
        } else {
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
        $remoteAddress = $connection->getRemoteAddress();

        if (isset($this->peersOutputStream[$remoteAddress]) && !empty($this->peersOutputStream[$remoteAddress])) {
            $this->write($connection, Packet::REQUEST_PEER_BLOCK_SYNC_RESPONSE, []);
            return;
        }

        $peerInfo = $packet->getBody();

        if (!empty($peerInfo)) {
            $this->node->getBlockchain()->es->peerService()->save($peerInfo);
            if (!isset($this->peers[$remoteAddress])) {
                $this->peers[$remoteAddress] = new Peer($connection);
                $this->peers[$remoteAddress]->setPublicKey($peerInfo['publicKey']);
                $this->peers[$remoteAddress]->setLocalConfig($peerInfo['localPeerConfig']['host'] . ':' . $peerInfo['localPeerConfig']['port']);
            }
        }

        if (!array_key_exists('height', $peerInfo)) {
            $connection->close();
            return;
        }

        $blockHeight = (int) $peerInfo['height'] + 1;

        $topHeight = $this->node->getBlockchain()->getTopHeight();

        if ($blockHeight === $topHeight) {
        //    var_dump('Node sync complete, waitting for next block: height -> ' . $blockHeight . ' | top -> '.$topHeight);
            // $connection->close();
            // return;
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

        $blocks = $this->node->getBlockchain()->es->blockService()->getCompressed($blockHeight, $toBlock);

        $this->write($connection, Packet::REQUEST_PEER_BLOCK_SYNC_RESPONSE, [
            'blocks' => $blocks
        ]);
    }

    public function handlePeerBlockSyncResponse($packet, ConnectionInterface $connection)
    {
        $remoteAddress = $connection->getRemoteAddress();

        if ($packet->onlyOne()) {
            $body = $packet->getBody();
            $decrypted = $this->decrypt($body, $packet->getPublicKey());

            if ($decrypted) {
                $blocks = [];
                foreach ($decrypted['blocks'] as $tmpBlock) {
                    if ($newBlock = Block::decompress($tmpBlock)) {
                        $blocks[] = Block::toBlock($newBlock);
                    }
                }

                if ($this->node->getBlockchain()->bulkAdd($blocks) && isset($this->peers[$remoteAddress])) {
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
                    if ($newBlock = Block::decompress($tmpBlock)) {
                        $blocks[] = Block::toBlock($newBlock);
                        $lastHeight = $newBlock['height'];
                    }
                }

                if ($this->node->getBlockchain()->bulkAdd($blocks)) {
                    if ($this->node->getBlockchain()->getTopKnowHeight() === $lastHeight) {
                        $this->_startPersitencePeers();
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


    public function handleBroadcastForPersistence($packet, ConnectionInterface $connection)
    {
        $remoteAddress = $connection->getRemoteAddress();

        if (isset($this->peersOutputStream[$remoteAddress]) && !empty($this->peersOutputStream[$remoteAddress])) {
            $this->write($connection, Packet::REQUEST_PEER_BLOCK_SYNC_RESPONSE, []);
            return;
        }

        $peerInfo = $packet->getBody();

        if (!empty($peerInfo)) {
            $this->node->getBlockchain()->es->peerService()->save($peerInfo);
            if (!isset($this->peersPersistence[$remoteAddress])) {
                $this->peersPersistence[$remoteAddress] = new Peer($connection);
                $this->peersPersistence[$remoteAddress]->setPublicKey($peerInfo['publicKey']);
                $this->peersPersistence[$remoteAddress]->setLocalConfig($peerInfo['localPeerConfig']['host'] . ':' . $peerInfo['localPeerConfig']['port']);
            }
        }

        $blockHeight = (int) $peerInfo['height'] + 1;

        $topHeight = $this->node->getBlockchain()->getTopHeight();

        if ($blockHeight === $topHeight) {
        //    var_dump('Node sync complete, waitting for next block: height -> ' . $blockHeight . ' | top -> '.$topHeight);
            // $connection->close();
            // return;
        }

        if ($blockHeight > $topHeight) {
            $connection->write(Packet::prepare(
                $this->network,
                Packet::REQUEST_PEER_BLOCK_SYNC,
                $this->getHelloMoonMessage(),
                $this->getPublicKey()
            ));
            return;
        }
    }

    public function handleBroadcastTopBlock($packet, ConnectionInterface $connection)
    {
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
                    if ($newBlock = Block::decompress($tmpBlock)) {
                        $blocks[] = Block::toBlock($newBlock);
                    }
                }

                if (!$this->node->getBlockchain()->bulkAdd($blocks)) {
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
                    if ($newBlock = Block::decompress($tmpBlock)) {
                        $blocks[] = Block::toBlock($newBlock);
                        $lastHeight = $newBlock['height'];
                    }
                }

                if (!$this->node->getBlockchain()->bulkAdd($blocks)) {
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
        $remoteAddress = $connection->getRemoteAddress();

        if (isset($this->peersOutputStream[$remoteAddress]) && !empty($this->peersOutputStream[$remoteAddress])) {
            $this->write($connection, Packet::BROADCAST_TOP_BLOCK, []);
            return;
        }
    }

    public function handleBroadcastMemoryPoolTransactionResponse($packet, ConnectionInterface $connection)
    {
        $body = $packet->getBody();
        $decrypted = $this->decrypt($body, $packet->getPublicKey());

        if ($decrypted) {
            if (is_array($decrypted)) {
                $decrypted = $decrypted[0];
            }

            $transactionPool = $this->node->getBlockchain()->push($decrypted);
            if (!isset($transactionPool['error'])) {
                $this->broadcastMemoryPool($decrypted);
            }
        }
    }

    public function handleBroadcastMemoryPoolMessageResponse($packet, ConnectionInterface $connection)
    {
        $body = $packet->getBody();
        $decrypted = $this->decrypt($body, $packet->getPublicKey());

        if ($decrypted) {
            if (is_array($decrypted)) {
                $decrypted = $decrypted[0];
            }

            $transactionPool = $this->node->getBlockchain()->pushMessage($decrypted);
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

    private function getHelloMoonMessage() {
        return [
            'height' => $this->node->getBlockchain()->getTopHeight(),
            'topHeight' => $this->node->getBlockchain()->getTopKnowHeight(),
            'publicKey' => $this->getPublicKey(),
            'localPeerConfig' => $this->localPeerConfig,
            'peersCount' => count($this->peers),
            'inputPeers' => count($this->peersInputStream),
            'peersPersistence' => $this->node->getPeersPersistence(),
            'peers' => $this->node->getPeers(),
            'topCumulativeDifficulty' => $this->node->getBlockchain()->getTopCumulativeDifficulty(),
            'outputPeers' => count($this->peersOutputStream),
        ];
    }

    public function broadcastMinedBlock()
    {
        $lastBlock = $this->node->getBlockchain()->getLastBlock();

        $blocks = [$lastBlock->compress()];

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

    private function _startBlockchainSynchro()
    {
        $len = count($this->peersOutputStream);
        $lenPeers = count($this->peers);
        $canStartSynchro = true;

        foreach ($this->peersOutputStream as $remoteAddress => $data) {
            if (!empty($data)) {
                $canStartSynchro = false;
            }
        }

        if ($this->node->getBlockchain()->getTopKnowHeight() === $this->node->getBlockchain()->getTopHeight()) {
            $this->_startPersitencePeers();
            return;
        }

        $peersToConnect = $this->node->getBlockchain()->es->peerService()->getByTopCumulativeDifficulty();

        foreach ($peersToConnect as $peer) {
            $this->connectForSynchro($peer);
        }
    }


    private function _startPersitencePeers()
    {
        $this->closeAllConnections();

        $peersToConnect = $this->node->getBlockchain()->es->peerService()->getByTopCumulativeDifficulty();

        foreach ($peersToConnect as $peer) {
            $this->connectForPersistence($peer);
        }
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
        if (count($this->peersPersistence) >= $this->limitConnectedPeers) {
            return false;
        }

        return true;
    }

    public function getLocalPeerConfig() {
        return $this->localPeerConfig;
    }

}
