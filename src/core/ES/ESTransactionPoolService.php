<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\ES;

use Inescoin\Transaction;
use Inescoin\LoggerService;
use Inescoin\BlockchainConfig;

class ESTransactionPoolService extends ESService
{
	protected $type = 'transaction-pool';

	protected $index = 'blockchain-transaction-pool';

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
		// var_dump("[ESTransactionPoolService][all] Index: $this->index  | Type: $this->type");

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
			        ]
			    ]
			]);
 		} catch (\Exception $e) {
 			$response['error'] = $e->getMessage();
			var_dump('ERROR --> ' . self::class . ' | ' . $response['error']);
		}

		if (isset($response['error'])) {
			return [
				'error' => $response['error'],
	            'count' => 0,
	            'transactions' => []
	        ];
		}

		$output = [];
		foreach ($response['hits']['hits'] as $hit) {
			$transaction = $this->_toTransaction($hit['_source']);
			$output[] = $formatted ? $transaction : $transaction->getInfos();
		}

		return [
            'count' => $response['hits']['total']['value'],
            'transactions' => $output
        ];
	}

	private function _toTransaction($transaction)
	{
		return Transaction::toObject($transaction);
	}

	public function getMapping()
	{
		return [
		    'transaction-pool' => [
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
			        'coinbase' => [
			          'type' => 'boolean',
			        ],
			        'createdAt' => [
			          'type' => 'long',
			        ],
			        'fee' => [
			          'type' => 'long'
			        ],
			        'from' => [
			          'type' => 'text'
			        ],
			        'hash' => [
			          'type' => 'text'
			        ],
			        'publicKey' => [
			          'type' => 'text',
			        ],
			        'signature' => [
			          'type' => 'text',
			        ],
			        'transfers' => [
			          'type' => 'text',
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
