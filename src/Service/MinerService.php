<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\Service;

use DateTimeImmutable;
use Inescoin\BlockchainConfig;
use Inescoin\Helper\BlockHelper;
use Inescoin\Helper\MerkleTree;
use Inescoin\Helper\TransactionHelper;
use Inescoin\Model\Block;
use Inescoin\Model\Transaction;
use Inescoin\Service\BlockchainService;
use Inescoin\Service\LoggerService;

final class MinerService
{
    private $locked = false;

    /**
     * @var BlockchainService
     */
    private $blockchainService;

    private $hashDifficulty;

    private $minerRewardAmount = 5000;

    private $pool = [];

    public function __construct(BlockchainService $blockchainService, $hashDifficulty)
    {
        $this->blockchainService = $blockchainService;
        $this->hashDifficulty = $hashDifficulty;
        $this->logger = (LoggerService::getInstance())->getLogger();
    }

    public function getBlockTemplate($data)
    {
        if (!is_array($data)) {
            return [
                'error' => 'malformed query'
            ];
        }

        if (!isset($data['walletAddress'])) {
            return [
                'error' => 'wallet address key not found'
            ];
        }

        $nonce = 0;

        $lastBlock = $this->getBlockchainService()->getBlockchainManager()->getBlock()->last();

        if (!$lastBlock) {
            $lastBlock = BlockHelper::generateGenesisBlock($this->getBlockchainService()->getPrefix());
        }

        $height = $lastBlock->getHeight();
        $previousHash = $lastBlock->getHash();
        $previousBlockTxCount = $lastBlock->getCountTransaction();
        $previousBlockTotalTxCount = $lastBlock->getCountTotalTransaction();
        $previousBlockCreatedAt = $lastBlock->getCreatedAt();
        $previousCumulativeDifficulty = $lastBlock->getCumulativeDifficulty();

        $minerReward = BlockchainConfig::FIXED_MINER_REWARD;

        $difficulty = $this->getBlockchainService()->getNextDifficulty();
        if ($difficulty < BlockchainConfig::MIN_DIFFICULTY) {
            $difficulty = BlockchainConfig::MIN_DIFFICULTY;
        }

        $minerTransactionReward = TransactionHelper::generateCoinbaseTansaction(
            $data['walletAddress'],
            $minerReward,
            'now',
            $this->getBlockchainService()->getPrefix()
        );

        $dataPool = $this->getBlockchainService()->getDataPool();
        $dataPool[] = $minerTransactionReward->getInfos();

        $countTransaction = count($dataPool);

        $dataEncoded = Block::getDataEncoded($dataPool);

        // Check empty block timestamp
        $timeLeft = time() - ($previousBlockCreatedAt + BlockchainConfig::NEXT_EMPTY_TIMESTAMP);
        if ($countTransaction === 1 && $timeLeft < 0) {
            $output['error'] = 'Time left for next empty block: ' . $timeLeft;
            $output['timeLeft'] = $timeLeft * -1;
            return $output;
        }

        $height++;

        $output = [
            'id' => $data['walletAddress'],
            'data' => $dataEncoded,
            'nonce' => $nonce,
            'configHash' => BlockchainConfig::getHash(),
            'difficulty' => $difficulty,
            'height' => $height,
            'createdAt' => (new DateTimeImmutable())->getTimestamp(),
            'previousHash' => $previousHash,
            'countTransaction' => $countTransaction,
            'previousBlockTxCount' => $previousBlockTxCount,
            'previousBlockTotalTxCount' => $previousBlockTotalTxCount,
            'previousBlockCreatedAt' => $previousBlockCreatedAt,
            'previousCumulativeDifficulty' => $previousCumulativeDifficulty,
            'cumulativeDifficulty' => $previousCumulativeDifficulty + 2 ** $difficulty,
            'merkleRoot' => MerkleTree::getRoot($dataPool)
        ];

        // $this->logger->info("[Miner] getBlockTemplate for " . $data['walletAddress'] . ' at height ' . $height . ' with difficulty ' . $difficulty);
        var_dump("[Miner] getBlockTemplate for " . $data['walletAddress'] . ' at height ' . $height . ' with difficulty ' . $difficulty);

        $this->pool[$data['walletAddress']] = $output;

        return $output;
    }

    public function submitBlockHash($data)
    {
        if (!is_array($data)) {
            return [
                'error' => 'malformed query'
            ];
        }

        if (!isset($data['hash'])) {
            return [
                'error' => 'hash key not found'
            ];
        }

        if (!isset($data['nonce'])) {
            return [
                'error' => 'nonce key not found'
            ];
        }

        if (!isset($data['walletAddress'])) {
            return [
                'error' => 'walletAddress key not found'
            ];
        }

        if (!isset($this->pool[$data['walletAddress']])) {
            return [
                'error' => 'Wrong wallet address, use /getBlockTemplate before'
            ];
        }

        // $this->pool[$data['walletAddress']]['hash'] = Block::calculateHashFromArray($this->pool[$data['walletAddress']]);
        $this->pool[$data['walletAddress']]['hash'] = $data['hash'];
        $this->pool[$data['walletAddress']]['nonce'] = $data['nonce'];

        $this->logger->info("[Miner] Nonce detected ---> " . $this->pool[$data['walletAddress']]['nonce']);

        $block = BlockHelper::fromArrayToBlockModel($this->pool[$data['walletAddress']]);
        $block->setMerkelRoot($this->pool[$data['walletAddress']]['merkleRoot']);

        $lastBlock = $this->getBlockchainService()->getBlockchainManager()->getBlock()->last();

        if (empty($lastBlock)) {
            return [
                'error' => 'Rejected empty last block'
            ];
        }

        $lastBlock = new Block($lastBlock->getDataAsArray());
        if (!$lastBlock || $lastBlock->isNextValid($block)) {
            var_dump("[Miner] Block submitted ok, start blockchainService push...");
            $this->logger->info("[Miner] Block submitted ok, start blockchainService push...");
            if ($this->getBlockchainService()->addBlock($block, $lastBlock)) {
                $this->logger->info("[Miner] Done OK!");
                return [
                    'done' => 'ok'
                ];
            }
        }

        sleep(2);

        $this->logger->info("[Miner] Rejected block!");
        return [
            'error' => 'Rejected block'
        ];
    }

    public function push($data)
    {
        return $this->blockchainService->push($data);
    }

    public function pushMessagePool($data)
    {
        return $this->blockchainService->pushMessage($data);
    }

    public function getBlockchainService(): BlockchainService
    {
        return $this->blockchainService;
    }
}
