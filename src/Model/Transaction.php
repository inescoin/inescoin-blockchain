<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\Model;

use DateTimeImmutable;
use Inescoin\BlockchainConfig;
use Inescoin\EC\AddressValidator;
use Inescoin\Helper\PKI;
use Inescoin\Helper\Pow;
use Inescoin\Manager\BlockchainManager;

class Transaction {

	private $fromWalletId = '';
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
	private $fixedFee = BlockchainConfig::FIXED_TRANSACTION_FEE;
	private $limitTransfers = 50;
    private $transfers = [];
    private $toDo = [];
    private $toDoHash = '';
    private $height = '';

    private $waletIdSize = 20;

	public function __construct($privateKey = null, $prefix = '')
	{
		$this->privateKey = $privateKey;

		$this->configHash = BlockchainConfig::getHash();
		$this->blockchainManager = BlockchainManager::getInstance($prefix);
	}

	public function getFromWalletId()
	{
		return $this->fromWalletId;
	}

	public function getTransfers() {
		return is_array($this->transfers)
			? base64_encode(json_encode($this->transfers))
			: $this->transfers;
	}

	public function getToDo() {
		return is_array($this->toDo)
			? base64_encode(json_encode($this->toDo))
			: $this->toDo;
	}

	public function getTransfersJson()
    {
        return json_decode(base64_decode($this->getTransfers(), true));
    }

    public function getTodoJson()
    {
        return  @json_decode(rawurldecode(base64_decode($this->getToDo())), true);
    }

    public function addToDo($toDo)
	{
		// action: 'create|renew|update|delete'
		// body: {}
		if (is_array($toDo)) {
			$this->toDo = $toDo;
		} else {
			$this->toDo = [$toDo];
		}

		$this->toDoHash = Pow::hash(($this->getToDo()));
	}

	public function addTransfers($transfers, $nonce = false)
	{
		if (is_array($transfers)) {
			$this->transfers = [];
			foreach ($transfers as $k => $transfer) {
				if (isset($transfer['amount']) && isset($transfer['toWalletId'])) {
					if (!$nonce) {
						$nonce = bin2hex( (string) mt_rand(11111, 99999) . (string) microtime(true))  . (string) mt_rand(11111, 99999);
					}

					if ($hash = Pow::hash($this->fromWalletId . $transfer['toWalletId'] . $transfer['amount'] . $nonce)) {
						$reference = '';

						if (isset($transfer['reference'])) {
							$reference = str_replace(['_','-', ' '], '', $transfer['reference']);
							$reference = ctype_alnum($reference)
								? substr($transfer['reference'], 0, $this->waletIdSize)
								: '';
						}

						$this->transfers[] = [
							'toWalletId' => $transfer['toWalletId'],
							'amount' => (int) $transfer['amount'],
							'nonce' => $nonce,
							'hash' => $hash,
							'reference' => $reference
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

	public function getHeight()
	{
		return $this->height;
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
        	. $this->fromWalletId
        	. $this->toDoHash
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
			'height' => $this->height,
			'fromWalletId' => $this->fromWalletId,
			'transfers' => $this->getTransfers(),
			'toDo' => $this->getToDo(),
			'toDoHash' => $this->toDoHash,
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
		$this->height = isset($data['height']) ? (string) $data['height'] : $this->height;
		$this->fromWalletId = isset($data['fromWalletId']) ? (string) $data['fromWalletId'] : $this->fromWalletId;
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

		if (isset($data['toDo'])) {
			$this->addToDo($data['toDo']);
		}

		if ($this->createdAt instanceof DateTimeImmutable) {
        	$this->createdAt = $this->createdAt->getTimestamp();
        }

		if (!$this->coinbase && !empty($this->privateKey) && NULL === $this->amountWithFee) {
			$this->fee = $this->fee >= $this->fixedFee ? $this->fee : $this->fixedFee;
		}

		if (!$this->coinbase && $this->fee === 0) {
        	$this->fee = $this->fixedFee;
        }

		if (!$this->coinbase) {
        	$this->amountWithFee = $this->amount + $this->fee;
        } else {
        	$this->amountWithFee = $this->amount;
        	$this->fee = 0;
        }

		$this->signature = isset($data['signature']) ? (string) $data['signature'] : NULL;

		$walletBank = $this->bankService->getAddressBalances($this->fromWalletId);

		if (!empty($walletBank) && empty($this->bankHash)) {
			$this->bankHash = $walletBank[$this->fromWalletId]['hash'];
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
		$this->height = isset($data['height']) ? (string) $data['height'] : $this->height;
		$this->fromWalletId = isset($data['fromWalletId']) ? (string) $data['fromWalletId'] : $this->fromWalletId;
		$this->transfers = isset($data['transfers']) ? $data['transfers'] : $this->transfers;
		$this->toDo = isset($data['toDo']) ? $data['toDo'] : $this->toDo;
		$this->toDoHash = isset($data['toDoHash']) ? $data['toDoHash'] : $this->toDoHash;
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

	public function isValid($checkTransfers = false)
	{
		$isWeb = false;

		if (!empty($this->toDo) && $this->toDo !== 'W10=') {
			$_todo = is_string($this->toDo)
				? @json_decode(rawurldecode(base64_decode($this->toDo)), true)
				: $this->toDo;

			if (!$_todo) {
				var_dump('[Transaction] [Todo] ERROR: Bad todo format');
				var_dump($this->toDo);
				return false;
			}

			$_todo = $_todo[0];

	        $action = $_todo['action'];
			$url = strtolower($_todo['name']);

            if ($action !== BlockchainConfig::WEB_ACTION_UPDATE && !in_array($this->amount, BlockchainConfig::WEB_COSTS_WITHOUT_UPDATE)) {
	            var_dump('[Transaction] [Todo] ' . "[$url - $action]" . ' ERROR: Bad domain amount: ' . $this->amount);
				return false;
	        }

            if ($action === BlockchainConfig::WEB_ACTION_UPDATE && $this->amount !== BlockchainConfig::WEB_COST_UPDATE) {
	            var_dump('[Transaction] [Todo] ' . "[$url - $action]" . ' ERROR: Bad domain amount 2');
				return false;
	        }

	        if (!ctype_alnum($url)) {
	        	var_dump('[Transaction] [Todo] ' . "[$url - $action]" . ' ERROR: Domain name not alphanumeric: ' . $url);
				return false;
	        }

            if (strlen($url) < BlockchainConfig::WEB_URL_MIN_SIZE) {
	        	var_dump('[Transaction] [Todo] ' . "[$url - $action]" . ' ERROR: Domain name too small < ' . BlockchainConfig::WEB_URL_MIN_SIZE);
				return false;
	        }

            if (strlen($url) > BlockchainConfig::WEB_URL_MAX_SIZE) {
	        	var_dump('[Transaction] [Todo] ' . "[$url - $action]" . ' ERROR: Domain name too big > ' . BlockchainConfig::WEB_URL_MAX_SIZE);
				return false;
	        }

	   //      $domainExists = $this->blockchainManager->getDomain()->exists($url, 'url');

	   //      if ($action === 'create') {
				// if ($domainExists) {
    //            	 	var_dump('[Transaction] [Todo] ' . "[$url - $action]" . ' ERROR: Domain already exists');
    //                 return false;
    //             }
	   //      } else {
    //             if (!$domainExists) {
    //            	 	var_dump('[Transaction] [Todo] ' . "[$url - $action]" . ' ERROR: Domain not found');
    //                 return false;
    //             }

    //             if ($action !== 'renew') {
	   //              $domain = $this->blockchainManager->getDomain()->selectFisrt($url, 'url');

	   //              if ($domain->getOwnerAddress() !== $this->fromWalletId) {
    //            	 		var_dump('[Transaction] [Todo] ' . "[$url - $action]" . ' ERROR: Action not authorized, ownerAddress not same');
	   //                  return false;
	   //              }

	   //              if ($domain->getOwnerPublicKey() !== $this->publicKey) {
    //            	 		var_dump('[Transaction] [Todo] ' . "[$url - $action]" . ' ERROR: Action not authorized, ownerPublicKey not same');
	   //                  return false;
	   //              }
	   //          }
    //         }

            $isWeb = true;
	    }

		if ($checkTransfers && !$this->isValidTransfers($isWeb)) {
			var_dump('ERROR: Invalid transfers');
			return false;
		}

		if (!$this->coinbase && !$this->publicKey && $this->fromWalletId !== BlockchainConfig::NAME) {
			var_dump('ERROR: Invalid publicKey');
			var_dump($this->getInfos());
			return false;
		}

		$valideAddress = AddressValidator::isValid($this->fromWalletId, $this->publicKey);
		if (!$this->coinbase && in_array($valideAddress, AddressValidator::INVALID)) {
			var_dump('ERROR: Invalid address FROM => [' . $valideAddress . '] ' . $this->fromWalletId);
			return false;
		}

		if ($this->amount < BlockchainConfig::MIN_TRANSACRTION_AMOUNT) {
			var_dump('ERROR: [MIN_TRANSACRTION_AMOUNT] Invalid amount to low => ' . $this->amount .' | ' . $this->fromWalletId);
			return false;
		}

		if ($this->amount > BlockchainConfig::MAX_TRANSACRTION_AMOUNT) {
			var_dump('ERROR: [MAX_TRANSACRTION_AMOUNT] Invalid amount to big => ' . $this->amount .' | ' . $this->fromWalletId);
			return false;
		}

		if (!$this->coinbase && $this->fee < BlockchainConfig::FIXED_TRANSACTION_FEE) {
			var_dump('ERROR: Low transaction fee => ' . $this->fee, (float) BlockchainConfig::FIXED_TRANSACTION_FEE);
			return false;
		}

		if (!$this->coinbase && !$this->imNotEmpty()) {
			var_dump('ERROR: Empty property', $this->getInfos());
			return false;
		}

		if (!$this->coinbase && !PKI::ecVerify($this->getHash(), $this->signature, $this->publicKey)) {
			var_dump('ERROR: Invalid signature', $this->getHash(), $this->getInfos());
			return false;
		}

		if ($this->isCoinbase() && (int) $this->getAmount() !== (int) BlockchainConfig::FIXED_MINER_REWARD) {
            return false;
        }

		return true;
	}

	public function isValidTransfers($isWeb = false) {
		$transfers = $this->getTransfersJson();

		$validMiners = [];
		if ($this->coinbase || $isWeb) {
			$validMiners = explode('|', BlockchainConfig::AUTHORIZED_MINERS);
		}

		if (count($transfers) > $this->limitTransfers) {
			var_dump('ERROR: Transfers limit by transaction is ' . $this->limitTransfers);
			return false;
		}

		foreach ($transfers as $transfer) {
			$transfer = (array) $transfer;

			$toWalletId = isset($transfer['toWalletId'])
				? $transfer['toWalletId']
				: $transfer['to'];

			if (($this->coinbase || $isWeb) && !in_array($toWalletId, $validMiners)) {
				var_dump('ERROR: [coinbase] Invalid address => ' . $toWalletId . ' excepeted => ' . BlockchainConfig::AUTHORIZED_MINERS);
				return false;
			}


			$reference = str_replace(['_','-', ' '], '', $transfer['reference']);
			if (!empty($reference) && !ctype_alnum($reference)) {
				var_dump('ERROR: Wallet Id alpha numeric, "-", " ", and "_" only, found => ' . $reference);
				return false;
			}

			if (!empty($reference) && strlen($reference) > $this->waletIdSize) {
				var_dump('ERROR: Wallet Id limit ' . $this->waletIdSize . ', found =>' . strlen($reference));
				return false;
			}

			if (empty($toWalletId)) {
				var_dump('ERROR: Empty Transfer to');
				return false;
			}

			if (empty($transfer['amount'])) {
				var_dump('ERROR: Empty Transfer amount');
				return false;
			}

			$hash = Pow::hash($this->fromWalletId . $toWalletId . $transfer['amount'] . $transfer['nonce']);

			if ($hash !== $transfer['hash']) {
				var_dump('ERROR: hash transfer invalid', $hash, $transfer, $this->getInfos());
				return false;
			}

			if ($this->fromWalletId === $toWalletId) {
				var_dump('ERROR: To address is same than FromWalletId address');
				return false;
			}

			$valideAddressTo = AddressValidator::isValid($toWalletId);
			if (in_array($valideAddressTo, AddressValidator::INVALID)) {
				var_dump('ERROR: Invalid address transfer to => [' . $valideAddressTo . '] ' . $toWalletId);
				return false;
			}
		}

		return true;
	}

	public function imNotEmpty()
	{
		return !empty($this->bankHash)
			&& !empty($this->configHash)
			&& !empty($this->fromWalletId)
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
			'height' => $this->height,
			'bankHash' => $this->bankHash,
			'fromWalletId' => $this->fromWalletId,
			'transfers' => $this->getTransfers(),
			'toDo' => $this->getToDo(),
			'toDoHash' => $this->toDoHash,
			'amount' => $this->amount,
			'amountWithFee' => $this->amountWithFee,
			'fee' => $this->fee,
			'coinbase' => $this->coinbase,
			'createdAt' => $this->createdAt,
			'publicKey' => $this->publicKey,
			'signature' => $this->signature,
		];
	}

	public function getJsonInfos()
	{
		$infos = $this->getInfos();
        $infos['transfers'] = $this->getTransfersJson();
        $infos['toDo'] = $this->getToDoJson();

        return $infos;
	}

	public static function toObject(array $arrayTransaction)
	{
		return (new self())->setData($arrayTransaction);
	}
}
