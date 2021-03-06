<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

use Inescoin\BlockchainConfig;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;


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

	public function getLogger()
	{
		return $this->logger;
	}

	static function getInstance($name = BlockchainConfig::NAME) {
		if (null === self::$instance) {
			self::$instance = new self($name);
		}

		return self::$instance;
	}
}
