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

	protected $client;

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

    private function _jsonRPC($method = 'POST', $uri = '', $params = [])
    {
    	$allowedMethods = ['POST', 'GET'];

    	if (!in_array($method, $allowedMethods)) {
    		$method = 'GET';
    	}

		return $this->client->request($method, $uri, [ 'json' => $params]);
	}

	public function getKeys() {
		return [
			'address' => $this->address,
			'publicKey' => $this->publicKey,
			'privateKey' => $this->privateKey,
		];
	}
}
