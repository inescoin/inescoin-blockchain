<?php

namespace Inescoin\Entity;

class Peer extends AbstractEntity
{
	protected $height; // 'height' => 'integer PRIMARY KEY',
	protected $topHeight; // 'topHeight' => 'integer',
	protected $topCumulativeDifficulty; // 'topCumulativeDifficulty' => 'integer',
	protected $remoteAddress; // 'remoteAddress' => 'text',
	protected $publicKey; // 'publicKey' => 'text',
	protected $host; // 'host' => 'text',
	protected $port; // 'port' => 'integer',
	protected $rpcHost; // 'rpcHost' => 'text',
	protected $rpcPort; // 'rpcPort' => 'integer',
	protected $lastSeen; // 'lastSeen' => 'integer',
	protected $peersInputStream; // 'peersInputStream' => 'integer',
	protected $peersOutputStream; // 'peersOutputStream' => 'integer',
	protected $peersInputStream; // 'peersInputStream' => 'integer',
	protected $peersCount; // 'peersCount' => 'integer'

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     *
     * @return self
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTopHeight()
    {
        return $this->topHeight;
    }

    /**
     * @param mixed $topHeight
     *
     * @return self
     */
    public function setTopHeight($topHeight)
    {
        $this->topHeight = $topHeight;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTopCumulativeDifficulty()
    {
        return $this->topCumulativeDifficulty;
    }

    /**
     * @param mixed $topCumulativeDifficulty
     *
     * @return self
     */
    public function setTopCumulativeDifficulty($topCumulativeDifficulty)
    {
        $this->topCumulativeDifficulty = $topCumulativeDifficulty;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRemoteAddress()
    {
        return $this->remoteAddress;
    }

    /**
     * @param mixed $remoteAddress
     *
     * @return self
     */
    public function setRemoteAddress($remoteAddress)
    {
        $this->remoteAddress = $remoteAddress;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param mixed $publicKey
     *
     * @return self
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     *
     * @return self
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     *
     * @return self
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRpcHost()
    {
        return $this->rpcHost;
    }

    /**
     * @param mixed $rpcHost
     *
     * @return self
     */
    public function setRpcHost($rpcHost)
    {
        $this->rpcHost = $rpcHost;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRpcPort()
    {
        return $this->rpcPort;
    }

    /**
     * @param mixed $rpcPort
     *
     * @return self
     */
    public function setRpcPort($rpcPort)
    {
        $this->rpcPort = $rpcPort;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastSeen()
    {
        return $this->lastSeen;
    }

    /**
     * @param mixed $lastSeen
     *
     * @return self
     */
    public function setLastSeen($lastSeen)
    {
        $this->lastSeen = $lastSeen;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPeersInputStream()
    {
        return $this->peersInputStream;
    }

    /**
     * @param mixed $peersInputStream
     *
     * @return self
     */
    public function setPeersInputStream($peersInputStream)
    {
        $this->peersInputStream = $peersInputStream;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPeersOutputStream()
    {
        return $this->peersOutputStream;
    }

    /**
     * @param mixed $peersOutputStream
     *
     * @return self
     */
    public function setPeersOutputStream($peersOutputStream)
    {
        $this->peersOutputStream = $peersOutputStream;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPeersInputStream()
    {
        return $this->peersInputStream;
    }

    /**
     * @param mixed $peersInputStream
     *
     * @return self
     */
    public function setPeersInputStream($peersInputStream)
    {
        $this->peersInputStream = $peersInputStream;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPeersCount()
    {
        return $this->peersCount;
    }

    /**
     * @param mixed $peersCount
     *
     * @return self
     */
    public function setPeersCount($peersCount)
    {
        $this->peersCount = $peersCount;

        return $this;
    }
}
