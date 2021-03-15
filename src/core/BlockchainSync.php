<?php

// Copyright 2019 - 2020 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

use Inescoin\ES\ESBlockchainProvider;
use Inescoin\BlockchainConfig;
use Inescoin\Blockchain;
use Inescoin\Block;
use Inescoin\RPC\RpcClient;
use DateTimeImmutable;

use Inescoin\LoggerService;

class BlockchainSync
{

	protected $iteration = 0;

	protected $transferLimit = 100;

	protected $rpcClient;

	protected $blockchain;

	protected $lastMessageCreatedAt = 0;
	protected $lastCleanBlacklistAt = 0;
	protected $cleanBlacklistInterval = 90;

	// protected $peersConfig = [
	// 	'ssl' => true,
	// 	'rpcHost' => 'node.inescoin.org',
	// 	'rpcPort' => 80
	// ];

	protected $peersConfig = [
		'rpcHost' => '0.0.0.0',
		'rpcPort' => 8087
	];

	public $logger;

	protected $alreadyChecked = [];
	protected $blacklist = [];

	public function __construct(
		$prefix = BlockchainConfig::NAME,
		$name = BlockchainConfig::NAME,
		$loggerName = BlockchainConfig::NAME,
		$rpcBindIp,
		$rpcBindPort
	) {
		$this->logger = (LoggerService::getInstance($loggerName))->getLogger();

		$this->rpcClient = new RpcClient();
		$this->blockchain = new Blockchain('./', $prefix, true, true);

		$this->lastMessageCreatedAt = (new \DateTimeImmutable())->getTimestamp();
		$this->lastCleanBlacklistAt = (new \DateTimeImmutable())->getTimestamp();

		$this->peersConfig['rpcHost'] = $rpcBindIp;
		$this->peersConfig['rpcPort'] = $rpcBindPort;
	}

	public function run() {
		while (true) {
			$this->alreadyChecked = [];

			$peers = $this->blockchain->es->peerService()->getByTopCumulativeDifficulty();
			array_unshift($peers, $this->peersConfig);

			$lastMessageCreatedAt = $this->blockchain->es->messagePoolService()->getLastMessageCreatedAt();

			foreach ($peers as $peer) {
				$nowTimestamp = (new \DateTimeImmutable())->getTimestamp();

				if ($nowTimestamp > $this->cleanBlacklistInterval + $this->lastCleanBlacklistAt) {
					$this->blacklist = [];
					$this->lastCleanBlacklistAt = (new \DateTimeImmutable())->getTimestamp();

					$this->logger->info('[BlockchainSync] ' . count($this->blacklist) . ', Timer: ' . $this->lastCleanBlacklistAt . 'sec');
				}

				$ssl = isset($peer['ssl']);
				$remote = (!$ssl ? 'http://' : 'https://') . $peer['rpcHost'] . (!$ssl ? ':' . $peer['rpcPort'] : '');

				if (!(in_array($remote, $this->alreadyChecked) || in_array($remote, $this->blacklist))) {
					$currentBlockHeight = $this->blockchain->getTopHeight();

					$this->logger->info('[BlockchainSync] ' . 'Connecting to ' . $remote);

					$status = $this->rpcClient->request($peer['rpcHost'], 'GET', 'status', [], $peer['rpcPort'], $ssl);
					$remotePeers = $this->rpcClient->request($peer['rpcHost'], 'GET', 'peers', [], $peer['rpcPort'], $ssl);
					$messages = $this->rpcClient->request($peer['rpcHost'], 'POST', 'last-messages', [
						'timestamp' => $lastMessageCreatedAt
					], $peer['rpcPort'], $ssl);

					if (isset($messages['count']) && isset($messages['messages'])) {
						foreach ($messages['messages'] as $message) {
							if ($this->blockchain->pushMessage($message)) {
								// $this->logger->info('[BlockchainSync] ' . '[Success] message pushed');
							} else {
								// $this->logger->info('[BlockchainSync] ' . '[Error] message pushed');
							}
						}

						$this->logger->info('[BlockchainSync] ' . $messages['count'] . ' new message(s) : Timestamp => ' . $lastMessageCreatedAt);
					}

					foreach ($remotePeers as $index => $peer) {
		                $this->blockchain->es->peerService()->index($index, $peer);
					}

					if (!is_array($status) || !isset($status['height'])) {
						$this->logger->info('[BlockchainSync] ' . ' |xxx| Aborted connexion from ' . $remote);
						$this->blacklist[] = $remote;
						continue;
					}

					if (is_array($status) && isset($status['height']) && $currentBlockHeight < $status['height']) {
						$page = ceil($status['height'] / $this->transferLimit);
						$this->logger->info('[BlockchainSync] ' . $remote . ' | height => ' . $status['height']);

						$currentPos = !$currentBlockHeight ?  1 : $page - ceil($currentBlockHeight / $this->transferLimit);

						while ($status['height'] > $currentBlockHeight && !in_array($remote, $this->blacklist)) {
							$peer = (array) $peer;
							$blocks = $this->rpcClient->request($peer['rpcHost'], 'POST', 'get-blocks-by-height', [
								'height' => $currentBlockHeight + 1,
								'limit' => $this->transferLimit,
								'original' => 1
							],
							$peer['rpcPort'], $ssl);

							if (isset($blocks['error'])) {
								$this->logger->info('[BlockchainSync] ' . $blocks['error']);
								break;
							}

							$_blocks = [];
							foreach ($blocks as $block) {
								$_blocks[] = Block::toBlock((array) $block);
							}

							if (!empty($_blocks)) {
								if ($this->blockchain->bulkAdd($_blocks)) {
									$currentBlockHeight = $this->blockchain->getTopHeight();
									$currentPos++;
								} else {
									$this->logger->info('[BlockchainSync] ' . '| x ERROR x | > bulkAdd <');
									$this->blacklist[] = $remote;
								}
							}

							$currentBlockHeight = $this->blockchain->getTopHeight();
							sleep(3);
						}
					} else {
						if ((int) $currentBlockHeight === (int) $status['height']) {
							$this->logger->info('[BlockchainSync] ' . $remote . ' |  Synchro OK | ' . $status['height']);
						} else if ((int) $currentBlockHeight > (int) $status['height']) {
							$this->logger->info('[BlockchainSync] ' . $remote . ' | x ==> Synchro NOT OK <== x ! | ' . $status['height']);
							$this->blacklist[] = $remote;
						} else {
							$this->logger->info('[BlockchainSync] ' . $remote . ' | x ERROR x | ');
						}
					}

					// $this->alreadyChecked[] = $remote;
				}
			}

			$this->logger->info('[BlockchainSync] ' . '[' . $this->iteration++ . '] Waiting for next block: ' . $this->blockchain->getTopHeight());
			sleep(10);
		}
	}
}
