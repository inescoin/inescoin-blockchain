<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

use Inescoin\ZeroPrefix;
use Inescoin\LoggerService;

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

		$this->logger = (LoggerService::getInstance())->getLogger();
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

	    			$this->logger->info('[MinerPool] ' . $timer . ' sec left for next empty block');
	    			sleep(10);
			    	continue;
			    }

	    		$difficulty = (int) $blockTemplate['difficulty'];
	    		$txCount = $blockTemplate['countTransaction'] ?? 1;

			    $this->logger->info('[MinerPool] Get new block template at height => ' . $blockTemplate['height']);
			    $this->logger->info('[MinerPool] Miner start at difficulty => ' . $difficulty);
			    $this->logger->info('[MinerPool] Tx count => ' . $txCount);

		    	$blockTemplate['nonce'] = 0;
			    while (true) {
		    		$hash = Block::calculateHashFromArray($blockTemplate);

		    		if ($this->hashDifficulty->hashMatchesDifficulty($hash, $difficulty)) {
		    			$response = $this->submitBlock($hash, $blockTemplate['nonce']);
		    			$this->logger->info('[MinerPool] Response : ' . $response . ' | hash : ' . $hash . ' | nonce : ' . $blockTemplate['nonce']);
		    			break;
		    		}

		    		++$blockTemplate['nonce'];
		    	}
    		} catch (ConnectException $e) {
			    $this->logger->error('[MinerPool] error => ' . $e->getMessage());
    			sleep(30);
    		}

    		// sleep(10);
    	}
    }

}
