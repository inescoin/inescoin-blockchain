<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\ES;

use Elasticsearch\ClientBuilder;

use Inescoin\LoggerService;
use Inescoin\BlockchainConfig;

class ESService
{
	private static $esInstance = null;

	protected $type = '';

	protected $index = '';

	protected $prefix = '';

	public $client;

	public $logger;

	public function __construct($prefix = '') {
		$this->logger = (LoggerService::getInstance())->getLogger();

		$this->client = \Elasticsearch\ClientBuilder::create()
			->setHosts(['localhost:9200'])
			->build();

		$this->prefix = $prefix;

		$this->initIndex($prefix . $this->index);
	}

	public function initIndex($index = '', $nbShared = 2, $nbReplica = 0)
	{
		if (empty($index)) {
			$index = $this->prefix . $this->index;
		}

		// $this->logger->info("[ESService][_newIndex] Index: $index  | nbShared: $nbShared | $nbReplica: $nbReplica");

		$params = [
		    'index' => $index,
		    'include_type_name' => true,
		    'body' => [
		        'settings' => [
		            'number_of_shards' => $nbShared,
		            'number_of_replicas' => $nbReplica
		        ],
		    ]
		];

		if (!empty($this->getMapping())) {
			$params['body']['mappings'] = $this->getMapping();
		}

		$response = [];
		$alreadyExists = $this->client->indices()->exists(['index' => $index]);
		if (!$alreadyExists) {
			// var_dump("[ESService][_newIndex] Index: $index  | nbShared: $nbShared | $nbReplica: $nbReplica");
			$response = $this->client->indices()->create($params);
		} else  {
			// var_dump("[ESService][_newIndex | exists] Index: $index  | nbShared: $nbShared | $nbReplica: $nbReplica");
		}

		return $response;
	}

	protected function _removeIndex()
	{
		// $this->logger->info("[ESService][_removeIndex] Index: " . $this->index);
		if (!empty($this->index) && $this->client->indices()->exists(['index' => $this->index])) {
			return $this->client->indices()->delete([
			    'index' => $this->index
			]);
		}

		return false;

	}

	protected function _getIndexes($clean = false) {
		// $this->logger->info("[ESService][_getIndexes]");

		$mapping = $this->client->indices()->getMapping();

		$response = [];
		foreach ($mapping as $index => $value) {
			if (substr($index, 0, 1) !== '.') {
				$response[$index] = $value;

				if ($clean) {
					if (in_array($index, ['my_index'])) {
						// var_dump('Clean ==> ', $index);
						$this->removeIndex($index);
					}
				}
			}
		}

		return $response;
	}

	protected function _index($index, $type, $id, $body = [], $refresh = false) {
		// $this->logger->info("[ESService][_index] Index: $index  | Type: $type | Id: $id  | Refresh: " . (int) $refresh . " | Body: " . serialize($body));

		$response = [];
		try {
			$params = [
			    'index' => $index,
			    'type' => $type,
			    'id' => $id,
			    'body' => $body
			];

			if ($refresh) {
				$params['refresh'] = 'wait_for';
			}

			$response =  $this->client->index($params);
		} catch (\Exception $e) {
			$response['error'] = $e->getMessage();
			var_dump('ERROR --> ' . self::class . ' | ' . $response['error']);
		}

		return $response;
	}

	protected function _update($index, $type, $id, $body = [], $refresh = false) {
		// $this->logger->info("[ESService][_update] Index: $index  | Type: $type | Id: $id  | Refresh: " . (int) $refresh . " | Body: " . serialize($body));

		$response = [];
		try {
			$params = [
			    'index' => $index,
			    'type' => $type,
			    'id' => $id,
			    'body' => [ 'doc' => $body ]
			];

			if ($refresh) {
				$params['refresh'] = 'wait_for';
			}

			$response =  $this->client->update($params);
		} catch (\Exception $e) {
			$response['error'] = $e->getMessage();
			var_dump('ERROR --> ' . self::class . ' | ' . $response['error']);
		}

		return $response;
	}

	protected function _exists($index, $type, $id)
	{
		// $this->logger->info("[ESService][_exists] Index: $index  | Type: $type | Id: $id ");

		$response = [];
		try {
			$params = [
			    'index' => $index,
			    'type' => $type,
			    'id' => $id
			];

			$response =  $this->client->exists($params);
		} catch (\Exception $e) {
			$response['error'] = $e->getMessage();
			var_dump('ERROR --> ' . self::class . ' | ' . $response['error']);
		}

		return $response;
	}

	protected function _bulkIndex($index, $type, $data) {
		$params['refresh'] = "wait_for";
		$params['body'] = [];

		if (is_array($data)) {
			foreach ($data as $id => $body) {
				$params['body'][] = [
			        'index' => [
			            '_index' => $index,
			            '_type' => $type,
			            '_id' => $id,
			        ]
			    ];

			    $params['body'][] = $body;
			}
		}

		if (!empty($params['body'])) {
			try {
				$this->client->bulk($params);
			} catch (\Exception $e) {
				var_dump('ERROR --> ' . self::class . ' | ' . $e->getMessage());
			}
		}
	}

	protected function _get($index, $type, $id) {
		// $this->logger->info("[ESService][_get] Index: $index  | Type: $type | Id: $id");

		$response = [];

		try {
			$response = $this->client->get([
			    'index' => $index,
			    'type' => $type,
			    'id' => $id
			]);
		} catch (\Exception $e) {
			$response['error'] = $e->getMessage();
			var_dump('ERROR --> ' . self::class . ' | ' . $response['error']);
		}

		return $response;

	}

	protected function _delete($index, $type, $ids) {
		// var_dump("[ESService][_delete] Index: $index | Type: $type | Ids: " . serialize($ids));

		$params = [
			'body' => [],
			'refresh' => 'wait_for'
		];

		if (is_array($ids)) {
			foreach ($ids as $id) {
				$params ['body'][] = [
			        'delete' => [
			            '_index' => $index,
			            '_type' => $type,
			            '_id' => $id
			        ]
			    ];
			}
		} else {
			$params ['body'][] = [
		        'delete' => [
		            '_index' => $index,
		            '_type' => $type,
		            '_id' => $ids
		        ]
		    ];
		}

		if (!empty($params['body'])) {
			$response = $this->client->bulk($params);
		}
	}

	protected function _deleteByQuery($index, $type, $height, $key = 'height') {
		// $this->logger->info("[ESService][_deleteByQuery] Index: $index | Type: $type | Height: $height | Key: $key");

		try {
			$response = $this->client->deleteByQuery([
			    'index' => $this->index,
			    'type'  => $this->type,
			    'body'  => [
			        'query' => [
			            "range" =>  [
				            $key => [
				                "gte" => $height
				              ]
				          ]
				        ]
			        ]
			    ]);
		} catch (\Exception $e) {
			$response['error'] = $e->getMessage();
			var_dump('ERROR --> ' . self::class . ' | ' . $response['error']);
		}
		return $response;
	}

	protected function _search($index, $type, $matched = [], $size = 1000) {
		// $this->logger->info("[ESService][_search] Index: $index | Type: $type | Size: $size | Query: " . serialize($matched));

		try {
			$response = $this->client->search([
			    'index' => $index,
			    'type' => $type,
			    "from" => 0,
			    "size" => $size,
			    'body' => [
			        'query' => [
			            'match' => $matched
			        ]
			    ]
			]);
 		} catch (\Exception $e) {
			$response['error'] = $e->getMessage();
			$this->logger->error('[ESService] ERROR --> ' . $response['error']);
		}
		return $response;
	}

	protected function _all($index, $type) {
		// $this->logger->info("[ESService][_all] Index: $index  | Type: $type");

		try {
			$response = $this->client->search([
			    'index' => $index,
			    'type' => $type,
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
			$this->logger->error('[ESService] ERROR --> ' . $response['error']);
		}

		return $response;
	}

	public function index($id, $body, $refresh = true) {
		return $this->_index($this->index, $this->type, $id, $body, $refresh);
	}

	public function update($id, $body, $refresh = true) {
		return $this->_update($this->index, $this->type, $id, $body, $refresh);
	}

	public function exists($id) {
		return $this->_exists($this->index, $this->type, $id);
	}

	public function bulkIndex($data) {
		return $this->_bulkIndex($this->index, $this->type, $data);
	}

	public function reset() {
		$this->_removeIndex();
		return $this;
	}

	public function deleteByQuery($height, $key = 'height') {
		$this->_deleteByQuery($this->index, $this->type, $height, $key = 'height');
		return $this;
	}

	public function get($id) {
		return $this->_get($this->index, $this->type, $id);
	}

	public function delete($ids) {
		return $this->_delete($this->index, $this->type, $ids);
	}

	public function search($matched = []) {
		return $this->_search($this->index, $this->type, $matched);
	}

	public function all() {
		return $this->_all($this->index, $this->type);
	}

	public static function getInstance($type, $prefix = BlockchainConfig::NAME) {
     	$instanceName = "Inescoin\\ES\\ES" . ucfirst($type) . "Service";

    	if(null === self::$esInstance) {
       		self::$esInstance = new $instanceName($prefix);
    	} else if(self::$esInstance->type !== $type) {
       		self::$esInstance = new $instanceName($prefix);
    	}

		// self::$esInstance->logger->info('New instance: ' . $instanceName  . ' | Prefix: ' . $prefix);

    	return self::$esInstance;
   }

   public function getMapping() {
		return [];
	}
}
