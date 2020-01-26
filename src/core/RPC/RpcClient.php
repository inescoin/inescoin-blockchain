<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\RPC;

use GuzzleHttp\Client;

class RpcClient {

	protected $client;

	private $host = '';

	public function __construct()
	{
	}

	public function request($host, $method = 'POST', $uri = '', $params = [])
    {
    	$this->_initClient('http://' . $host  .'/');

    	$allowedMethods = ['POST', 'GET'];

    	if (!in_array($method, $allowedMethods)) {
    		$method = 'GET';
    	}

    	$response = [];
    	try {
			$response = $this->client->request($method, $uri, [ 'json' => $params]);
    	} catch(\Exception $e) {
    		$response['error'] = $e->getMessage();
    	}

    	if (!is_array($response) && $response->getStatusCode() === 200) {
    		$response = (array) json_decode($response->getBody()->getContents());
    	}

    	return $response;
	}

	private function _initClient($host) {
		if ($this->host === $host) {
			return;
		}

		$this->client = new \GuzzleHttp\Client([
			'base_uri' => $host,
			'request.options' => [
			     'exceptions' => false,
			]
		]);

		$this->host = $host;
	}
}
