<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\ES;

use Inescoin\LoggerService;

use DateTimeImmutable;

class ESPeerService extends ESService
{
	protected $type = 'peer';

	protected $index = 'blockchain-peer';

	private $logger;

	public function __construct($prefix = '') {
		$this->logger = (LoggerService::getInstance())->getLogger();
		$this->index = $prefix ? $prefix . '_' . $this->index : $this->index;
		parent::__construct();
	}

	public function index($id, $body, $refresh = false)
	{
		if (!$this->exists($id)) {
			return $this->_index($this->index, $this->type, $id, $body, $refresh);
		} else {
			return $this->update($this->index, $body);
		}
	}

	public function getRemoteAddresses() {
		$this->logger->info("[ESPeerService][getRemoteAddresses] Index: {$this->index}  | Type: {$this->type}");
		// var_dump("[ESService][_all] Index: $index  | Type: $type");

		try {
			$response = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    "from" => 0,
			    "size" => 1000,
			    'body' => [
			        'query' => [
			            'match_all' => [
			            	"boost" => 1.2
			            ]
			        ],
			        "sort" => [
				    	"lastSeen" => "desc"
				  	],
			    ]
			]);
 		} catch (\Exception $e) {
 			$response['error'] = $e->getMessage();
			// var_dump('ERROR --> ' . $response['error']);
		}

		$output = [];
		foreach ($response['hits']['hits'] as $hit) {
			$output[$hit['_id']] = $hit['_source'];
		}

		return $output;
	}

	public function getByTopCumulativeDifficulty() {
		$this->logger->info("[ESPeerService][getByTopCumulativeDifficulty] Index: {$this->index}  | Type: {$this->type}");
		// var_dump("[ESService][_all] Index: $index  | Type: $type");

		try {
			$response = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    "from" => 0,
			    "size" => 100,
			    'body' => [
			        'query' => [
			            'match_all' => [
			            	"boost" => 1.2
			            ]
			        ],
			        "sort" => [
				    	"topCumulativeDifficulty" => "desc"
				  	],
			    ]
			]);
 		} catch (\Exception $e) {
 			$response['error'] = $e->getMessage();
			// var_dump('ERROR --> ' . $response['error']);
		}

		$output = [];
		foreach ($response['hits']['hits'] as $hit) {
			$output[$hit['_id']] = $hit['_source'];
		}

		return $output;
	}

	public function save($peer)
	{
		// if (is_string($remoteAddress)) {
		// 	$this->_save($remoteAddress);
		// } elseif (is_array($remoteAddress)) {
		// 	foreach ($remoteAddress as $rAddress) {
		// 		$this->_save($rAddress);
		// 	}
		// }
		$this->_save($peer);
	}

	public function getMapping()
	{
		return [
		    'peer' => [
		    	'properties' => [
			        'height' => [
			          'type' => 'long',
			        ],
			        'topHeight' => [
			          'type' => 'long',
			        ],
			        'topCumulativeDifficulty' => [
			          'type' => 'long',
			        ],
			        'remoteAddress' => [
			          'type' => 'text',
			        ],
			        'publicKey' => [
			          'type' => 'text',
			        ],
			        'host' => [
			          'type' => 'text',
			        ],
			        'port' => [
			          'type' => 'long',
			        ],
			        'lastSeen' => [
			          'type' => 'long',
			        ],
			        'peersInputStream' => [
			          'type' => 'long',
			        ],
			        'peersOutputStream' => [
			          'type' => 'long',
			        ],
			        'peersInputStream' => [
			          'type' => 'long',
			        ],
			        'peersCount' => [
			          'type' => 'long',
			        ],
		    	],
		  	],
		];
	}

	private function _save($peer)
	{
		$height = $peer['height'] ?? 0;
		$topHeight = $peer['topHeight'] ?? 0;
		$config = $peer['localPeerConfig'] ?? 0;
		$publicKey = $peer['publicKey'] ?? '';
		$topCumulativeDifficulty = $peer['topCumulativeDifficulty'] ?? 0;
		$peersCount = $peer['peersCount'] ?? 0;
		$peersOutputStream = $peer['peersOutputStream'] ?? 0;
		$peersInputStream = $peer['peersInputStream'] ?? 0;

		$host = $config['host'];
		$port = $config['port'];

		$remoteAddress = $host . ':' . $port;

		$body = [
			'height' => $height,
			'peersCount' => $peersCount,
			'peersOutputStream' => $peersOutputStream,
			'peersInputStream' => $peersInputStream,
			'remoteAddress' => $remoteAddress,
			'publicKey' => $publicKey,
			'topCumulativeDifficulty' => $topCumulativeDifficulty,
			'host' => $host,
			'port' => $port,
			'lastSeen' => (new \DateTimeImmutable())->getTimestamp() * 1000
		];

		// var_dump('[ESPeerService] [_save] ', $body);
		$this->index($remoteAddress, $body);
	}
}
