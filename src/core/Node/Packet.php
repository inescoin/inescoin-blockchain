<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\Node;

use Inescoin\Pow;

use Exception;

final class Packet
{
    /**
     * Network type Mainnet
     */
    public const NETWORK_MAINNET = 'x0';

    /**
     * Network type Mainnet
     */
    public const NETWORK_TESTNET = 'x1';


    public const NETWORKS_TYPE = [
        self::NETWORK_MAINNET,
        self::NETWORK_TESTNET
    ];

    /**
     * Send node infos to network
     */
    public const HELLO_MOON = '10';

    /**
     * Send node infos to network response
     */
    public const HELLO_MOON_RESPONSE = '11';

    /**
     * Request peer list
     */
    public const REQUEST_PEERS = '12';

    /**
     * Request peer list response
     */
    public const REQUEST_PEERS_RESPONSE = '13';

    /**
     * Request peer block sync
     */
    public const REQUEST_PEER_BLOCK_SYNC = '14';

    /**
     * Request peer block sync response
     */
    public const REQUEST_PEER_BLOCK_SYNC_RESPONSE = '15';

    /**
     * Start Persistence
     */
    public const BROADCAST_FOR_PERSISTENCE = '16';

    /**
     * Broadcast top block
     */
    public const BROADCAST_TOP_BLOCK = '17';

    /**
     * Response to broadcast top block
     */
    public const BROADCAST_TOP_BLOCK_RESPONSE = '18';

    /**
     * Boadcast memory transaction pool
     */
    public const BROADCAST_MEMORY_TRANSACTION_POOL = '19';

    /**
     * Boadcast memory message pool
     */
    public const BROADCAST_MEMORY_MESSAGE_POOL = '20';

    /**
     * Boadcast memory contract pool
     */
    public const BROADCAST_MEMORY_CONTRACT_POOL = '21';


    public const MESSAGES_TYPE = [
        self::HELLO_MOON,
        self::HELLO_MOON_RESPONSE,
        self::REQUEST_PEERS,
        self::REQUEST_PEERS_RESPONSE,
        self::REQUEST_PEER_BLOCK_SYNC,
        self::REQUEST_PEER_BLOCK_SYNC_RESPONSE,
        self::BROADCAST_FOR_PERSISTENCE,
        self::BROADCAST_TOP_BLOCK,
        self::BROADCAST_TOP_BLOCK_RESPONSE,
        self::BROADCAST_MEMORY_TRANSACTION_POOL,
        self::BROADCAST_MEMORY_MESSAGE_POOL,
        self::BROADCAST_MEMORY_CONTRACT_POOL,
    ];

    private $network;

    private $type;

    private $body;

    private $position = '0000100001';

    private $page = 1;
    private $pageTotal = 1;

    private $stream;

    private $publicKey = '000000000000000000000000000000000000000000000000000000000000000000';

    private $errors = [];

    public function __construct($stream)
    {
        if (!is_string($stream)) {
            $this->errors[] = 'Data must be type string';
        }

        $this->_init($stream);

        // if (in_array('Body not valid', $this->errors)) {
        //     $stream = substr($stream, (strlen($stream) / 2));
        //     $this->_init($stream);
        // }
    }

    public function _init($stream)
    {
        $this->network = substr($stream, 0, 2);

        if (!in_array($this->network, self::NETWORKS_TYPE) && !in_array('Network type', $this->errors)) {
            $this->errors[] = 'Network type';
        }

        $this->type = substr($stream, 2, 2);

        if (!in_array($this->type, self::MESSAGES_TYPE)) {
            $this->errors[] = 'Message type';
        }

        $this->page = (int) substr($stream, 4, 5);
        $this->pageTotal = (int) substr($stream, 9, 5);
        $this->position = substr($stream, 4, 10);
        $this->publicKey = substr($stream, 14, 66);
        $this->body = substr($stream, 80);

        $this->_initBody();

        $this->stream = $stream;
    }

    public function getNetwork()
    {
        return $this->network;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getPosition()
    {
        return $this->body;
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function getStream()
    {
        return $this->stream;
    }

    public function toArray()
    {
        return [
            'network' => $this->network,
            'type' => $this->type,
            'body' => $this->body,
            'publicKey' => $this->publicKey,
            'position' => $this->position,
            'page' => $this->page,
            'pageTotal' => $this->pageTotal,
            'stream' => $this->stream,
            'errors' => $this->errors,
        ];
    }

    public function isLast()
    {
        return $this->page === $this->pageTotal;
    }

    public function isFirst()
    {
        return $this->page === 1;
    }

    public function onlyOne()
    {
        return $this->isLast() && $this->isFirst();
    }

    public static function prepare($network, $type, $body, $publicKey = '', $position = '0000100001')
    {
        $body = bin2hex(gzcompress(serialize($body), 9));
        $packet = $network . $type . (string) $position . $publicKey . $body;

        // var_dump([
        //     'network' => $network,
        //     'type' => $type,
        //     'body' => $body,
        //     'publicKey' => $publicKey,
        //     'position' => $position,
        // ]);
        // var_dump('[OUTPUT] Packet::prepare', $packet);

        return $packet;
    }

    public function isValid()
    {
        return empty($this->errors) ? true : $this->errors;
    }

    private function _initBody()
    {
        if (empty($this->body)) {
            $this->body = '';
        } else {
            $this->body = @unserialize(gzuncompress(hex2bin($this->body)));
        }
    }

}
