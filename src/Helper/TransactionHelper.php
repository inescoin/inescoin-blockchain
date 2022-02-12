<?php

// Copyright 2019-2022 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\Helper;

use Inescoin\BlockchainConfig;
use Inescoin\Helper\Pow;
use Inescoin\Manager\BlockchainManager;
use Inescoin\Model\Block;
use Inescoin\Model\Transaction;

class TransactionHelper
{

	public static function generateGenesisTansaction(string $address, string|int $minerReward, string $date = 'now', ?string $prefix = null): Transaction
	{
		return self::generateCoinbaseTansaction($address, $minerReward, $date, $prefix);
	}

	public static function generateCoinbaseTansaction(string $address, string $minerReward, string $date = 'now', ?string $prefix = null): Transaction
	{
		$blockchainManager = BlockchainManager::getInstance($prefix);

		$walletBank = $blockchainManager
			->getBank()
			->getAddressBalances(BlockchainConfig::NAME, true);

		$bankHash = !empty($walletBank)
			? $walletBank[BlockchainConfig::NAME]['hash']
			: Pow::hash('Hello Moon');

		$transaction = (new Transaction(null, $prefix))->setData([
			'fee' => 0,
			'fromWalletId' => BlockchainConfig::NAME,
			'createdAt' => (new \DateTimeImmutable($date))->getTimestamp(),
			'coinbase' => true,
			'bankHash' => $bankHash,
			'publicKey' => '',
			'signature' => '',
		]);

		$transaction->addTransfers([[
			'toWalletId' => $address,
			'amount' => $minerReward,
		]]);

		return $transaction;
	}

	public static function isValidTransactions(Block $block, ?string $prefix = null): bool
    {
        $blockInfos = $block->getJsonInfos();

        $transactions = $blockInfos['data'];

        if ($blockInfos['height'] === 0) {
            return true;
        }

        if (!is_array($transactions) || empty($transactions)) {
            $this->logger->info('[Blockchain][isValidTransactions] Empty transactions');
            return false;
        }

        foreach ($transactions as $transation) {
            $transactionModel = (new Transaction(null, $prefix))->setData($transation);

            if (!$transactionModel->isValid(true)) {
                return false;
            }
        }

        return true;
    }
}
