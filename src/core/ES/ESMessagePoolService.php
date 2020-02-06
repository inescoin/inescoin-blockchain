<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\ES;

use Inescoin\BlockchainConfig;

class ESMessagePoolService extends ESService
{
	protected $type = 'message-pool';

	protected $index = 'blockchain-message-pool';

	public function __construct($prefix = '') {
		$this->index = $prefix ? $prefix . '_' . $this->index : $this->index;
		parent::__construct();
	}

	public function index($id, $body, $refresh = false)
	{
		return $this->_index($this->index, $this->type, $id, $body);
	}

	public function getMessagesByAddresses($address = BlockchainConfig::NAME, $size = 100, $page = 0)
	{
		$addresses = explode(',', $address);

		$addresses = implode(' OR ', $addresses);

		try {
			$result = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    "from" => $page,
			    "size" => $size,
			    'body' => [
			        'query' => [
			            "multi_match" => [
					      "query" => $addresses,
					      "type"=> "best_fields",
					      "fields" => [ "from", "to" ],
					      "operator" => "or"
					    ]
			        ]
			    ]
			]);
 		} catch (\Exception $e) {
			$result['error'] = $e->getMessage();
		}

		if (isset($result['error']) || !isset($result['hits']['hits'][0])) {
			return [];
		}

		$output['messages'] = [];
		foreach ($result['hits']['hits'] as $hit) {
			$output['messages'][] = $hit['_source'];
		}

		$output['total'] = $result['hits']['total']['value'];

		return $output;
	}

	public function getLastMessageCreatedAt() {
		$createdAt = 1;
		try {
			$result = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    "size" => 1,
			    'body' => [
			    	"stored_fields" => [
					    "createdAt"
					],
			        'query' => [
			            'match_all' => [
			            	"boost" => 1.2
			            ]
			        ],
			        "sort" => [
				    	"createdAt" => "desc"
				  	],
			    ]
			]);

			if (isset($result['hits']['hits'][0])) {
				$createdAt = $result['hits']['hits'][0]['sort'][0];
			}
		} catch(\Exception $e) {
			var_dump($e->getMessage());
		}

		return (int) $createdAt;
	}

	public function getLastMessagesPool($toCreatedAt = 1) {
		$toCreatedAt = $toCreatedAt ? $toCreatedAt : 1;

		$output = [
			'count' => 0,
			'messages' => []
		];

		try {
			$result = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    'size' => 100,
			    'body' => [
			    	'query' => [
			    		"range" => [
				            "createdAt" => [
				                "gt" => $toCreatedAt,
				                "boost" => 2.0
				            ]
				        ]
			        ],
			        "sort" => [
				    	"createdAt" => "asc"
				  	],
			    ]
			]);
		} catch(\Exception $e) {
			var_dump($e->getMessage());
		}

		if (!isset($result)) {
			return $output;
		}

		$output['count'] = $result['hits']['total']['value'];
		foreach ($result['hits']['hits'] as $source) {
			$output['messages'][] = (array) $source['_source'];
		}

		return $output;
	}

	public function getMapping() {
		return [
		    'message-pool' => [
		    	'properties' => [
			        'createdAt' => [
			          'type' => 'long'
			        ],
			        'from' => [
			          'type' => 'text'
			        ],
			        'to' => [
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
			        'message' => [
			          'type' => 'text'
			        ],
		    	],
		  	],
		];
	}
}
