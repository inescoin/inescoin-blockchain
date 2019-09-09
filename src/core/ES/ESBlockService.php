<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\ES;

use Inescoin\Block;
use Inescoin\BlockchainConfig;
use Inescoin\LoggerService;
use Inescoin\Pow;

use DateTimeImmutable;

class ESBlockService extends ESService
{
	protected $type = 'block';

	protected $index = 'blockchain-block';

	private $transactionService;

	private $logger;

	private $walletBank = [];

	public function __construct($prefix = '') {
		$this->logger = (LoggerService::getInstance())->getLogger();
		$this->transactionService = ESService::getInstance('transaction', $prefix);
		$this->bankService = ESService::getInstance('bank', $prefix);
		$this->transferService = ESService::getInstance('transfer', $prefix);

		$this->index = $prefix ? $prefix . '_' . $this->index : $this->index;
		parent::__construct();
	}

	public function index($id, $body, $refresh = false) {
		if (is_string($body)) {
			$body = (array) json_decode($body);
		}

		$this->logger->info("[ESBlockService][index] Id: $id | Body: " . serialize($body));

		if (!$this->exists($id)) {
			$this->bulkBlocks([$body]);
		}
	}

	public function bulkBlocks($blocks, $resetMode = false) {
		$blocksList = [];
		$transactionsList = [];
		$transfersList = [];


		$walletBank = $this->bankService->getAddressBalances(BlockchainConfig::NAME);

		if (empty($blocks)) {
			var_dump('[ESBlockService] [bulkBlocks] No block found');
			return false;
		}

		if (empty($walletBank) && isset($blocks[0]) && $blocks[0]['height'] === 0) {
			$this->walletBank[BlockchainConfig::NAME] =  [
				'amount' => 0,
        		'transactionHash' => '',
				'blockHeight' => 0,
        		'hash' => ''
			];
		} else {
			// if ($resetMode && !isset($walletBank[BlockchainConfig::NAME])) {
			// 	var_dump('[ESBlockService] [$walletBank] [$resetMode]');
			// 	exit();
			// }

			$this->walletBank[BlockchainConfig::NAME] = $walletBank[BlockchainConfig::NAME];

		}

		var_dump('[ESBlockService] Start bank import...');
		if (is_array($blocks)) {
			$addressBalanceTo = [];
			$addressBalanceFrom = [];

			foreach ($blocks as $block) {
				if ($this->exists($block['height']) && !$resetMode) {
					continue;
				}

				$blockFee = 0;
				$blocksList[$block['height']] = $block;
				$transactions = @json_decode(base64_decode($block['data']), true);
				if (!$transactions) {
					continue;
				}

				$holders = [];
				$minerReward = 0;
				$minerAddress = '';
				$totalBlockAmount = 0;
				$totalBlockTransactions = count($transactions);


				$minerTransaction = [];
				$minerTransfer = [];

				foreach ($transactions as $transaction) {
					$transactionsList[$transaction['hash']] = [
						"hash" => $transaction['hash'],
						"configHash" => $transaction['configHash'],
						"bankHash" => $transaction['bankHash'],
						"blockHeight" => $block['height'],
						"from" => $transaction['from'],
						"transfers" => $transaction['transfers'],
						"amount" => $transaction['amount'],
						"amountWithFee" => $transaction['amountWithFee'],
						"createdAt" => $transaction['createdAt'] ?? null,
						"coinbase" => $transaction['coinbase'] ?? false,
						"fee" => $transaction['fee'],
						"publicKey" => $transaction['publicKey'],
						"signature" => $transaction['signature'],
						'status' => 'pending'
					];

					$blockFee += $transaction['fee'];

					if ($transaction['coinbase'] && $transaction['from'] === BlockchainConfig::NAME) {
						if ($block['height'] !== 0 && $this->walletBank[BlockchainConfig::NAME]['hash'] !== $transaction['bankHash']) {
							var_dump('Bank Hash ERROR <---------------------------------------------> '. $block['height']);
							var_dump('------------------  ' . $this->walletBank[BlockchainConfig::NAME]['hash'] .' <=> ' . $transaction['bankHash'] . ' ------------------');
							// break 2;
							// exit();
						}

						$transfers = json_decode(base64_decode($transaction['transfers']), true);

						$minerAddress = $transfers[0]['to'];
						$minerReward = $transfers[0]['amount'];
						$minerTransfer = $transfers[0];
						$minerTransaction = $transaction;
					}

					if (!in_array($transaction['from'], $this->walletBank) && !in_array($transaction['from'], $holders)) {
						$holders[] = $transaction['from'];
					}
				}

				if (empty($minerTransaction)) {
					var_dump('!! FATAL ERROR !! - No miner transaction -', $transaction);
					exit();
				}

				$hash = $minerTransaction['from']
        			. (string) $block['height']
        			. $block['merkleRoot']
        			. $block['hash']
        			. (string) $block['createdAt']
        			. (string) ($minerTransfer['amount'] + $blockFee)
        			. $minerTransaction['hash']
        			. (string) $minerTransfer['amount']
        			. $minerTransfer['to']
					. (string) true;

				$addressBalanceTo[$minerAddress][] = [
					'blockFee' => $blockFee,
					'amount' => $minerTransfer['amount'] + $blockFee,
					'blockHeight' => $block['height'],
					'hash' => Pow::hash($hash)
				];

				// Miner Reward
				// $holders[] = BlockchainConfig::NAME;

				$holdersData = $this->bankService->getAddressBalances($holders);

				$this->walletBank = array_merge(
					$holdersData,
					$this->walletBank
				);

				foreach ($transactions as $transaction) {
		            $addressFrom = $transaction['from'];
		            if (!isset($this->walletBank[$addressFrom]) || $this->walletBank[$addressFrom]['amount'] <= 0 && BlockchainConfig::NAME !== $addressFrom) {
		            	continue;
		            }

		            if (BlockchainConfig::NAME === $addressFrom ||
		            	isset($this->walletBank[$addressFrom]) && (int) $this->walletBank[$addressFrom]['amount'] >= (int) $transaction['amountWithFee']
		            	&& $this->walletBank[$addressFrom]['amount'] > 0) {

		            	if (BlockchainConfig::NAME !== $addressFrom) {
			                $this->walletBank[$addressFrom]['amount'] -= ($transaction['amountWithFee']);
			            }

		                if (!array_key_exists($addressFrom, $addressBalanceFrom)) {
		                	$addressBalanceFrom[$addressFrom] = [];
			            }

			            $walletHash = Pow::hash(
	                			$transaction['from']
	                			. (string) $block['height']
	                			. $block['merkleRoot']
	                			. $block['hash']
	                			. (string) $block['createdAt']
	                			. (string) ($transaction['amountWithFee'])
	                			. $transaction['hash']
	                			. (string) $transaction['coinbase']);

			            $addressBalanceFrom[$addressFrom][] = [
	                		'amount' => $transaction['amountWithFee'],
							'blockHeight' => $block['height'],
	                		'hash' => $walletHash
	                	];

	                	$this->walletBank[$addressFrom]['hash'] = $walletHash;
	                	$this->walletBank[$addressFrom]['blockHeight'] = $block['height'];

		            	if (is_string($transaction['transfers'])) {
		            		$transfers = (array) json_decode(base64_decode($transaction['transfers']), true);
		            	} else {
		            		$transfers = $transaction['transfers'];
		            	}

		            	if (empty($transfers)) {
		            		var_dump('Empty transfer <---------------------------------------------> '. $block['height'] . ' ' . $transaction['hash']);
							break 2;
		            	}

		            	$transferInTransaction = [];
		            	foreach ($transfers as $transfer) {
		            		if (!$transactionsList[$transaction['hash']]['coinbase']) {
				            	if (!array_key_exists($transfer['to'], $addressBalanceTo)) {
				            		$addressBalanceTo[$transfer['to']] = [];
					            }

					            $bankWalletHash = Pow::hash(
		                			$transaction['from']
		                			. (string) $block['height']
		                			. $block['merkleRoot']
		                			. $block['hash']
		                			. (string) $block['createdAt']
		                			. (string) ($transaction['amount'] + $transaction['fee'])
		                			. $transaction['hash']
		                			. (string) $transfer['amount']
		                			. $transfer['to']
									. (string) $transaction['coinbase']);

					            $addressBalanceTo[$transfer['to']][] = [
									'amount' => $transfer['amount'],
									'blockHeight' => $block['height'],
									'hash' => $bankWalletHash
								];
		            		}

				            $transfersList[$transfer['hash']] = $transfer;
				            $transfersList[$transfer['hash']]['transactionHash'] = $transaction['hash'];
				            $transfersList[$transfer['hash']]['from'] = $transaction['from'];
				            $transfersList[$transfer['hash']]['height'] = $block['height'];
				            $transfersList[$transfer['hash']]['createdAt'] = $transaction['createdAt'];

				            $transferInTransaction[$transfer['hash']] = $transfersList[$transfer['hash']];
		            	}

		            	$transactionsList[$transaction['hash']]['transfers'] = json_encode($transfersList);
		            } else {
						var_dump("[ESBlockService] [bulkBlocks] Invalid amount spent: address => " . $addressFrom . " | amount => " . $transaction['amount'] . " | Height => " . $block['height']);
		            }
		        }

			}

			if (!empty($blocksList)) {
				$this->bulkIndex($blocksList);
				$blocksList = [];
			}

			if (!empty($transactionsList)) {
				$this->transactionService->bulkIndex($transactionsList);
				$transactionsList = [];
			}

			if (!empty($transfersList)) {
				$this->transferService->bulkIndex($transfersList);
				$transfersList = [];
			}

			foreach ($addressBalanceFrom as $address => $trans) {
				$amount = 0;
				$height = 0;
				$hash = '';
				foreach ($trans as $data) {
					$amount += $data['amount'];
					$height = $block['height'];
					$hash = $data['hash'];
				}

				if ($amount) {
					var_dump('[ESBlockService] [bulkBlocks] --Decrement amount | Address: ' . $address . ' | Amount: -' . $amount . ' | Height: ' . $height . ' | Hash: ' . $hash);
					$this->bankService->decrementAmount($address, $amount, $height, $hash);
				}
			}

			foreach ($addressBalanceTo as $address => $trans) {
				$amount = 0;
				$height = 0;
				$hash = '';
				foreach ($trans as $data) {
					$amount += $data['amount'];
					$height = $block['height'];
					$hash = $data['hash'];
				}

				if ($amount) {
					var_dump('[ESBlockService] [bulkBlocks] ++Increment amount | Address: ' . $address . ' | Amount: +' . $amount . ' | Height: ' . $height . ' | Hash: ' . $hash);
					$this->bankService->incrementAmount($address, $amount, $height, $hash);
				}
			}

			$addressBalanceTo = [];
			$addressBalanceFrom = [];
		}

		return true;
	}

	public function getByHeight($id, $asArray = false) {
		$this->logger->info("[ESBlockService][getByHeight] Id: $id");
		$response = $this->get($id);

		if (isset($response['_source'])) {
			$block = $this->_toBlock((array) $response['_source']);
			return $asArray ? $block->getInfos() : $block;
		}

		return [];
	}

	public function getByHash($hash, $asArray = false) {
		$this->logger->info("[ESBlockService][getByHash] Hash: $hash");

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
		$this->logger->info("[ESBlockService][getChain] fromHeight: $fromHeight | toHeight: $toHeight");

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
			var_dump($e->getMessage());
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
		$this->logger->info("[ESBlockService][getChain] fromHeight: $fromHeight | toHeight: $toHeight");

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
			var_dump($e->getMessage());
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
		$this->logger->info('[ESBlockService][getTopHeight] Index: ' . $this->index . '  | Type: ' . $this->type);

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
			var_dump($e->getMessage());
		}

		return (int) $height;
	}

	public function getTopCumulativeDifficulty() {
		$this->logger->info('[ESBlockService][getTopCumulativeDifficulty] Index: ' . $this->index . '  | Type: ' . $this->type);

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
			var_dump($e->getMessage());
		}

		return (int) $cumulativeDifficulty;
	}

	public function getLastBlock($size = 1, $asArray = false, $page = 1)
	{
		$this->logger->info('[ESBlockService][getLastBlock] Index: ' . $this->index . '  | Type: ' . $this->type);

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

	public function getLastBlocks($size = 1, $asArray = false, $page = 1)
	{
		$this->logger->info('[ESBlockService][getLastBlock] Index: ' . $this->index . '  | Type: ' . $this->type);

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
			var_dump($e->getMessage());
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
