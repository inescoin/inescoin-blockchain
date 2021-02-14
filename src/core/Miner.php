<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

use Inescoin\Block;

use Inescoin\LoggerService;
use Inescoin\BlockchainConfig;
use Inescoin\MerkleTree;

use DateTimeImmutable;

final class Miner
{
    private $locked = false;

    private $blockchain;

    private $hashDifficulty;

    private $minerRewardAmount = 5000;

    private $pool = [];

    public function __construct(Blockchain $blockchain, $hashDifficulty)
    {
        $this->blockchain = $blockchain;
        $this->hashDifficulty = $hashDifficulty;
        $this->logger = (LoggerService::getInstance())->getLogger();
    }

    public function getBlockTemplate($data) {
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

        $lastBlock = $this->getBlockchain()->es->blockService()->getLastBlock();

        if (!$lastBlock) {
            $lastBlock = Block::generateGenesisBlock($this->getBlockchain()->getPrefix());
        }

        $height = $lastBlock->getHeight();
        $previousHash = $lastBlock->getHash();
        $previousBlockTxCount = $lastBlock->getCountTransaction();
        $previousBlockCreatedAt = $lastBlock->getCreatedAt();
        $previousCumulativeDifficulty = $lastBlock->getCumulativeDifficulty();




        $minerReward = $this->getBlockchain()->es->transactionService()->getMinerRewardAmount();
        $minerReward = $minerReward ? $minerReward : Block::MINER_REWARD;

        $difficulty = $this->getBlockchain()->getNextDifficulty();
        if ($difficulty < BlockchainConfig::MIN_DIFFICULTY) {
            $difficulty = BlockchainConfig::MIN_DIFFICULTY;
        }

        $minerTransactionReward = (new Transaction(null, $this->getBlockchain()->getPrefix()))->generateCoinbaseTansaction($data['walletAddress'], $minerReward);

        $dataPool = $this->getBlockchain()->getDataPool();
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

        $output = [
            'id' => $data['walletAddress'],
            'data' => $dataEncoded,
            'nonce' => $nonce,
            'configHash' => $this->getBlockchain()->getConfigHash(),
            'difficulty' => $difficulty,
            'height' => $height + 1,
            'createdAt' => (new DateTimeImmutable())->getTimestamp(),
            'previousHash' => $previousHash,
            'countTransaction' => $countTransaction,
            'previousBlockTxCount' => $previousBlockTxCount,
            'previousBlockCreatedAt' => $previousBlockCreatedAt,
            'previousCumulativeDifficulty' => $previousCumulativeDifficulty,
            'merkleRoot' => MerkleTree::getRoot($dataPool)
        ];

        $this->pool[$data['walletAddress']] = $output;

        return $output;
    }

    public function submitBlockHash($data) {
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

        $block = Block::toBlock($this->pool[$data['walletAddress']]);
        $block->setMerkelRoot($this->pool[$data['walletAddress']]['merkleRoot']);

        $lastBlock = $this->getBlockchain()->getLastBlock();
        if (!$lastBlock || $lastBlock->isNextValid($block)) {
            $this->logger->info("[Miner] Block submitted ok, start blockchain push...");
            if ($this->getBlockchain()->add($block)) {
                $this->logger->info("[Miner] Done OK!");
                return [
                    'done' => 'ok'
                ];
            }
        }

        sleep(30);

        $this->logger->info("[Miner] Rejected block!");
        return [
            'error' => 'Rejected block'
        ];
    }

    public function push($data)
    {
        return $this->blockchain->push($data);
    }

    public function pushMessagePool($data)
    {
        return $this->blockchain->pushMessage($data);
    }

    public function getBlockchain(): Blockchain
    {
        return $this->blockchain;
    }
}
