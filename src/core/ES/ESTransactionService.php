<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\ES;

use Inescoin\Transaction;
use Inescoin\LoggerService;

use Inescoin\BlockchainConfig;

class ESTransactionService extends ESService
{
	protected $type = 'transaction';

	protected $index = 'blockchain-transaction';
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

	public function all($formatted = false) {
		var_dump("[ESTransactionService][all] Index: $this->index  | Type: $this->type");

		try {
			$response = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    "size" => 100,
			    'body' => [
			        'query' => [
			            'match_all' => [
			            	"boost" => 1.2
			            ]
			        ]
			    ]
			]);
 		} catch (\Exception $e) {
 			$response['error'] = $e->getMessage();
			var_dump('ERROR --> ' . self::class . ' | ' . $response['error']);
		}

		if (isset($response['error'])) {
			return $response;
		}

		$output = [];
		foreach ($response['hits']['hits'] as $hit) {
			$transaction = $this->_toTransaction($hit['_source']);
			$output[] = $formatted ? $transaction : $transaction->getInfos();
		}

		return $response;
	}

	public function getChain($fromHeight, $toHeight) {
		$this->logger->info("[ESTransactionService][getChain] fromHeight: $fromHeight | toHeight: $toHeight");
		$size = $toHeight -  $fromHeight;
		$size = $size > 1  ? $size : 1;

		try {
			$result = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    'size' => $size,
			    'body' => [
			    	"stored_fields" => [
					    "_id"
					],
			    	'query' => [
			    		"range" => [
				            "blockHeight" => [
				                "gte" => $fromHeight,
				                "lte" => $toHeight,
				                "boost" => 2.0
				            ]
				        ]
			        ],
			        "sort" => [
				    	"blockHeight" => "desc"
				  	],
			    ]
			]);
		} catch(\Exception $e) {
			var_dump($e->getMessage());
		}

		if (!isset($result)) {
			return [];
		}

		$output = [];
		foreach ($result['hits']['hits'] as $hit) {
			$output[] = $hit['_id'];
		}

		return $output;
	}

	public function getWalletAddressHistory($address = BlockchainConfig::NAME, $size = 100, $page = 0)
	{
		try {
			$result = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    "from" => $page,
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
				    	"blockHeight" => "desc"
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

	public function getByHash($transactionHash)
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
			            	'hash' => $transactionHash
			            ]
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

		$transaction = $result['hits']['hits'][0]['_source'];
		$transaction['toDo'] = json_decode($transaction['toDo'], true);
		$transaction['transfers'] = json_decode($transaction['transfers'], true);
		return $transaction;
	}

	public function getByDomainUrl($url)
	{
		try {
			$result = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    "from" => 0,
			    "size" => 300,
			    'body' => [
			        'query' => [
			            'match' => [
			            	'url' => $url
			            ]
			        ],
			        "sort" => [
				    	"blockHeight" => "desc"
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

		$output = [];
		foreach ($result['hits']['hits'] as $hit) {
			$transaction = $hit['_source'];
			$transaction['toDo'] = base64_encode($transaction['toDo']);
			$output[] = $transaction;
		}

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

	public function getMinerRewardAmount()
	{
		return BlockchainConfig::FIXED_MINER_REWARD;

		$this->logger->info('[ESTransactionService][getMinerRewardAmount] Index: ' . $this->index . '  | Type: ' . $this->type);

		try {
			$result = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    "size" => 0,
			    'body' => [
			    	'query' => [
			            'match' => [
			            	"coinbase" => true
			            ]
			        ],
			        "aggs" => [
				    	"amount" => [
				    		"avg" => [
				    			"field" => 'amount'
				    		]
				    	]
				  	],
			    ]
			]);
		} catch(\Exception $e) {
			var_dump($e->getMessage());
		}

		if (!isset($result['aggregations'])) {
			return null;
		}

		$amount = (int) $result['aggregations']['amount']['value'];

		if ($amount !== BlockchainConfig::FIXED_MINER_REWARD) {
			$amount = BlockchainConfig::FIXED_MINER_REWARD;
		}

		return $amount;
	}

	private function _toTransaction($transaction)
	{
		return Transaction::toObject($transaction);
	}

	public function getMapping() {
		return [
		    'transaction' => [
		    	'properties' => [
			        'amount' => [
			          'type' => 'long',
			        ],
			        'bankHash' => [
			          'type' => 'text',
			        ],
			        'configHash' => [
			          'type' => 'text',
			        ],
			        'amountWithFee' => [
			          'type' => 'long',
			        ],
			        'blockHeight' => [
			          'type' => 'long',
			        ],
			        'coinbase' => [
			          'type' => 'boolean',
			        ],
			        'createdAt' => [
			          'type' => 'long',
			        ],
			        'fee' => [
			          'type' => 'long',
			        ],
			        'from' => [
			          'type' => 'text'
			        ],
			        'hash' => [
			          'type' => 'text'
			        ],
			        'publicKey' => [
			          'type' => 'text'
			        ],
			        'signature' => [
			          'type' => 'text'
			        ],
			        'transfers' => [
			          'type' => 'text'
			        ],
			        'toDo' => [
			          'type' => 'text'
			        ],
			        'toDoHash' => [
			          'type' => 'text'
			        ],
			        'url' => [
			          'type' => 'text'
			        ],
			        'urlAction' => [
			          'type' => 'text'
			        ],
		    	],
		  	],
		];
	}
}
