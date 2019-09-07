<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

use Inescoin\Pow;
use Inescoin\PKI;

use Inescoin\BlockchainConfig;
use Inescoin\EC\AddressValidator;

use Inescoin\ES\ESService;

use DateTimeImmutable;

class Transaction {

	private $from = '';
	private $hash = '';
	private $amount = 0;
	private $amountWithFee = 0;
	private $bankHash = '';
	private $fee = 0;
	private $coinbase = false;
	private $signature = '';
	private $publicKey = '';
	private $privateKey = '';
    private $createdAt;
    private $configHash = '';
	private $genesisTransaction = false;
	private $fixedFee = 1000000;
	private $limitTransfers = 50;
    private $transfers = [];

	public function __construct($privateKey = null, $prefix = '')
	{
		$this->privateKey = $privateKey;

		$this->configHash = BlockchainConfig::getHash();
		$this->bankService = ESService::getInstance('bank', $prefix);
	}

	public function getFrom()
	{
		return $this->from;
	}

	public function getTransfers() {
		return is_array($this->transfers)
			? base64_encode(json_encode($this->transfers))
			: $this->transfers;
	}

	public function getTransfersJson()
    {
        return json_decode(base64_decode($this->getTransfers(), true));
    }

	public function addTransfers($transfers, $nonce = false)
	{
		if (is_array($transfers)) {
			$this->transfers = [];
			foreach ($transfers as $k => $transfer) {
				if (isset($transfer['amount']) && isset($transfer['to'])) {
					if (!$nonce) {
						$nonce = bin2hex( (string) mt_rand(11111, 99999) . (string) microtime(true))  . (string) mt_rand(11111, 99999);
					}

					if ($hash = Pow::hash($this->from . $transfer['to'] . $transfer['amount'] . $nonce)) {
						$walletId = '';
						if (isset($transfer['walletId'])) {
							$walletId = str_replace(['_','-', ' '], '', $transfer['walletId']);
							$walletId = ctype_alnum($walletId)
								? substr($transfer['walletId'], 0, 10)
								: '';
						}

						$this->transfers[] = [
							'to' => $transfer['to'],
							'amount' => (int) $transfer['amount'],
							'nonce' => $nonce,
							'hash' => $hash,
							'walletId' => $walletId
						];

						$this->amount += $transfer['amount'];
					}
				}
			}

			$this->amountWithFee = $this->amount;
		}

		return $this;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function getAmountWithoutFee()
	{
		return $this->amountWithFee;
	}

	public function getFee()
	{
		return $this->fee;
	}

	public function getBankHash()
	{
		return $this->bankHash;
	}

	public function getCoinbase()
	{
		return $this->coinbase;
	}

	public function isCoinbase()
	{
		return $this->coinbase;
	}

	public function getPublicKey()
	{
		return $this->publicKey;
	}

	public function getPrivateKey()
	{
		return $this->privateKey;
	}

	public function getSignature()
	{
		return $this->signature;
	}

    public function getHash()
    {
        $createdAt = null;
        if (is_array($this->createdAt) && isset($this->createdAt['date'])) {
        	$createdAt = (new DateTimeImmutable($this->createdAt['date']))->getTimestamp();
        }

        if (is_object($this->createdAt) && !($this->createdAt instanceof DateTimeImmutable)) {
        	$createdAt = (new DateTimeImmutable($this->createdAt->date))->getTimestamp();
        }

        if ($this->createdAt instanceof DateTimeImmutable) {
        	$createdAt = $this->createdAt->getTimestamp();
        }

        if (is_int($this->createdAt)) {
        	$createdAt = $this->createdAt;
        }

        return Pow::hash(
        	$this->bankHash
        	. $this->configHash
        	. $this->from
        	. $this->getTransfers()
        	. $this->amount
        	. $createdAt
        );
    }

	public function getData()
	{
		return [
			'bankHash' => $this->bankHash,
			'configHash' => $this->configHash,
			'hash' => $this->getHash(),
			'from' => $this->from,
			'transfers' => $this->getTransfers(),
			'amount' => $this->amount,
			'fee' => $this->fee,
			'createdAt' => $this->createdAt,
			'coinbase' => $this->coinbase,
			'publicKey' => $this->publicKey,
			'signature' => $this->signature,
		];
	}

	public function init($data)
	{
		if (!is_array($data) || empty($this->privateKey)) {
			return $this;
		}

		$this->bankHash = isset($data['bankHash']) ? (string) $data['bankHash'] : $this->bankHash;
		$this->configHash = isset($data['configHash']) ? (string) $data['configHash'] : $this->configHash;
		$this->from = isset($data['from']) ? (string) $data['from'] : $this->from;
		$this->publicKey = isset($data['publicKey']) ? (string) $data['publicKey'] : $this->publicKey;
		$this->amount = isset($data['amount']) ? (int) $data['amount'] : $this->amount;
		$this->fee = isset($data['fee']) ? (int) $data['fee'] : $this->fee;
		$this->amountWithFee = isset($data['amountWithFee']) ? (int) $data['amountWithFee'] : NULL;
		$this->coinbase = isset($data['coinbase']) ? (bool) $data['coinbase'] : false;
		$this->createdAt = isset($data['createdAt']) ? $data['createdAt'] : new DateTimeImmutable();
		$this->genesisTransaction = isset($data['genesisTransaction']) ? (bool) $data['genesisTransaction'] : false;

		if (isset($data['transfers'])) {
			$this->addTransfers($data['transfers']);
		}

		if ($this->createdAt instanceof DateTimeImmutable) {
        	$this->createdAt = $this->createdAt->getTimestamp();
        }

		if (!$this->coinbase && !empty($this->privateKey) && NULL === $this->amountWithFee) {
			$this->fee = $this->fee >= $this->fixedFee ? $this->fee : $this->fixedFee;
		}

		if (!$this->coinbase && NULL === $this->amountWithFee) {
        	$this->amountWithFee = $this->amount + $this->fixedFee;
        }

        if ($this->coinbase) {
        	$this->amountWithFee = $this->amount;
        	$this->fee = 0;
        }

		$this->signature = isset($data['signature']) ? (string) $data['signature'] : NULL;

		$walletBank = $this->bankService->getAddressBalances($this->from);

		if (!empty($walletBank)) {
			$this->bankHash = $walletBank[$this->from]['hash'];
		}

		if ($this->genesisTransaction || (!empty($this->privateKey) && NULL === $this->signature)) {
			$txHash = $this->getHash();
			$this->signature = $this->genesisTransaction ? $txHash : PKI::ecSign($txHash, $this->privateKey);
		}

		return $this;
	}

	public function setData($data)
	{
		$data = is_object($data) ? (array) $data : $data;

		$this->bankHash = isset($data['bankHash']) ? (string) $data['bankHash'] : $this->bankHash;
		$this->configHash = isset($data['configHash']) ? (string) $data['configHash'] : $this->configHash;
		$this->from = isset($data['from']) ? (string) $data['from'] : $this->from;
		$this->transfers = isset($data['transfers']) ? $data['transfers'] : $this->transfers;
		$this->amount = isset($data['amount']) ? (int) $data['amount'] : $this->amount;
		$this->fee = isset($data['fee']) ? (int) $data['fee'] : $this->fee;
		$this->amountWithFee = isset($data['amountWithFee']) ? (int) $data['amountWithFee'] : NULL;
		$this->coinbase = isset($data['coinbase']) ? $data['coinbase'] : $this->coinbase;
		$this->createdAt = isset($data['createdAt']) ? $data['createdAt'] : $this->createdAt;
		$this->publicKey = isset($data['publicKey']) ? (string) $data['publicKey'] : $this->publicKey;
		$this->signature = isset($data['signature']) ? (string) $data['signature'] : $this->signature;

		if ($this->createdAt instanceof DateTimeImmutable) {
        	$this->createdAt = $this->createdAt->getTimestamp();
        }

        if (!$this->coinbase && $this->fee === 0) {
        	$this->fee = $this->fixedFee;
        }

        if (!$this->coinbase && NULL === $this->amountWithFee) {
        	$this->amountWithFee = $this->amount + $this->fee;
        }

        if ($this->coinbase) {
        	$this->amountWithFee = $this->amount;
        	$this->fee = 0;
        }

		return $this;
	}

	public function isValid()
	{

		if (!$this->isValidTransfers()) {
			var_dump('ERROR: Invalid transfers');
			return false;
		}

		if (!$this->coinbase && !$this->publicKey && $this->from !== BlockchainConfig::NAME) {
			var_dump('ERROR: Invalid publicKey');
			var_dump($this->getInfos());
			return false;
		}

		$valideAddress = AddressValidator::isValid($this->from, $this->publicKey);
		if (!$this->coinbase && in_array($valideAddress, AddressValidator::INVALID)) {
			var_dump('ERROR: Invalid address FROM => [' . $valideAddress . '] ' . $this->from);
			return false;
		}

		if ($this->amount < BlockchainConfig::MIN_TRANSACRTION_AMOUNT) {
			var_dump('ERROR: [MIN_TRANSACRTION_AMOUNT] Invalid amount to low => ' . $this->amount .' | ' . $this->from);
			return false;
		}

		if ($this->amount > BlockchainConfig::MAX_TRANSACRTION_AMOUNT) {
			var_dump('ERROR: [MAX_TRANSACRTION_AMOUNT] Invalid amount to big => ' . $this->amount .' | ' . $this->from);
			return false;
		}

		if (!$this->coinbase && $this->fee < BlockchainConfig::FIXED_TRANSACTION_FEE) {
			var_dump('ERROR: Low transaction fee => ' . $this->fee, (float) BlockchainConfig::FIXED_TRANSACTION_FEE);
			return false;
		}

		if (!$this->coinbase && !$this->imNotEmpty()) {
			var_dump('ERROR: Empty property');
			return false;
		}

		if (!$this->coinbase && !PKI::ecVerify($this->getHash(), $this->signature, $this->publicKey)) {
			var_dump('ERROR: Invalid signature');
			return false;
		}

		return true;
	}

	public function isValidTransfers() {
		$transfers = $this->getTransfersJson();

		$validMiners = [];
		if ($this->coinbase) {
			$validMiners = explode('|', BlockchainConfig::AUTHORIZED_MINERS);
		}

		if (count($transfers) > $this->limitTransfers) {
			var_dump('ERROR: Transfers limit by transaction is ' . $this->limitTransfers);
			return false;
		}

		foreach ($transfers as $transfer) {
			$transfer = (array) $transfer;

			if ($this->coinbase && !in_array($transfer['to'], $validMiners)) {
				var_dump('ERROR: [coinbase] Invalid address =>' . $transfer['to'] . ' excepeted => ' . BlockchainConfig::AUTHORIZED_MINERS);
				return false;
			}

			$hash = Pow::hash($this->from . $transfer['to'] . $transfer['amount'] . $transfer['nonce']);

			$walletId = str_replace(['_','-', ' '], '', $transfer['walletId']);
			if (!empty($walletId) && !ctype_alnum($walletId)) {
				var_dump('ERROR: Wallet Id alpha numeric, "-", " ", and "_" only, found => ' . $walletId);
				return false;
			}

			if (!empty($walletId) && strlen($walletId) > 10) {
				var_dump('ERROR: Wallet Id limit 10, found =>' . strlen($walletId));
				return false;
			}

			if (empty($transfer['to'])) {
				var_dump('ERROR: Empty Transfer to');
				return false;
			}

			if (empty($transfer['amount'])) {
				var_dump('ERROR: Empty Transfer amount');
				return false;
			}

			if ($hash !== $transfer['hash']) {
				var_dump('ERROR: hash transfer invalid');
				return false;
			}

			if ($this->from === $transfer['to']) {
				var_dump('ERROR: To address is same than From address');
				return false;
			}

			$valideAddressTo = AddressValidator::isValid($transfer['to']);
			if (in_array($valideAddressTo, AddressValidator::INVALID)) {
				var_dump('ERROR: Invalid address transfer to => [' . $valideAddressTo . '] ' . $transfer['to']);
				return false;
			}
		}

		return true;
	}

	public function imNotEmpty()
	{
		return !empty($this->bankHash)
			&& !empty($this->configHash)
			&& !empty($this->from)
			&& !empty($this->transfers)
			&& !empty($this->amount)
			&& !empty($this->publicKey)
			&& !empty($this->signature);
	}

	public function getInfos()
	{
		return [
			'hash' => $this->getHash(),
			'configHash' => $this->configHash,
			'bankHash' => $this->bankHash,
			'from' => $this->from,
			'transfers' => $this->getTransfers(),
			'amount' => $this->amount,
			'amountWithFee' => $this->amountWithFee,
			'fee' => $this->fee,
			'coinbase' => $this->coinbase,
			'createdAt' => $this->createdAt,
			'publicKey' => $this->publicKey,
			'signature' => $this->signature,
		];
	}

	public function generateGenesisTansaction($address, $minerReward, $date = 'now')
	{
		$bankHash = Pow::hash('Hello Moon');

		$this->from = BlockchainConfig::NAME;
		$this->fee = 0;
		$this->createdAt = (new DateTimeImmutable($date))->getTimestamp();
		$this->coinbase = true;
		$this->publicKey = '';
		$this->signature = '';
		$this->bankHash = $bankHash;

		$this->addTransfers([[
			'to' => $address,
			'amount' => $minerReward,
		]], $bankHash);

		return $this;
	}


	public function generateCoinbaseTansaction($address, $minerReward, $date = 'now')
	{
		$this->from = BlockchainConfig::NAME;
		$this->fee = 0;
		$this->createdAt = (new DateTimeImmutable($date))->getTimestamp();
		$this->coinbase = true;
		$this->publicKey = '';
		$this->signature = '';

		$walletBank = $this->bankService->getAddressBalances($this->from);

		if (!empty($walletBank)) {
			$this->bankHash = $walletBank[$this->from]['hash'];
		}

		$this->addTransfers([[
			'to' => $address,
			'amount' => $minerReward,
		]]);

		return $this;
	}

	public static function toObject(array $arrayTransaction)
	{
		return (new self())->setData($arrayTransaction);
	}
}
