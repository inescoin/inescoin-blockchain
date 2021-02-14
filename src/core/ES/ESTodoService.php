<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\ES;

use Inescoin\Transaction;
use Inescoin\LoggerService;

use Inescoin\BlockchainConfig;

class ESTodoService extends ESService
{
	protected $type = 'todo';

	protected $index = 'blockchain-todo';

	public $logger;

	public function __construct($prefix = '') {
		$this->logger = (LoggerService::getInstance())->getLogger();
		$this->index = $prefix ? $prefix . '_' . $this->index : $this->index;
		parent::__construct();
	}

	public function allByHeight()
	{

		try {
			$result = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    'from' => $from,
			    'size' => $size,
			    'body' => [
			    	'query' => [
			            'match_all' => [
			            	"boost" => 1.2
			            ]
			        ],
			        "sort" => [
				    	"height" => "desc"
				  	],
			    ]
			]);
		} catch(\Exception $e) {
			var_dump($e->getMessage());
		}

		if (!isset($result['hits']['hits'][0])) {
			return null;
		}

		$output = [];
		if ($size === 1) {
			$block = $this->_toBlock($result['hits']['hits'][0]['_source']);
			$output = $asArray ? $block->getJsonInfos() : $block;
		} else {
			foreach ($result['hits']['hits'] as $source) {
				$block = $this->_toBlock($source['_source']);
				$output[] = $asArray ? $block->getJsonInfos() : $block;
			}
		}

		return $output;
	}

	public function all() {
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
				    	"blockHeight" => "asc"
				  	],
			    ]
			]);
 		} catch (\Exception $e) {
 			$response['error'] = $e->getMessage();
			var_dump('ERROR --> ' . self::class . ' | ' . $response['error']);
		}

		return $response;
	}

	public function getMapping() {
		return [
		    'todo' => [
		    	'properties' => [
		    		'hash' => [
			          'type' => 'text',
			        ],
			        'ownerAddress' => [
			        	'type' => 'text'
			        ],
			        'ownerPublicKey' => [
			        	'type' => 'text'
			        ],
			        'command' => [
			          'type' => 'text',
			        ],
			        'blockHeight' => [
			          'type' => 'long',
			        ],
			        'amount' => [
			          'type' => 'text',
			        ],
			        'transactionHash' => [
			          'type' => 'text',
			        ],
			        'createdAt' => [
			          'type' => 'long',
			        ],
		    	],
		  	],
		];
	}
}
