<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\ES;

use Inescoin\Transaction;
use Inescoin\LoggerService;

use Inescoin\BlockchainConfig;

class ESWebsiteService extends ESService
{
	protected $type = 'website';

	protected $index = 'blockchain-website';

	public $logger;

	public function __construct($prefix = '') {
		$this->logger = (LoggerService::getInstance())->getLogger();
		$this->index = $prefix ? $prefix . '_' . $this->index : $this->index;
		parent::__construct();
	}

	public function getByUrl($url)
	{
		$response = $this->get($url);

		if (isset($response['error']) || !array_key_exists('_source', $response)) {
            return [];
        }

        return $response['_source'];
	}

	public function getByAddress($addressList = BlockchainConfig::NAME, $size = 100, $page = 0)
	{
		$from = 0;
		if ($page > 1) {
			$from = ((int) $page * $size - 1) - $size;
		}

		if (is_string($addressList)) {
			$addressList = [$addressList];
		}

		$addresses = implode(' OR ', $addressList);
		try {
			$result = $this->search([
				'ownerAddress' => $addresses
			]);
 		} catch (\Exception $e) {
			$result['error'] = $e->getMessage();
			var_dump('ERROR --> ' . self::class . ' | ' . $response['error']);
		}

		if (isset($result['error']) || !isset($result['hits']['hits'][0])) {
			return [];
		}

		$output['domainList'] = [];
		foreach ($result['hits']['hits'] as $hit) {
			$output['domainList'][] = $hit['_source'];
		}

		$output['total'] = $result['hits']['total']['value'];

		return $output;
	}

	public function getMapping() {
		return [
		    'website' => [
		    	'properties' => [
		    		'hash' => [
			          'type' => 'text',
			        ],
			        'url' => [
			          'type' => 'text',
			        ],
			        'body' => [
			          'type' => 'text',
			        ],
			        'ownerAddress' => [
			          'type' => 'text',
			        ],
			        'ownerPublicKey' => [
			          'type' => 'text',
			        ],
			        'signature' => [
			          'type' => 'text',
			        ],
			        'blockHeight' => [
			          'type' => 'long'
			        ],
			        'transsactionHash' => [
			          'type' => 'text'
			        ],
		    	],
		  	],
		];
	}
}
