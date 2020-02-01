<?php

// Copyright 2019 - 2020 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

use Inescoin\ES\ESBlockchainProvider;
use Inescoin\BlockchainConfig;
use Inescoin\Block;

class BlockchainConsumer
{
	protected $esService;

	public function __construct($prefix = BlockchainConfig::NAME) {
		$this->esService = ESBlockchainProvider::getInstance($prefix);
	}

	public function run() {

		while (true) {
			$todos = $this->esService->todoService()->all();
			$topBlockHeight = $this->esService->blockService()->getTopHeight();
			$domaineToClean = $this->esService->domainService()->getByHeight($topBlockHeight);

			var_dump('Blockchain consumer service top height: ' . $topBlockHeight);
			var_dump('Blockchain consumer service clean domain: ' . count($domaineToClean));

			if (!isset($todos['hits']['hits'])) {
				var_dump('Not hits...');
				sleep(5);
			}

			if (isset($todos) && isset($todos['hits']) && isset($todos['hits']['hits'])) {
				foreach ($todos['hits']['hits'] as $todo) {
					$command = (array) json_decode($todo['_source']['command']);
					if (!isset($command['name'])) {
						var_dump($todo['_source']['hash'] . ' - $command[\'name\'] not found');
						continue;
					}

					if (!isset($command['action'])) {
						var_dump($todo['_source']['hash'] . ' - $command[\'action\'] not found');
						continue;
					}

					if (!isset($command['signature'])) {
						var_dump($todo['_source']['hash'] . ' - $command[\'signature\'] not found');
						continue;
					}

					if (!isset($todo['_source']['transactionHash'])) {
						var_dump($todo['_source']['hash'] . ' - $command[\'transactionHash\'] not found');
						continue;
					}

					if (!isset($todo['_source']['ownerAddress'])) {
						var_dump($todo['_source']['hash'] . ' - $command[\'ownerAddress\'] not found');
						continue;
					}

					if (!isset($todo['_source']['ownerPublicKey'])) {
						var_dump($todo['_source']['hash'] . ' - $command[\'ownerPublicKey\'] not found');
						continue;
					}

					if (!isset($todo['_source']['blockHeight'])) {
						var_dump($todo['_source']['hash'] . ' - $command[\'blockHeight\'] not found');
						continue;
					}

					$exec = [
						'hash' => $todo['_source']['hash'],
						'url' => strtolower($command['name']),
						'ownerAddress' => $todo['_source']['ownerAddress'],
						'ownerPublicKey' => $todo['_source']['ownerPublicKey'],
						'signature' => $command['signature'],
						'blockHeight' => $todo['_source']['blockHeight'],
						'transactionHash' => $todo['_source']['transactionHash'],
					];

					if (!isset($command['action'])) {
						var_dump('[ERROR] Actoin not found: ' . $todo['_source']['hash']);
						continue;
					}

					$errorAmount = false;
					switch ($command['action']) {
						case 'create':
							switch ($todo['_source']['amount']) {
								case BlockchainConfig::WEB_COST_ONE_MONTH:
									$exec['blockHeightEnd'] = $exec['blockHeight'] + 2;
									break;
								case BlockchainConfig::WEB_COST_THREE_MONTH:
									$exec['blockHeightEnd'] = $exec['blockHeight'] + (2 * 3);
									break;
								case BlockchainConfig::WEB_COST_SIX_MONTH:
									$exec['blockHeightEnd'] = $exec['blockHeight'] + (2 * 6);
									break;

								default:
									$errorAmount = true;
									break;
							}

							if ($errorAmount) {
								var_dump('[ERROR] [create] Bad action amount: ' . $todo['_source']['hash']);
								var_dump($todo['_source']['amount']);
								continue 2;
							}

							if (!$this->esService->domainService()->exists($command['name'])) {
								$this->esService->domainService()->index($command['name'], $exec);
							} else {
								var_dump('[ERROR] [create] already exists ' . $todo['_id'] . ' - ' . $command['name']);
							}

							var_dump('[SUCCESS] [create] ' . $todo['_id']);
							$this->esService->todoService()->delete($todo['_id']);
							break;

						case 'renew':
							switch ($todo['_source']['amount']) {
								case BlockchainConfig::WEB_COST_ONE_MONTH:
									$exec['blockHeightEnd'] = $exec['blockHeight'] + 2;
									break;
								case BlockchainConfig::WEB_COST_THREE_MONTH:
									$exec['blockHeightEnd'] = $exec['blockHeight'] + (2 * 3);
									break;
								case BlockchainConfig::WEB_COST_SIX_MONTH:
									$exec['blockHeightEnd'] = $exec['blockHeight'] + (2 * 6);
									break;

								default:
									$errorAmount = true;
									break;
							}

							if ($errorAmount) {
								var_dump('[ERROR] [create] Bad action amount: ' . $todo['_source']['hash']);
								var_dump($todo['_source']['amount']);
								continue 2;
							}

							if ($errorAmount) {
								var_dump('[ERROR] [create] Bad action amount: ' . $todo['_source']['hash']);
								continue 2;
							}

							if (!$this->esService->domainService()->exists($command['name'])) {
								var_dump('[ERROR] [renew] not found ' . $todo['_id'] . ' - ' . $command['name']);
								continue 2;
							}

							$website = $this->esService->domainService()->get($command['name']);
							if (isset($website['error'])) {
								var_dump('[ERROR] Domain not found: ' . $command['name']);
								$this->esService->todoService()->delete($todo['_id']);
								continue 2;
							}

							$websiteSource = $website['_source'];


							switch ($todo['_source']['amount']) {
								case BlockchainConfig::WEB_COST_ONE_MONTH:
									$exec['blockHeightEnd'] = $websiteSource['blockHeightEnd'] + 2;
									break;
								case BlockchainConfig::WEB_COST_THREE_MONTH:
									$exec['blockHeightEnd'] = $websiteSource['blockHeightEnd'] + (2 * 3);
									break;
								case BlockchainConfig::WEB_COST_SIX_MONTH:
									$exec['blockHeightEnd'] = $websiteSource['blockHeightEnd'] + (2 * 6);
									break;

								default:
									$errorAmount = true;
									break;
							}

							$upExec = [
								'blockHeightEnd' => $exec['blockHeightEnd'],
							];

							$updateDomain = $this->esService->domainService()->update($exec['url'], $upExec);
							if (isset($updateDomain['error'])) {
								var_dump('[ERROR] [$updateDomain] : ' . $todo['_source']['hash'] . ' | ' . $updateDomain['error']);
								continue 2;
							}

							var_dump('[SUCCESS] [renew] ' . $todo['_id']);
							$this->esService->todoService()->delete($todo['_id']);
							break;

						case 'update':
							if($todo['_source']['amount'] !== BlockchainConfig::WEB_COST_UPDATE) {
								var_dump('[ERROR] [update] Bad action amount: ' . $todo['_source']['hash'] . ' | Given: ' . $todo['_source']['amount'] . ' | Excepted: ' . BlockchainConfig::WEB_COST_UPDATE);
								continue 2;
							}

							$domain = $this->esService->domainService()->get($command['name']);

							if (isset($domain['error'])) {
								var_dump('[ERROR] Domain not found: ' . $command['name']);
								$this->esService->todoService()->delete($todo['_id']);
							} else {
								$domainSource = $domain['_source'];
								$exec = [
									'hash' => $domainSource['hash'],
									'url' => strtolower($domainSource['url']),
									'body' => base64_encode(json_encode($command['data'])),
									'ownerAddress' => $domainSource['ownerAddress'],
									'ownerPublicKey' => $domainSource['ownerPublicKey'],
									'signature' => $command['signature'],
									'blockHeight' => $todo['_source']['blockHeight'],
									'transactionHash' => $todo['_source']['transactionHash'],
								];

								if ($this->esService->websiteService()->exists($domainSource['url'])) {
									$this->esService->websiteService()->update($domainSource['url'], $exec);
								} else {
									$this->esService->websiteService()->index($domainSource['url'], $exec);
								}

								$this->_cleanBlock($todo['_source']);
								var_dump('[SUCCESS] [update] ' . $todo['_id']);
								$this->esService->todoService()->delete($todo['_id']);
							}
							break;

						case 'delete':
							if($todo['_source']['amount'] !== BlockchainConfig::WEB_COST_DELETE) {
								var_dump('[ERROR] [delete] Bad action amount: ' . $todo['_source']['hash']);
								continue 2;
							}

							$deleteDomain = $this->esService->domainService()->delete($command['name']);
							if (isset($deleteDomain['error'])) {
								var_dump('[ERROR] [$deleteDomain] : ' . $todo['_source']['hash'] . ' | ' . $deleteDomain['error']);
								continue 2;
							}

							$deleteWebsite = $this->esService->websiteService()->delete($command['name']);
							if (isset($deleteWebsite['error'])) {
								var_dump('[ERROR] [$deleteWebsite] : ' . $todo['_source']['hash'] . ' | ' . $deleteWebsite['error']);
								continue 2;
							}

							$this->_cleanBlock($todo['_source']);
							var_dump('[SUCCESS] [delete] ' . $todo['_id'], $exec);
							$this->esService->todoService()->delete($todo['_id']);
							break;
					}
				}
			}

			// Clean expired domains
			if (!empty($domaineToClean)) {
				foreach ($domaineToClean as $website) {
					var_dump('[BlockchainConsumer] [Clean] [$deleteDomain] : ' . $website['url']);

					$domain = $this->esService->domainService()->get($website['url']);
					if (!isset($domain['error'])) {
						// Clean data from creation block
						$blockSourceCreate = $this->esService->blockService()->get($domain['_source']['blockHeight']);
						$newBlockDataCreate = Block::cleanBlock(
							$blockSourceCreate['_source'],
							$domain['_source']['transactionHash']
						);

						$this->esService->blockService()->update($domain['_source']['blockHeight'], $newBlockDataCreate);

						// Clean data from last update block
						$blockSourceUpdate = $this->esService->blockService()->get($website['blockHeight']);
						$newBlockDataUpdate = Block::cleanBlock(
							$blockSourceUpdate['_source'],
							$website['transactionHash']
						);

						$this->esService->blockService()->update($website['blockHeight'], $newBlockDataUpdate);

						var_dump('[BlockchainConsumer] [Clean] [$deleteDomain] : ' . $website['url'] . ' | txHash: ' . $website['transactionHash']);
					} else {
						var_dump('[BlockchainConsumer] [Clean] [ERROR]' . $domain['error']);
					}

					$deleteDomain = $this->esService->domainService()->delete($website['url']);
					if (isset($deleteDomain['error'])) {
						var_dump('[BlockchainConsumer][ERROR] [Clean] [$deleteDomain] : ' . $website['url'] . ' | ' . $deleteDomain['error']);
					}

					$deleteWebsite = $this->esService->websiteService()->delete($website['url']);
					if (isset($deleteWebsite['error'])) {
						var_dump('[BlockchainConsumer][ERROR] [Clean]  [$deleteWebsite] : ' . $website['url'] . ' | ' . $deleteWebsite['error']);
					}
				}
			}

			sleep(10);
		}
	}

	private function _cleanBlock($websiteSource)
	{
		$blockSource = $this->esService->blockService()->get($websiteSource['blockHeight']);
		$newBlockData = Block::cleanBlock(
			$blockSource['_source'],
			$websiteSource['transactionHash']
		);

		$this->esService->blockService()->update($websiteSource['blockHeight'], $newBlockData);
	}
}
