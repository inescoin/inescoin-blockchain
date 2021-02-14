<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\ES;

use Inescoin\Transaction;
use Inescoin\LoggerService;

use Inescoin\BlockchainConfig;

class ESTransferService extends ESService
{
	protected $type = 'transfer';

	protected $index = 'blockchain-transfer';
	public $logger;

	public function __construct($prefix = '') {
		$this->logger = (LoggerService::getInstance())->getLogger();
		$this->index = $prefix ? $prefix . '_' . $this->index : $this->index;
		parent::__construct();
	}

	public function index($id, $body, $refresh = false)
	{
		return $this->_index($this->index, $this->type, $id, $body, $refresh);
	}

	public function getByHash($transferHash)
	{
		try {
			$result = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    "from" => 0,
			    "size" => 1,
			    'body' => [
			        'query' => [
			            'match' => [
			            	'hash' => $transferHash
			            ]
			        ],
			    ]
			]);
			var_dump('ERROR --> 1');
 		} catch (\Exception $e) {
			$result['error'] = $e->getMessage();
			var_dump('ERROR --> ' . self::class . ' | ' . $response['error']);
		}

		if (isset($result['error']) || !isset($result['hits']['hits'][0])) {
			return [];
		}

		return $result['hits']['hits'][0]['_source'];
	}

	public function getWalletAddressHistory($address = BlockchainConfig::NAME, $size = 100, $page = 1)
	{
		$from = 0;
		if ($page > 1) {
			$from = ((int) $page * $size - 1) - $size;
		}

		try {
			$result = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    "from" => $from,
			    "size" => $size,
			    'body' => [
			        'query' => [
			            "multi_match" => [
					      "query" => $address,
					      "type"=> "best_fields",
					      "fields" => [ "from", "to" ],
					      "operator" => "or"
					    ]
			        ],
			        "sort" => [
				    	"height" => "desc"
				  	],
			    ]
			]);
 		} catch (\Exception $e) {
			$result['error'] = $e->getMessage();
			var_dump('ERROR --> ' . self::class . ' | ' . $response['error']);
		}

		if (isset($result['error']) || !isset($result['hits']['hits'][0])) {
			return [];
		}

		$output['transactions'] = [];
		foreach ($result['hits']['hits'] as $hit) {
			$output['transactions'][] = $hit['_source'];
		}

		$output['total'] = $result['hits']['total']['value'];

		return $output;
	}

	public function count()
	{
		try {
			$result = $this->client->count([
			    'index' => $this->index,
			    'type' => $this->type,
			    'ignore_unavailable' => true
			]);
		} catch (\Exception $e) {
			$result['error'] = $e->getMessage();
		}

		if (isset($result['error'])) {
			return 0;
		}

		return $result['count'];
	}

	public function getMapping() {
		return [
		    'transfer' => [
		    	'properties' => [
		    		'height' => [
			          'type' => 'long',
			        ],
			        'createdAt' => [
			          'type' => 'long',
			        ],
			        'from' => [
			          'type' => 'text'
			        ],
			        'amount' => [
			          'type' => 'long',
			        ],
			        'to' => [
			          'type' => 'text'
			        ],
			        'nonce' => [
			          'type' => 'text'
			        ],
			        'hash' => [
			          'type' => 'text'
			        ],
			        'transsactionHash' => [
			          'type' => 'text'
			        ],
			        'walletId' => [
			          'type' => 'text'
			        ],
		    	],
		  	],
		];
	}
}
