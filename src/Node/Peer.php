<?php

namespace Inescoin\Node;


use React\Socket\ConnectionInterface;

use JsonSerializable;

final class Peer implements JsonSerializable {

	private $connection;

    private $publicKey = null;

    private $peers = [];

    private $topHeight = 1;

    private $localConfig = [];

    private $data = [];

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function send($message): void
    {
        $this->connection->write($message);
    }

    public function read($message)
    {
        return $message;
    }

    public function host(): string
    {
        return parse_url((string) $this->connection->getRemoteAddress(), PHP_URL_HOST);
    }

    public function port(): int
    {
        return parse_url((string) $this->connection->getRemoteAddress(), PHP_URL_PORT);
    }

    public function jsonSerialize()
    {
        return [
            'host' => $this->host(),
            'port' => $this->port(),
        ];
    }

    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
        return $this;
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function setPeers(array $peers)
    {
        if (is_array($peers) && !empty($peers)) {
            $this->peers = $peers;
        }

        return $this;
    }

    public function getPeers()
    {
        return $this->peers;
    }

    public function setTopHeight(int $height)
    {
        $this->topHeight = $height;

        return $this;
    }

    public function getTopHeight()
    {
        return $this->topHeight;
    }

    public function isSync(int $localTopHeight) {

        $numberLastBlocks = 5;
        // var_dump('[Peer][isSync] ' . $localTopHeight . ' === ' . $this->topHeight);

        return $this->topHeight === $localTopHeight || ($this->topHeight - $localTopHeight > 0  && $this->topHeight - $localTopHeight < $numberLastBlocks);
    }

    public function close()
    {
        $this->connection->close();
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function setLocalConfig($localConfig) {
        $this->localConfig = $localConfig;
    }

    public function getLocalConfig() {
        return $this->localConfig;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     *
     * @return self
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
