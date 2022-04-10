<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\Model;

use Inescoin\Helper\Pow;
use Inescoin\Helper\PKI;

use Inescoin\BlockchainConfig;

use DateTimeImmutable;

class Message {

	private $fromWalletId = '';
	private $toWalletId = '';
	private $message = '';
	private $signature = '';
	private $publicKey = '';

	private $privateKey = '';

    private $configHash = '';
    private $createdAt;

	public function __construct($privateKey = null) {
		$this->privateKey = $privateKey;
		$this->configHash = BlockchainConfig::getHash();
	}

	public function getMessage() {
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
        	$this->configHash
        	. $this->fromWalletId
        	. $this->toWalletId
        	. $this->message
        	. $createdAt
        );
	}

	public function getFrom()
	{
		return $this->fromWalletId;
	}

	public function getTo()
	{
		return $this->toWalletId;
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

	public function getData() {
		return [
			'fromWalletId' => $this->fromWalletId,
			'toWalletId' => $this->toWalletId,
			'message' => $this->message,
			'publicKey' => $this->publicKey,
			'signature' => $this->signature,
		];
	}

	public function getDataDecrypted() {
		return [
			'fromWalletId' => $this->fromWalletId,
			'toWalletId' => $this->toWalletId,
			'message' => base64_decode($this->message),
			'publicKey' => $this->publicKey,
			'signature' => $this->signature,
		];
	}

	public function init($data) {
		if (!is_array($data) || empty($this->privateKey)) {
			return $this;
		}

		$this->fromWalletId = isset($data['fromWalletId']) ? (string) $data['fromWalletId'] : $this->fromWalletId;
		$this->toWalletId = isset($data['toWalletId']) ? (string) $data['toWalletId'] : $this->toWalletId;
		$this->publicKey = isset($data['publicKey']) ? (string) $data['publicKey'] : $this->publicKey;
		$this->message = isset($data['message']) ? (string) $data['message'] : $this->message;

		$this->createdAt = isset($data['createdAt']) ? $data['createdAt'] : new DateTimeImmutable();

		if ($this->createdAt instanceof DateTimeImmutable) {
        	$this->createdAt = $this->createdAt->getTimestamp();
        }

		$this->signature = isset($data['signature']) ? (string) $data['signature'] : NULL;

		if (!empty($this->privateKey) && NULL === $this->signature) {
			$this->signature = PKI::ecSign(bin2hex($this->getMessage()), $this->privateKey);
		}

		return $this;
	}

	public function setData($data) {
		$data = is_object($data) ? (array) $data : $data;

		$this->fromWalletId = isset($data['fromWalletId']) ? (string) $data['fromWalletId'] : $this->fromWalletId;
		$this->toWalletId = isset($data['toWalletId']) ? (string) $data['toWalletId'] : $this->toWalletId;
		$this->message = isset($data['message']) ? (string) $data['message'] : $this->message;
		$this->publicKey = isset($data['publicKey']) ? (string) $data['publicKey'] : $this->publicKey;

		$this->createdAt = isset($data['createdAt']) ? $data['createdAt'] : $this->createdAt;
		$this->signature = isset($data['signature']) ? (string) $data['signature'] : $this->signature;

        if ($this->createdAt instanceof DateTimeImmutable) {
        	$this->createdAt = $this->createdAt->getTimestamp();
        }

		return $this;
	}

	public function isValid() {
		return $this->imNotEmpty()
			&& PKI::ecVerify($this->getMessage(), $this->signature, $this->publicKey);
	}

	public function imNotEmpty() {
		return !empty($this->fromWalletId)
			&& !empty($this->toWalletId)
			&& !empty($this->message)
			&& !empty($this->publicKey)
			&& !empty($this->createdAt)
			&& !empty($this->signature);
	}

	public function getInfos() {
		return [
			'fromWalletId' => $this->fromWalletId,
			// 'hash' => $this->hash,
			'toWalletId' => $this->toWalletId,
			'message' => $this->message,
			'publicKey' => $this->publicKey,
			'createdAt' => $this->createdAt,
			'signature' => $this->signature,
		];
	}
}
