<?php

namespace Inescoin\Node;


use React\Socket\ConnectionInterface;

use JsonSerializable;

final class Peer implements JsonSerializable {
	public function __construct(public ConnectionInterface $connection) {}

	public function send(string $data) {
		$this->connection->write($data);
	}

	public function host(): string
    {
        return parse_url((string) $this->connection->getRemoteAddress(), PHP_URL_HOST);
    }

    public function port(): int
    {
        return parse_url((string) $this->connection->getRemoteAddress(), PHP_URL_PORT);
    }

    public function jsonSerialize(): array
    {
        return [
            'host' => $this->host(),
            'port' => $this->port(),
        ];
    }

    public function url(): string {
        return implode(':', $this->jsonSerialize());
    }
}
