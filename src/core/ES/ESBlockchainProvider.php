<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\ES;

class ESBlockchainProvider {

	private $blockService;
	private $transactionService;
	private $messageService;
	private $websiteService;
	private $productService;
	private $bankService;
	private $transferService;
	private $transferPoolService;
	private $domainService;
	private $todoService;

	private static $esBlockchainProviderInstance = null;

	public function __construct($prefix = '') {
		$this->blockService = ESService::getInstance('block', $prefix);
		$this->transactionService = ESService::getInstance('transaction', $prefix);
		$this->transactionPoolService = ESService::getInstance('transactionPool', $prefix);
		$this->messageService = ESService::getInstance('message', $prefix);
		$this->messagePoolService = ESService::getInstance('messagePool', $prefix);
		$this->peerService = ESService::getInstance('peer', $prefix);
		$this->bankService = ESService::getInstance('bank', $prefix);
		$this->transferService = ESService::getInstance('transfer', $prefix);
		$this->transferPoolService = ESService::getInstance('transferPool', $prefix);
		$this->domainService = ESService::getInstance('domain', $prefix);
		$this->websiteService = ESService::getInstance('website', $prefix);
		$this->todoService = ESService::getInstance('todo', $prefix);
	}

	public function blockService()
	{
		return $this->blockService;
	}

	public function bankService()
	{
		return $this->bankService;
	}

	public function transferService()
	{
		return $this->transferService;
	}

	public function transferPoolService()
	{
		return $this->transferPoolService;
	}

	public function peerService()
	{
		return $this->peerService;
	}

	public function transactionService()
	{
		return $this->transactionService;
	}

	public function messageService()
	{
		return $this->messageService;
	}

	public function transactionPoolService()
	{
		return $this->transactionPoolService;
	}

	public function messagePoolService()
	{
		return $this->messagePoolService;
	}

	public function domainService()
	{
		return $this->domainService;
	}

	public function websiteService()
	{
		return $this->websiteService;
	}

	public function todoService()
	{
		return $this->todoService;
	}

	public function resetAll($heigth) {
		if (!$heigth) {
			$this->blockService->reset();
			$this->transactionService->reset();
			$this->transactionPoolService->reset();
			$this->messageService->reset();
			$this->messagePoolService->reset();
			$this->todoService->reset();
			$this->domainService->reset();
			$this->websiteService->reset();
			// $this->peerService->reset();
			$this->bankService->reset();
			$this->transferService->reset();
			$this->transferPoolService->reset();
		} else {
			$this->blockService->deleteByQuery($heigth);
		}

	}

	public static function getInstance($prefix = '')
	{
		if (null === self::$esBlockchainProviderInstance) {
			self::$esBlockchainProviderInstance = new ESBlockchainProvider($prefix);
		}

		return self::$esBlockchainProviderInstance;
	}
}
