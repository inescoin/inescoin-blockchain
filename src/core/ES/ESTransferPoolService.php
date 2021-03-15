<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\ES;

use Inescoin\Transaction;
use Inescoin\LoggerService;

use Inescoin\BlockchainConfig;

class ESTransferPoolService extends ESService
{
	protected $type = 'transfer-pool';

	protected $index = 'blockchain-transfer-pool';
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
				    	"createdAt" => "desc"
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

	public function deleteByTransaction($txs)
	{
		$transactionHashs = implode(' OR ', $txs);

		$result = $this->search([
			'transactionHash' => $transactionHashs
		]);

		$out = [];
		if (!isset($result['error'])) {
			foreach ($result['hits']['hits'] as $address) {
				$out[] = $address['_id'];
			}

			$this->delete($out);
		}
	}

	public function getMapping() {
		return [
		    'transfer-pool' => [
		    	'properties' => [
		    		'createdAt' => [
			          'type' => 'long',
			        ],
			        'from' => [
			          'type' => 'text'
			        ],
			        'amount' => [
			          'type' => 'long',
			        ],
			        'fee' => [
			          'type' => 'long',
			        ],
			        'to' => [
			          'type' => 'text'
			        ],
			        'from' => [
			          'type' => 'text'
			        ],
			        'hash' => [
			          'type' => 'text'
			        ],
			        'transactionHash' => [
			          'type' => 'text'
			        ],
			        'nonce' => [
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
