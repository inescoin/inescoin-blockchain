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

class BlockchainSync
{

	protected $iteration = 0;

	protected $transferLimit = 100;

	protected $rpcClient;

	protected $blockchain;

	protected $peersConfig = [
		'ssl' => true,
		'rpcHost' => 'node.inescoin.org',
		'rpcPort' => 80
	];

	protected $alreadyChecked = [];
	protected $blacklist = [];

	public function __construct($prefix = 'inescoin') {
		$this->rpcClient = new RpcClient();
		$this->blockchain = new Blockchain('./', $prefix, true, true);
	}

	public function run() {
		while (true) {
			$this->alreadyChecked = [];

			$peers = $this->blockchain->es->peerService()->getByTopCumulativeDifficulty();
			array_unshift($peers, $this->peersConfig);

			foreach ($peers as $peer) {
				$ssl = isset($peer['ssl']);
				$remote = (!$ssl ? 'http://' : 'https://') . $peer['rpcHost'] . (!$ssl ? ':' . $peer['rpcPort'] : '');

				if (!(in_array($remote, $this->alreadyChecked) || in_array($remote, $this->blacklist))) {
					$currentBlockHeight = $this->blockchain->getTopHeight();

					var_dump('|-----> Connect to ' . $remote . '...');

					$status = $this->rpcClient->request($peer['rpcHost'], 'GET', 'status', [], $peer['rpcPort'], $ssl);
					$remotePeers = $this->rpcClient->request($peer['rpcHost'], 'GET', 'peers', [],$peer['rpcPort'], $ssl);

					foreach ($remotePeers as $index => $peer) {
		                $this->blockchain->es->peerService()->index($index, $peer);
					}

					if (!isset($status['height'])) {
						var_dump('<------| Aborted connexion with ' . $remote . '...', $status);
						continue;
					}

					if (is_array($status) && isset($status['height']) && $currentBlockHeight < $status['height']) {

						$page = ceil($status['height'] / $this->transferLimit);
						var_dump($remote . ' | height => ' . $status['height'], $page);

						$currentPos = !$currentBlockHeight ?  1 : $page - ceil($currentBlockHeight / $this->transferLimit);

						while ($status['height'] > $currentBlockHeight && !in_array($remote, $this->blacklist)) {
							$blocks = $this->rpcClient->request($peer['rpcHost'], 'POST', 'get-blocks-by-height', [
								'height' => $currentBlockHeight + 1,
								'limit' => $this->transferLimit,
								'original' => 1
							],
							$peer['rpcPort'], $ssl);

							if (isset($blocks['error'])) {
								var_dump($blocks['error']);
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
									var_dump('| x ERROR x | > bulkAdd <');
									$this->blacklist[] = $remote;
								}
							}

							$currentBlockHeight = $this->blockchain->getTopHeight();
							sleep(3);
						}
					} else {
						if ((int) $currentBlockHeight === (int) $status['height']) {
							var_dump($remote . ' |  Synchro OK | ' . $status['height']);
						} else if ((int) $currentBlockHeight > (int) $status['height']) {
							var_dump($remote . ' | x ==> Synchro NOT OK <== x ! | ' . $status['height']);
						} else {
							var_dump($remote . ' | x ERROR x | ');
						}
					}

					$this->alreadyChecked[] = $remote;
				}
			}

			var_dump('Waiting for next block [' . $this->iteration++ . '] => Top Block => ' . $this->blockchain->getTopHeight());
			sleep(10);
		}
	}
}
