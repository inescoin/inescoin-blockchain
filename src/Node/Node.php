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
        $response = $this->_checkFrom($data);
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
            //$this->p2pServer->broadcastMemoryPool($data);
        } else {
            $this->logger->info($transaction);
        }

        return $transaction;
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
}
