<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\RPC;

use GuzzleHttp\Client;

class RpcClient {

	protected $client = [];

	private $hosts = [];

	public function __construct()
	{
	}

	public function request($baseUri, $method = 'POST', $uri = '', $params = [], $port = '8087', $ssl = false)
    {
    	$host = $this->_initClient($baseUri, $port, $ssl);

    	$allowedMethods = ['POST', 'GET'];

    	if (!in_array($method, $allowedMethods)) {
    		$method = 'GET';
    	}

    	$response = [];
    	try {
			var_dump('[RpcClient] [request] [' . $method . '] Url: ' . $host . $uri);
			if (!empty($params)) {
				var_dump($params);
			}
			$response = $this->client[$host]->request($method, $uri, [ 'json' => $params]);
    	} catch(\Exception $e) {
    		$response['error'] = $e->getMessage();
    	}

    	if (!is_array($response) && $response->getStatusCode() === 200) {
    		$response = (array) json_decode($response->getBody()->getContents());
    	}

    	return $response;
	}

	private function _initClient($baseUri, $port, $ssl = false) {
		$port = !$ssl
			? ':' . $port
			: '';
		$protocol = !$ssl
			? 'http://'
			: 'https://';
		$host = $protocol . $baseUri . $port . '/';

		if (!array_key_exists($host, $this->client)) {
			var_dump('[RpcClient] [_initClient] base_uri: ' . $host);
			$this->client[$host] = new \GuzzleHttp\Client([
				'base_uri' => $host,
				'request.options' => [
				     'exceptions' => false,
				]
			]);
		}

		return $host;
	}
}
