<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\ES;

use Inescoin\BlockchainConfig;

class ESBankService extends ESService
{
	protected $type = 'bank';

	protected $index = 'blockchain-bank';

	public $transactionService;
	public $transferService;

	public function __construct($prefix = '') {
		$this->transactionService = ESService::getInstance('transaction', $prefix);
		$this->transferService = ESService::getInstance('transfer', $prefix);
		$this->transferPoolService = ESService::getInstance('transferPool', $prefix);
		$this->domainService = ESService::getInstance('domain', $prefix);

		$this->index = $prefix ? $prefix . '_' . $this->index : $this->index;
		parent::__construct();
	}

	public function index($id, $body, $refresh = false)
	{
		return $this->_index($this->index, $this->type, $id, $body, $refresh);
	}

	public function updateAmount($id, $amount, $height, $increment = true, $hash = '')
	{
		$scriptSource = $increment ? '' : 'ctx._source.previousHash = ctx._source.hash; ctx._source.hash = params.hash;';

		$scriptSource = "ctx._source.amount " . ($increment ? "+=" : "-=") . " params.amount; ctx._source.lastHeight = params.height; $scriptSource";

		$amount = $amount / 1000000000;

		$params = [
			'id' => $id,
			'index' => $this->index,
			'type' => $this->type,
			'refresh' => 'wait_for',
			'body' => [
				'script' => [
					'source' => $scriptSource,
					'lang' => 'painless',
					'params' => [
						'amount' => (float) $amount,
						'height' => $height,
						'hash' => $hash
					]
				],
				'upsert' => [
		            'amount' => (float) ($id === BlockchainConfig::NAME ? -$amount : $amount),
		            'address' => $id,
		            'firstHeight' => $height,
		            'lastHeight' => $height,
		            'hash' => $hash
		        ]
			]

		];

		try {
			$res = $this->client->update($params);
		} catch (\Exception $e) {
			$this->logger->error('[ESBankService] [updateAmount] ERROR --> ' . $e->getMessage());
		}
	}

	public function incrementAmount($id, $amount, $height, $hash = '')
	{
		$this->logger->info('[ESBankService] [incrementAmount] id: ' .  $id . ' | amount: ' . $amount . ' | height: ' . $height . ' | hash: ' . $hash);
		$this->updateAmount($id, $amount, $height, true, $hash);
	}

	public function decrementAmount($id, $amount, $height, $hash = '')
	{
		$this->logger->info('[ESBankService] [decrementAmount] id: ' .  $id . ' | amount: ' . $amount . ' | height: ' . $height . ' | hash: ' . $hash);
		$this->updateAmount($id, $amount, $height, false, $hash);
	}

	public function getAddressBalances($addressList)
	{
		if (is_string($addressList)) {
			$addressList = [$addressList];
		}

		$addresses = implode(' OR ', $addressList);

		$result = $this->search([
			'address' => $addresses
		]);

		$out = [];
		if (!isset($result['error'])) {
			foreach ($result['hits']['hits'] as $address) {
				$sourceAddress = $address['_source']['address'];
				$out[$sourceAddress] = $address['_source'];
				$out[$sourceAddress]['amount'] = $out[$sourceAddress]['amount'] * 1000000000;

				if ($address['_source']['address'] !== BlockchainConfig::NAME && $out[$sourceAddress]['amount'] < 0) {
					$out[$sourceAddress]['amount'] = 0;
				}
			}
		}

		return $out;
	}

	public function getBankAmount($addressList)
	{
		$amount = 0;
		$wallet = $this->getAddressBalances($addressList);
		if (!empty($wallet)) {
			$amount = $wallet[BlockchainConfig::NAME]['amount'] * -1;
		}

		return $amount;
	}

	public function getWalletAddressInfos($walletAddress, $page = 1)
	{
		$response = $this->get($walletAddress);

		$infos = [
			'transfers' => []
		];

		if (isset($response['_source'])) {
			$infos = (array) $response['_source'];
			$infos['amount'] = $infos['amount'] * 1000000000;

			if ($infos['address'] !== BlockchainConfig::NAME && $infos['amount'] < 0) {
				$infos['amount'] = 0;
			}
		} else {
			return [
				'error' => 'NOT FOUND'
			];
		}

		$infos['transfers'] = $this->transferService->getWalletAddressHistory($walletAddress, 100, $page);
		$infos['transfersPool'] = $this->transferPoolService->getWalletAddressHistory($walletAddress, 100, $page);
		$infos['domains'] = $this->domainService->getByAddress($walletAddress, 100, $page);

		return $infos;
	}

	public function getMapping()
	{
		return [
		    'bank' => [
		    	'properties' => [
			        'amount' => [
			          'type' => 'double',
			        ],
			        'firstHeight' => [
			          'type' => 'long',
			        ],
			        'lastHeight' => [
			          'type' => 'long',
			        ],
			        'address' => [
			          'type' => 'text',
			        ],
			        'previousHash' => [
			          'type' => 'text',
			        ],
			        'hash' => [
			          'type' => 'text',
			        ],
			        'transactionHash' => [
			          'type' => 'text',
			        ],
			        'transferHash' => [
			          'type' => 'text',
			        ],
		    	],
		  	],
		];
	}
}
