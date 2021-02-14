<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\ES;

use Inescoin\BlockchainConfig;

class ESMessageService extends ESService
{
	protected $type = 'message';

	protected $index = 'blockchain-message';

	public function __construct($prefix = '') {
		$this->index = $prefix ? $prefix . '_' . $this->index : $this->index;
		parent::__construct();
	}

	public function getMapping() {
		return [
		    'message' => [
		    	'properties' => [
			        'blockHeight' => [
			          'type' => 'long',
			        ],
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
