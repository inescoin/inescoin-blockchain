<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\RPC;

use Inescoin\BlockchainConfig;

class Cache {

	static $instance = null;

	public $cacheFolder = './cache/';

	public $cacheClearTimer = 900;

	public $cacheTimeout = 60;

	public function __construct($prefix = 'bob')
	{
		$this->cacheFolder = $this->cacheFolder . BlockchainConfig::NAME . '/' . $prefix . '/';
	}

	static function getInstance($name = BlockchainConfig::NAME) {
		if (null === self::$instance) {
			self::$instance = new self($name);
		}

		return self::$instance;
	}

	public function getCache($md5) {
		$this->checkCacheForlder();
		$this->clearTimeoutedCache();

		$filename = $this->cacheFolder . $md5 . '.json';

		if (!is_file($filename)) {
			return null;
		}

		$timeLeft = time() - filemtime($filename);
		if ($timeLeft > $this->cacheTimeout) {
			return null;
		}

		return @unserialize(file_get_contents($filename));
	}

	public function setCache($md5, $serialized) {
		$this->checkCacheForlder();

		$filename = $this->cacheFolder . $md5 . '.json';
		@file_put_contents($filename, serialize($serialized));
	}

	private function checkCacheForlder() {
		if (!is_dir($this->cacheFolder)) {
			@mkdir($this->cacheFolder, 0777, true);
		}
	}

	private function clearTimeoutedCache() {
		$output = array();

		$filetime = 'time.lock';
		$folderTimeFile = $this->cacheFolder . $filetime;

		if (!file_exists($folderTimeFile)) {
			@file_put_contents($folderTimeFile, '');
			return;
		}

		$time = time();
		if ($time - filemtime($folderTimeFile) > $this->cacheClearTimer) {
			$files = @glob($this->cacheFolder . '*');

			if ($files) {
				foreach($files  as $file) {
					$timeLeft = $time - filemtime($file);
					if ($timeLeft > ($this->cacheTimeout) && $folderTimeFile !== $file) {
						@unlink($file);
					}
				}
			}

			@unlink($folderTimeFile);
		}
	}
}
