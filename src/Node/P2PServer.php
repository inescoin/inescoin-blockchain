<?php

namespace Inescoin\Node;

use React\Socket\ConnectionInterface;
use React\Socket\Connector;

use Inescoin\Node\Node;

class P2PServer {

	public $node;

	public $peers = [];

	public function __construct(public Connector $connector) {}

	public function __invoke(ConnectionInterface $connection) {
		$connection->on('data', function (string $data) use ($connection): void {
			$packet = new Node\Packet($data);

            if ($packet->isValid()) {
            	switch ($packet->getType()) {
        		 	case Packet::HELLO_MOON:
                        var_dump($packet->getType());
                        break;
            	}
            }
		});

		$connection->on('close', function () use ($connection): void {
    		var_dump(' - - ----------- Closed: ' . $connection->getRemoteAddress());
        });
	}

	public function attachNode(Node $node): void {
        $this->node = $node;
    }

    public function attachPeer(Peer $peer): bool {
    	$this->peers[$peer->connection->getRemoteAddress()] = $peer;
    }

    public function connect($remoteAddress) {
        if (isset($this->peers[$remoteAddress])) {
            var_dump($remoteAddress . ' already in peers');
            return;
        }

    	var_dump('Attempt to connect to: ' . $remoteAddress);

       	$this->connector->connect($remoteAddress)->then(function (ConnectionInterface $connection) {
    		var_dump('Connected to: ' . $remoteAddress);

    		$connection->write("Hello my name is Moon!\n");

    		$this($connection);

    		$this->attachPeer(new Peer($connection));
        });
    }
}
