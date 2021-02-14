<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

use Inescoin\Message;
use Inescoin\Transaction;
use Inescoin\PKI;
use GuzzleHttp\Client;

class Website {

    private $bankHash = '';
	private $prefix = '';
	private $address;
	private $privateKey;
	private $publicKey;
	private $walletDirectory;

	protected $remoteInfos = [];
	protected $nodePublicKey = '';

	protected $client;

    private $websitePool = [];
    private $categories = [];
    private $tags = [];
    private $products = [];

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
            'bankHash' => $this->getBankHash(),
    		'from' => $this->getAddress(),
    		'transfers' => $transfers,
    		'publicKey' => $this->getPublicKey(),
    		'fee' => $fee
    	];

    	$transactionToSend = new Transaction($this->getPrivateKey(), $this->prefix);
    	$transactionToSend->init($transactionData);

    	$this->transactionPool = $transactionToSend->getInfos();
    	return $transactionToSend;
    }

    public function send() {
        if (empty($this->remoteInfos)) {
            throw new Exception("[ERROR] Empty remote infos", 1);
        }

        if (empty($this->nodePublicKey)) {
            throw new Exception("[ERROR] Empty nodePublicKey", 1);
        }

        $output = PKI::encryptForNode($this->transactionPool, $this->publicKey, $this->privateKey, $this->nodePublicKey);

        $this->transactionPool = [];
    	return $this->_jsonRPC('POST', 'transaction', $output);
    }

    public function getRemoteInfos()
    {
    	$res = isset($this->remoteInfos[$this->address]) ? $this->remoteInfos[$this->address] : [];
        return $this->isRemoteInfos() ? $res : [];
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

        $response = $this->_jsonRPC('POST', 'get-wallet-addresses-infos', $data);

        if (isset($response['error'])) {
            die($response['error']. PHP_EOL);
        }

        if (empty($response)) {
            die('[ERROR] REMOTE NODE - Wallet address not found.' . PHP_EOL);
        }

        $this->remoteInfos = $response;
        $remoteInfos = $this->getRemoteInfos();
        $this->bankHash = !empty($remoteInfos) ? $remoteInfos->hash : '';
    }

    public function checkNodePublicKey()
    {
		$response = $this->_jsonRPC('GET', 'public-key');

		$this->nodePublicKey = $response['publicKey'] ?? '';
    }

    public function getCategories($publicAddress) {
        if (isset($this->categories[$publicAddress])) {
            return $this->categories[$publicAddress];
        }

        return [];
    }

    public function addCategory($category, $publicAddress) {
        if (!isset($this->categories[$publicAddress])) {
            $this->categories[$publicAddress] = [];
        }

        $categoryHash = md5($category);
        if (!in_array($categoryHash, $this->categories[$publicAddress])) {
            $this->categories[$publicAddress][$categoryHash] = $category;
        }
    }

    public function updateCategory($oldName, $newName, $publicAddress) {
        if (!isset($this->categories[$publicAddress])) {
            $this->categories[$publicAddress] = [];
        }
        if (in_array($oldName, $this->categories[$publicAddress])) {
            $position = array_search($oldName, $this->categories[$publicAddress]);
            $this->categories[$publicAddress][$position] = $newName;
        }
    }
    public function removeCategory($category, $publicAddress) {
        if (!isset($this->categories[$publicAddress])) {
            return;
        }

        $position = array_search($category, $this->categories[$publicAddress]);

        if (isset($this->categories[$publicAddress][$position])) {
            unset($this->categories[$publicAddress][$position]);
        }
    }

    public function getTags($publicAddress) {
        if (isset($this->tags[$publicAddress])) {
            return $this->tags[$publicAddress];
        }

        return [];
    }

    public function addTag($tag, $publicAddress) {
        if (!isset($this->tags[$publicAddress])) {
            $this->tags[$publicAddress] = [];
        }

        if (!in_array($tagHash, $this->tags[$publicAddress])) {
            $this->tags[$publicAddress][$tag] = $tag;
        }
    }

    public function removeTag($tag, $publicAddress) {
        if (!isset($this->tags[$publicAddress])) {
            return;
        }

        if (isset($this->tags[$publicAddress][$tag])) {
            unset($this->tags[$publicAddress][$tag]);
        }
    }

    public function getProducts($publicAddress) {
        if (isset($this->products[$publicAddress])) {
            return $this->products[$publicAddress];
        }

        return [];
    }

    public function create($data): self {

        if (!isset($data['url'])) {
            var_dump('[ERROR] $data[\'url\'] not set');
            return $this;
        }

        if (!is_string($data['url']) ) {
            var_dump('[ERROR] $data[\'url\'] not string');
            return $this;
        }

        if (strlen($data['url']) <= 10) {
            var_dump('[ERROR] $data[\'url\'] must length > 10');
            return $this;
        }

        $hash = Pow::hash('create-' . $data['url'] . $this->address . $this->publicKey);

        $this->addToPool([
            'type' => 'create',
            'hash' => $hash,
            'url' => $data['url'],
            'ownerAddress' => $this->address,
            'ownerPublicKey' => $this->publicKey,
            'signature' => PKI::ecSign($hash, $this->privateKey),
        ]);

        return $this;
    }

    public function update($data): self {

        if (!isset($data['url'])) {
            var_dump('[ERROR] $data[\'url\'] not set');
            return $this;
        }

        if (!is_string($data['url']) ) {
            var_dump('[ERROR] $data[\'url\'] not string');
            return $this;
        }

        if (strlen($data['url']) <= 10) {
            var_dump('[ERROR] $data[\'url\'] must length > 10');
            return $this;
        }

        $hash = Pow::hash('create-' . $data['url'] . $this->address . $this->publicKey);

        $website = [
            'type' => 'update',
            'hash' => $hash,
            'url' => $data['url'],
            'meta' => [],
            'ownerAddress' => $this->address,
            'ownerPublicKey' => $this->publicKey,
            'signature' => PKI::ecSign($hash, $this->privateKey),
        ];

        if (isset($data['meta']) && is_array($data['meta'])) {
            $website['meta'] = $data['meta'];
        }

        $this->addToPool($website);

        return $this;
    }

    public function delete($data): self {

        if (!isset($data['url'])) {
            var_dump('[ERROR] $data[\'url\'] not set');
            return $this;
        }

        if (!is_string($data['url']) ) {
            var_dump('[ERROR] $data[\'url\'] not string');
            return $this;
        }

        if (strlen($data['url']) <= 10) {
            var_dump('[ERROR] $data[\'url\'] must length > 10');
            return $this;
        }

        $hash = Pow::hash('create-' . $data['url'] . $this->address . $this->publicKey);
        $deleteHash = Pow::hash('create-' . $hash);

        $this->addToPool([
            'type' => 'delete',
            'hash' => $hash,
            'url' => $data['url'],
            'deleteHash' => $deleteHash,
            'ownerAddress' => $this->address,
            'ownerPublicKey' => $this->publicKey,
            'signature' => PKI::ecSign($deleteHash, $this->privateKey),
        ]);

        return $this;
    }

    public function addToPool($data) {
        switch ($data['type']) {
            case 'create':
                var_dump('creating website...');
                $this->websitePool[$data['hash']][] = [
                    'type' => $data['type'],
                    'url' => $data['url'],
                    'hash' => $data['hash'],
                    'ownerAddress' => $data['ownerAddress'],
                    'ownerPublicKey' => $data['ownerPublicKey'],
                    'signature' => $data['signature'],
                ];
                break;

            case 'update':
                var_dump('updating website...');
                $this->websitePool[$data['hash']][] = [
                    'type' => $data['type'],
                    'url' => $data['url'],
                    'hash' => $data['hash'],
                    'meta' => $data['meta'],
                    'ownerAddress' => $data['ownerAddress'],
                    'ownerPublicKey' => $data['ownerPublicKey'],
                    'signature' => $data['signature'],
                ];
                break;

            case 'delete':
                var_dump('deleting website...');
                $this->websitePool[$data['hash']][] = [
                    'type' => $data['type'],
                    'url' => $data['url'],
                    'hash' => $data['hash'],
                    'deleteHash' => $data['deleteHash'],
                    'ownerAddress' => $data['ownerAddress'],
                    'ownerPublicKey' => $data['ownerPublicKey'],
                    'signature' => $data['signature'],
                ];
                break;

            default:
                # nothing do to
                var_dump($data['type']);
                break;
        }
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

    public function getBankHash() {
        return $this->bankHash;
    }


    public function getWebsitePool() {
        return $this->websitePool;
    }

	public function getKeys() {
		return [
			'address' => $this->address,
			'publicKey' => $this->publicKey,
			'privateKey' => $this->privateKey,
		];
	}
}
