<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

use Inescoin\ZeroPrefix;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

use DateTimeImmutable;

class MinerPool
{
	protected $client;
	protected $walletAddress;

	public function __construct($ip, $port, $walletAddress) {
		$this->client = new \GuzzleHttp\Client([
			'base_uri' => "$ip:$port/",
			'request.options' => [
			     'exceptions' => false,
			]
		]);

		$this->walletAddress = $walletAddress;

		$this->hashDifficulty = new ZeroPrefix();
	}

	private function _jsonRPC($method = 'POST', $uri = '', $params = [])
    {
    	$allowedMethods = ['POST', 'GET'];

    	if (!in_array($method, $allowedMethods)) {
    		$method = 'GET';
    	}

		return $this->client->request($method, $uri, [ 'json' => $params]);
	}

	protected function getBlockTemplate()
    {
		// sleep(1);
		return $this->_jsonRPC('POST', 'getBlockTemplate', [
    		'walletAddress' => $this->walletAddress,
    	]);
    }

    protected function submitBlock($hash, $nonce)
    {
		$response = $this->_jsonRPC('POST', 'submitBlockHash', [
    		'nonce' => $nonce,
    		'hash' => $hash,
    		'walletAddress' => $this->walletAddress,
    	]);

    	return $response->getBody()->getContents();
    }

    public function start()
    {
    	while (true) {
    		try {
    			$response = $this->getBlockTemplate();
		    	$blockTemplate = $response->getBody()->getContents();

		    	$blockTemplate = @json_decode($blockTemplate);
		    	if (!$blockTemplate) {
		    		sleep(10);
			    	continue;
			    }

			    $blockTemplate = (array) $blockTemplate;
			    if (isset($blockTemplate['error'])) {
			    	$timer = $blockTemplate['timeLeft'];
	    			var_dump($timer . ' sec left for next empty block');
	    			sleep(10);
			    	continue;
			    }

	    		$difficulty = (int) $blockTemplate['difficulty'];
	    		$txCount = $blockTemplate['countTransaction'] ?? 1;

			    var_dump('[MinerPool] Get new block template at height => ' . $blockTemplate['height']);
			    var_dump('[MinerPool] Miner start at difficulty => ' . $difficulty);
			    var_dump('[MinerPool] Tx count => ' . $txCount);

		    	$blockTemplate['nonce'] = 0;
			    while (true) {
		    		$hash = Block::calculateHashFromArray($blockTemplate);

		    		if ($this->hashDifficulty->hashMatchesDifficulty($hash, $difficulty)) {
		    			$response = $this->submitBlock($hash, $blockTemplate['nonce']);
		    			var_dump('[MinerPool] Nonce => ' . $blockTemplate['nonce']);
		    			var_dump('[MinerPool]', $response, $hash, $blockTemplate['nonce']);
		    			break;
		    		}

		    		++$blockTemplate['nonce'];
		    	}
    		} catch (ConnectException $e) {
	    	    var_dump($e->getMessage());
    		}

    		// sleep(10);
    	}
    }

}
