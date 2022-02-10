<?php

// Copyright 2019-2022 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\Service;

use Inescoin\BlockchainConfig;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LoggerService
{
	private $logger;

	private $loggerFile = BlockchainConfig::NAME;

	static $instance = null;

	public function __construct($name = BlockchainConfig::NAME)
	{
		$name = str_replace('.log', '', $name);

		$this->logger = new Logger($name);
        $this->logger->pushHandler(new StreamHandler('./' . $name . '.log', Logger::DEBUG));
        $this->logger->pushHandler(new FirePHPHandler());
	}

	public function getLogger(): LoggerInterface
	{
		return $this->logger;
	}

	/**
	 * @param string $name
	 */
	static function getInstance(string $name = BlockchainConfig::NAME): LoggerService
	{
		if (null === self::$instance) {
			self::$instance = new self($name);
		}

		return self::$instance;
	}
}
