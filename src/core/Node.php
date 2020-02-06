<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

use Inescoin\Node\Transfer;
use Inescoin\Node\P2pServer;
use Inescoin\Node\Peer;

final class Node
{
    private $keys = [];

    private $miner;

    private $p2pServer;

    public function __construct(Miner $miner, P2pServer $p2pServer)
    {
        $this->keys = PKI::generateRSAKeys();

        $this->miner = $miner;
        $this->p2pServer = $p2pServer;
    }

    public function getLastBlocks($limit = 10, $page = 1, $original = false)
    {
        return $this->miner->getBlockchain()->getLastBlock($limit, true, $page, $original);
    }

    public function getMessages($id = null): array
    {
        return $this->miner->getBlockchain()->getMessagesByAddresses($id);
    }

    public function getLastMessagesPool($timestamp = null): array
    {
        return $this->miner->getBlockchain()->getLastMessagesPool($timestamp);
    }

    public function getBlockTemplate($data) {
        return $this->miner->getBlockTemplate($data);
    }

    public function submitBlockHash($data) {
        return $this->miner->submitBlockHash($data);
    }

    public function getMemoryPool(): array
    {
        return $this->miner->getBlockchain()->getMemoryPool();
    }

    public function mineBlock()
    {
        $block = $this->miner->mineBlock();

        if (is_array($block) && array_key_exists('error', $block)) {
            return $block;
        }

        $this->p2pServer->broadcast(new Transfer(Transfer::PING, serialize([
            'height' => $this->getBlockchain()->getTopHeight(),
            'peers' => $this->p2pServer->getPeers()
        ])));

        return $block;
    }

    public function broadcastMinedBlock()
    {
        var_dump("Broadcast mined block to peers strarted... ");
        $this->p2pServer->broadcastMinedBlock();
    }

    public function pushMemoryPool($data)
    {
        var_dump('[pushMemoryPool] broadcast start...');
        $transaction = $this->miner->push($data);
        if (!isset($transaction['error'])) {
            var_dump('[pushMemoryPool] broadcast done!');
            $this->p2pServer->broadcastMemoryPool($data);
        } else {
            var_dump($transaction);
        }
        // return [];
        return $transaction;
    }

    public function checkFromMemoryPool($data) {
        $response = $this->_checkFrom($data);

        if (isset($response['broadcasted']) && isset($response['data'])) {
            $pushed = $this->pushMemoryPool($response['data']);
            if (!isset($pushed['error'])) {
                return [$pushed];
            } else {
                return $pushed;
            }
        }
        return [
            'error' => 'invalid public key check'
        ];
    }

    public function pushMemoryMessagePool($data)
    {
        var_dump('[pushMemoryMessagePool] broadcast start...');
        $message = $this->miner->pushMessagePool($data);
        if (!isset($message['error'])) {
            var_dump('[pushMemoryMessagePool] broadcast done!');
            $this->p2pServer->broadcastMemoryMessagePool($data);
        } else {
            var_dump($message);
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

    public function getPeers(): array
    {
        return $this->p2pServer->getPeers();
    }

    public function getPeersPersistence(): array
    {
        return $this->p2pServer->getPeersPersistence();
    }

    public function connect(string $host, int $port): void
    {
        var_dump("[Node][connect] Peer connect to $host:$port");
        $this->p2pServer->connect($host, $port);
    }

    public function getBlockchain(): Blockchain
    {
        return $this->miner->getBlockchain();
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
                        var_dump('[Node] [checkFromMemoryPool] ERROR signature position ' . $position . '/' . $cMessage);
                    }
                } catch(\Exception $e) {
                    var_dump('[ERROR] : @323');
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

    public function getlocalPeerConfig()
    {
        return $this->p2pServer->getlocalPeerConfig();
    }
}
