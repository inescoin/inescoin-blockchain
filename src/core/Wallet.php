<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

use Inescoin\Message;
use Inescoin\Transaction;

use GuzzleHttp\Client;

class Wallet {

	private $prefix = '';
	private $address;
	private $privateKey;
	private $publicKey;
	private $walletDirectory;

	protected $remoteInfos = [];
	protected $nodePublicKey = '';

	protected $client;

	protected $transactionPool = [];

	public function __construct($walletDirectory = '../data/wallet/', $host = 'https//node.inescoin.org/', $prefix = '')
	{
		$this->prefix = $prefix;

		$this->walletDirectory = $walletDirectory;

		$this->client = new \GuzzleHttp\Client([
			'base_uri' => $host,
			'request.options' => [
			     'exceptions' => false,
			]
		]);
	}

	public function getAddress()
	{
		return $this->address;
	}

	public function getPublicKey()
	{
		return $this->publicKey;
	}

	public function getPrivateKey()
	{
		return $this->privateKey;
	}

	public function getWalletDirectory() {
		return $this->walletDirectory;
	}

	public function generateWallet($name, $password, $saveToFile = true)
	{
		$key = hash('sha256', $password, true);

    	$keys[] = PKI::newEcKeys();

    	if (file_exists($this->walletDirectory . $name . '.address')) {
    		return $this->openWallet($name, $password);
    	}

    	$privWallet = PKI::encryptFromKey(serialize($keys), $key);

    	$this->address = $keys[0]['address'];
		$this->publicKey = $keys[0]['publicKey'];
		$this->privateKey = $keys[0]['privateKey'];

		if ($saveToFile) {
			file_put_contents($this->walletDirectory . $name . '.address', $keys[0]['address']);
			file_put_contents($this->walletDirectory . $name . '.wallet', $privWallet);
		}

		return $this;
    }

    public function openWallet($name, $password)
    {
		$key = hash('sha256', $password, true);

		$keys = unserialize(PKI::decryptFromKey(
			file_get_contents($this->walletDirectory . $name . '.wallet'),
			$key
		));

		$this->address = $keys[0]['address'];
		$this->publicKey = $keys[0]['publicKey'];
		$this->privateKey = $keys[0]['privateKey'];

		return $this;
    }

    public function prepareTransaction($transfers, $fee = BlockchainConfig::FIXED_TRANSACTION_FEE)
    {
		$this->checkRemoteInfos();
		$this->checkNodePublicKey();

		$transactionData = [
    		'from' => $this->getAddress(),
    		'transfers' => $transfers,
    		'publicKey' => $this->getPublicKey(),
    		'fee' => $fee
    	];

    	$transactionToSend = new Transaction($this->getPrivateKey(), $this->prefix);
    	$transactionToSend->init($transactionData);

    	$this->transactionPool[] = $transactionToSend->getInfos();
    	return $transactionToSend;
    }

    public function send() {
    	$b64 = base64_encode(json_encode($this->transactionPool));
    	$b64Split = str_split($b64, 20);

    	$output = [
    		'publicKey' => $this->publicKey,
    		'message' => []
    	];

    	$this->privateKey = str_replace('0x', '', $this->privateKey);

    	foreach ($b64Split as $part) {
    		$_part = PKI::encryptFromPublicKey($part, base64_decode($this->nodePublicKey));
    		$output['message'][] = [
    			'd' => bin2hex($_part),
    			's' => PKI::ecSign($_part, $this->privateKey)
    		];
    	}

    	return $this->_jsonRPC('POST', 'transaction', $output);
    }

    public function sendTransaction($transfers)
    {
		$transactionData = [
    		'from' => $this->getAddress(),
    		'transfers' => $transfers,
    		'publicKey' => $this->getPublicKey()
    	];

    	$transactionToSend = new Transaction($this->getPrivateKey(), $this->prefix);
    	$transactionToSend->init($transactionData);


    	$this->_jsonRPC('POST', 'mempool', $transactionToSend->getInfos());

    	return $transactionToSend;
    }

    public function sendMessage($publicAddress, $publicMessageKey, $message)
    {
    	$messageData = [
    		'from' => $this->getAddress(),
    		'to' => $publicAddress,
    		'message' => PKI::encryptFromPublicKey($message, base64_decode($publicMessageKey)),
    		'publicKey' => $this->getPublicKey()
    	];

    	$messageToSend = new Message($this->getPrivateKey());
    	$messageToSend->init($messageData);

    	$this->_jsonRPC('POST', 'z42', $messageToSend->getInfos());


    	return $messageToSend;
    }

    public function readMessage($messageData)
    {
    	$messageReceived = new Message($this->getPrivateKey());
    	$messageReceived->setData($messageData);

    	return PKI::decryptFromPrivateKey($messageData['message'], base64_decode($this->getPrivateMessageKey()));
    }

    public function getRemoteInfos()
    {
    	return $this->isRemoteInfos() ? $this->remoteInfos[$this->address] : [];
    }

    public function isRemoteInfos()
    {
    	return !empty($this->remoteInfos);
    }

    public function checkRemoteInfos()
    {
		$data = [
    		'walletAddresses' => $this->getAddress()
    	];

    	$this->remoteInfos = $this->_jsonRPC('POST', 'get-wallet-addresses-infos', $data);
    }

    public function checkNodePublicKey()
    {
		$response = $this->_jsonRPC('GET', 'public-key');

		$this->nodePublicKey = $response['publicKey'] ?? '';
    }

    public function getNodePublicKey()
    {
    	return $this->nodePublicKey;
    }

    private function _jsonRPC($method = 'POST', $uri = '', $params = [])
    {
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

	public function getKeys() {
		return [
			'address' => $this->address,
			'publicKey' => $this->publicKey,
			'privateKey' => $this->privateKey,
		];
	}
}
