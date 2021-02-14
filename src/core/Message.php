<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

use Inescoin\Pow;
use Inescoin\PKI;

use Inescoin\BlockchainConfig;

use DateTimeImmutable;

class Message {

	private $from = '';
	private $to = '';
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
        	. $this->from
        	. $this->to
        	. $this->message
        	. $createdAt
        );
	}

	public function getFrom()
	{
		return $this->from;
	}

	public function getTo()
	{
		return $this->to;
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
			'from' => $this->from,
			'to' => $this->to,
			'message' => $this->message,
			'publicKey' => $this->publicKey,
			'signature' => $this->signature,
		];
	}

	public function getDataDecrypted() {
		return [
			'from' => $this->from,
			'to' => $this->to,
			'message' => base64_decode($this->message),
			'publicKey' => $this->publicKey,
			'signature' => $this->signature,
		];
	}

	public function init($data) {
		if (!is_array($data) || empty($this->privateKey)) {
			return $this;
		}

		$this->from = isset($data['from']) ? (string) $data['from'] : $this->from;
		$this->to = isset($data['to']) ? (string) $data['to'] : $this->to;
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

		$this->from = isset($data['from']) ? (string) $data['from'] : $this->from;
		$this->to = isset($data['to']) ? (string) $data['to'] : $this->to;
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
		var_dump('$this->imNotEmpty() ==> ', $this->imNotEmpty());
		var_dump('Signature ==> ', PKI::ecVerify($this->getMessage(), $this->signature, $this->publicKey));
		return $this->imNotEmpty()
			&& PKI::ecVerify($this->getMessage(), $this->signature, $this->publicKey);
	}

	public function imNotEmpty() {
		return !empty($this->from)
			&& !empty($this->to)
			&& !empty($this->message)
			&& !empty($this->publicKey)
			&& !empty($this->createdAt)
			&& !empty($this->signature);
	}

	public function getInfos() {
		return [
			'from' => $this->from,
			// 'hash' => $this->hash,
			'to' => $this->to,
			'message' => $this->message,
			'publicKey' => $this->publicKey,
			'createdAt' => $this->createdAt,
			'signature' => $this->signature,
		];
	}
}
