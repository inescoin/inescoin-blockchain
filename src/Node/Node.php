<?php

namespace Inescoin\Node;

use Inescoin\Helper\PKI;
use Inescoin\Node\P2pServer;
use Inescoin\Service\BlockchainService;
use Inescoin\Service\LoggerService;
use Inescoin\Service\MinerService;

final class Node {

    private $keys = [];

    /**
     * @var MinerService
     */
    private $miner;

    /**
     * @var P2pServer
     */
    private $p2pServer;

    public $logger;

    public function __construct(MinerService $miner, P2pServer $p2pServer)
    {
        $this->keys = PKI::generateRSAKeys();

        $this->miner = $miner;
        $this->p2pServer = $p2pServer;

        $this->logger = (LoggerService::getInstance())->getLogger();
    }

    public function getBlockchainService(): BlockchainService
    {
        return $this->miner->getBlockchainService();
    }

    public function getPublicKey(): string
    {
        return $this->keys['publicKey'];
    }

    public function getPrivateKey(): string
    {
        return $this->keys['privateKey'];
    }

    public function getKeys(): array
    {
        return $this->keys;
    }

    public function getBlockTemplate($data) {
        return $this->miner->getBlockTemplate($data);
    }

    public function submitBlockHash($data) {
        return $this->miner->submitBlockHash($data);
    }

    public function generateWallet(): array
    {
        return PKI::newEcKeys();
    }

    public function checkFromMemoryPool($data) {
        $this->logger->info('[Node] [pushMemoryPool] checkFromMemoryPool start...');

        $response = $this->_checkFrom($data);

        $this->logger->info('[Node] [pushMemoryPool] checkFromMemoryPool process...');

        if (empty($data)) {
            return [
                'error' => 'Data is empty',
            ];
        }

        if (isset($response['broadcasted']) && isset($response['data'])) {
            $pushed = $this->pushMemoryPool($response['data']);

            if (!isset($pushed['error'])) {
                return [$pushed];
            } else {
                return $pushed;
            }
        }

        return [
            'error' => 'invalid node public key check',
            '$response' => $response
        ];
    }

    public function pushMemoryPool($data)
    {
        $this->logger->info('[Node] [pushMemoryPool] broadcast start...');

        $transaction = $this->miner->push($data);

        if (!isset($transaction['error'])) {
            $this->logger->info('[Node] [pushMemoryPool] broadcast done!');
            $this->p2pServer->broadcastMemoryPool($data);
        } else {
            $this->logger->info($transaction);
        }

        return $transaction;
    }

    public function pushMemoryMessagePool($data)
    {
        $this->logger->info('[Node] [pushMemoryMessagePool] broadcast start...');
        $message = $this->miner->pushMessagePool($data);

        if (!isset($message['error'])) {
            $this->logger->info('[Node] [pushMemoryMessagePool] broadcast done!');
            $this->p2pServer->broadcastMemoryMessagePool($data);
        } else {
            $this->logger->info('[Node] ' . $message);
        }

        // return [];
        return $message;
    }

    public function checkFromMessagePool($data) {
        $response = $this->_checkFrom($data);

        if (isset($response['broadcasted']) && isset($response['data'])) {
            $response['data'] = $this->pushMemoryMessagePool($response['data']);
        }

        return $response;
    }

    private function _checkFrom($data)
    {
        $response['error'] = 500;

        if (is_array($data) && isset($data['message']) && is_array($data['message']) && isset($data['publicKey'])) {
            $output = '';
            $pk = base64_decode($this->getPrivateKey());
            $publicKey = $data['publicKey'];
            $cMessage = count($data['message']);
            foreach ($data['message'] as $position => $message) {
                try {
                    if (PKI::ecVerifyHex($message['d'], $message['s'], $publicKey)) {
                        $_part = PKI::decryptFromPrivateKey(hex2bin($message['d']), $pk);
                        $output .= $_part;
                    } else {
                        $this->logger->info('[Node] [checkFromMemoryPool] ERROR signature position ' . $position . '/' . $cMessage);
                    }
                } catch(\Exception $e) {
                    $this->logger->info('[ERROR] : @323');
                }

            }

            $decrypted = json_decode(base64_decode($output));
            if ($decrypted) {
                $response = [];
                $response['broadcasted'] = true;
                $response['data'] = $decrypted;
            }
        }

        return $response;
    }

    public function broadcastMinedBlock($block)
    {
        $this->logger->info("[Node] Broadcast mined block to peers strarted... ");
        $this->p2pServer->broadcastMinedBlock($block);
    }

    public function getP2PServer()
    {
        return $this->p2pServer;
    }

    public function getLocalPeerConfig()
    {
        return $this->p2pServer->getLocalPeerConfig();
    }

    public function getPeersPersistence()
    {
        return $this->p2pServer->getPeersPersistence();
    }
}
