<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\ES;

use Inescoin\Block;
use Inescoin\BlockchainConfig;
use Inescoin\LoggerService;
use Inescoin\Pow;

use DateTimeImmutable;

class ESBlockTempService extends ESService
{
	protected $type = 'block-temp';

	protected $index = 'blockchain-block-temp';

	private $transactionService;

	public $logger;

	private $walletBank = [];

	public function __construct($prefix = '') {
		$this->logger = (LoggerService::getInstance())->getLogger();

		$this->index = $prefix ? $prefix . '_' . $this->index : $this->index;
		parent::__construct();
	}

	public function index($id, $body, $refresh = false) {
		if (is_string($body)) {
			$body = (array) json_decode($body);
		}

		$this->logger->info("[ESBlockTempService][index] Id: $id | Body: " . serialize($body));

		if (!$this->exists($id)) {
			$this->bulkBlocks([$body]);
		}
	}

	public function bulkBlocks($blocks, $resetMode = false) {
		$blocksList = [];

		$this->logger->info('[ESBlockTempService] Start bank import...');

		if (is_array($blocks)) {
			$addressBalanceTo = [];
			$addressBalanceFrom = [];

			foreach ($blocks as $block) {
				$blocksList[$block['hash']] = $block;
			}

			if (!empty($blocksList)) {
				$this->bulkIndex($blocksList);
				$blocksList = [];
			}
		}

		return true;
	}

	public function getByHeight($id, $asArray = false) {
		$this->logger->info("[ESBlockTempService][getByHeight] Id: $id");
		$response = $this->get($id);

		if (isset($response['_source'])) {
			$block = $this->_toBlock((array) $response['_source']);
			return $asArray ? $block->getInfos() : $block;
		}

		return [];
	}

	public function getByHash($hash, $asArray = false) {
		$this->logger->info("[ESBlockTempService][getByHash] Hash: $hash");

		$result = $this->search([
			'hash' => $hash
		]);

		if (!isset($result['hits']['hits'][0])) {
			return null;
		}

		$block = $this->_toBlock($result['hits']['hits'][0]['_source']);
		return $asArray ? $block->getJsonInfos() : $block;
	}

	public function getChain($fromHeight, $toHeight, $formatted = true) {
		$this->logger->info("[ESBlockTempService][getChain] fromHeight: $fromHeight | toHeight: $toHeight");

		try {
			$result = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    'size' => 100,
			    'body' => [
			    	'query' => [
			    		"range" => [
				            "height" => [
				                "gte" => $fromHeight,
				                "lte" => $toHeight,
				                "boost" => 2.0
				            ]
				        ]
			        ],
			        "sort" => [
				    	"height" => "asc"
				  	],
			    ]
			]);
		} catch(\Exception $e) {
			$this->logger->error('[ESBlockService] ' . $e->getMessage());
		}

		if (!isset($result)) {
			return [];
		}

		$output = [];
		foreach ($result['hits']['hits'] as $hit) {
			$block = $this->_toBlock($hit['_source']);
			$output[] = $formatted ? $block : $block->getInfos();
		}

		return $output;
	}

	public function getCompressed($fromHeight, $toHeight) {
		$this->logger->info("[ESBlockTempService][getChain] fromHeight: $fromHeight | toHeight: $toHeight");

		try {
			$result = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    'size' => 100,
			    'body' => [
			    	'query' => [
			    		"range" => [
				            "height" => [
				                "gte" => $fromHeight,
				                "lte" => $toHeight,
				                "boost" => 2.0
				            ]
				        ]
			        ],
			        "sort" => [
				    	"height" => "asc"
				  	],
			    ]
			]);
		} catch(\Exception $e) {
			$this->logger->error('[ESBlockService] ' . $e->getMessage());
		}

		if (!isset($result)) {
			return [];
		}

		$output = [];
		foreach ($result['hits']['hits'] as $hit) {
			$block = $this->_toBlock($hit['_source']);
			$output[] = $block->compress();
		}

		return $output;
	}

	public function getTopHeight() {
		$this->logger->info('[ESBlockTempService][getTopHeight] Index: ' . $this->index . '  | Type: ' . $this->type);

		$height = 1;
		try {
			$result = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    "size" => 1,
			    'body' => [
			    	"stored_fields" => [
					    "height"
					],
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

			if (isset($result['hits']['hits'][0])) {
				$height = $result['hits']['hits'][0]['_id'];
			}
		} catch(\Exception $e) {
			$this->logger->error('[ESBlockService] ' . $e->getMessage());
		}

		return (int) $height;
	}

	public function getTopCumulativeDifficulty() {
		$this->logger->info('[ESBlockTempService][getTopCumulativeDifficulty] Index: ' . $this->index . '  | Type: ' . $this->type);

		$cumulativeDifficulty = 1;
		try {
			$result = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    "size" => 1,
			    'body' => [
			    	"stored_fields" => [
					    "cumulativeDifficulty"
					],
			        'query' => [
			            'match_all' => [
			            	"boost" => 1.2
			            ]
			        ],
			        "sort" => [
				    	"cumulativeDifficulty" => "desc"
				  	],
			    ]
			]);

			if (isset($result['hits']['hits'][0])) {
				$cumulativeDifficulty = $result['hits']['hits'][0]['sort'][0];
			}
		} catch(\Exception $e) {
			$this->logger->error('[ESBlockService] ' . $e->getMessage());
		}

		return (int) $cumulativeDifficulty;
	}

	public function getLastBlock($size = 1, $asArray = false, $page = 1)
	{
		$this->logger->info('[ESBlockTempService][getLastBlock] Index: ' . $this->index . '  | Type: ' . $this->type);

		$from = 0;
		if ($page > 1) {
			$from = ((int) $page * $size - 1) - $size;
		}

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
			$this->logger->error('[ESBlockService] ' . $e->getMessage());
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

	public function getLastBlocks($size = 1, $asArray = false, $page = 1)
	{
		$this->logger->info('[ESBlockTempService][getLastBlocks] Index: ' . $this->index . '  | Type: ' . $this->type);

		$from = 0;
		if ($page > 1) {
			$from = ((int) $page * $size - 1) - $size;
		}

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
			$this->logger->error('[ESBlockService] ' . $e->getMessage());
		}

		$output = [
			'total' => 0,
			'blocks' => []
		];

		if (!isset($result['hits']['hits'][0])) {
			return null;
		}

		$output['total'] = $result['hits']['total'];

		if ($size === 1) {
			$block = $this->_toBlock($result['hits']['hits'][0]['_source']);
			$output = $asArray ? $block->getJsonInfos() : $block;
		} else {
			foreach ($result['hits']['hits'] as $source) {
				$block = $this->_toBlock($source['_source']);
				$output['blocks'][] = $asArray ? $block->getJsonInfos() : $block;
			}
		}

		return $output;
	}

	private function _toBlock($block)
	{
		if (!$block) {
			return null;
		}

		return Block::toBlock($block);
	}

	public function getMapping() {
		return [
		    'block' => [
		    	'properties' => [
			        'countTotalTransaction' => [
			          'type' => 'long',
			        ],
			        'configHash' => [
			          'type' => 'text',
			        ],
			        'merkleRoot' => [
			          'type' => 'text',
			        ],
			        'countTransaction' => [
			          'type' => 'long',
			        ],
			        'createdAt' => [
			          'type' => 'long',
			        ],
			        'data' => [
			          'type' => 'text',
			        ],
			        'previousCumulativeDifficulty' => [
			          'type' => 'long',
			        ],
			        'cumulativeDifficulty' => [
			          'type' => 'long',
			        ],
			        'difficulty' => [
			          'type' => 'long',
			        ],
			        'hash' => [
			          'type' => 'text',
			        ],
			        'height' => [
			          'type' => 'long',
			        ],
			        'name' => [
			          'type' => 'text',
			        ],
			        'nonce' => [
			          'type' => 'text',
			        ],
			        'previousHash' => [
			          'type' => 'text',
			        ],
			        'symbol' => [
			          'type' => 'text',
			        ],
			        'status' => [
			          'type' => 'text',
			        ]
		      	],
		    ],
		];
	}
}
