<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

use Inescoin\Block;

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
        $minerReward = $this->getBlockchain()->es->transactionService()->getMinerRewardAmount();
        $minerReward = $minerReward ? $minerReward : Block::MINER_REWARD;

        $difficulty = $this->getBlockchain()->getNextDifficulty();
        if ($difficulty < BlockchainConfig::MIN_DIFFICULTY) {
            $difficulty = BlockchainConfig::MIN_DIFFICULTY;
        }

        $height = $lastBlock->getHeight();
        $previousHash = $lastBlock->getHash();
        $previousCumulativeDifficulty = $lastBlock->getCumulativeDifficulty();

        var_dump('[Miner] [previousCumulativeDifficulty] ----------------------> ' . $previousCumulativeDifficulty);

        $minerTransactionReward = (new Transaction(null, $this->getBlockchain()->getPrefix()))->generateCoinbaseTansaction($data['walletAddress'], $minerReward);

        $dataPool = $this->getBlockchain()->getDataPool();

        $dataPool[] = $minerTransactionReward->getInfos();

        $dataEncoded = Block::getDataEncoded($dataPool);

        $output = [
            'id' => $data['walletAddress'],
            'data' => $dataEncoded,
            'nonce' => $nonce,
            'configHash' => $this->getBlockchain()->getConfigHash(),
            'difficulty' => $difficulty,
            'height' => $height + 1,
            'createdAt' => (new DateTimeImmutable())->getTimestamp(),
            'previousHash' => $previousHash,
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

        var_dump("Nonce detected ---> " . $this->pool[$data['walletAddress']]['nonce']);

        $block = Block::toBlock($this->pool[$data['walletAddress']]);
        $block->setMerkelRoot($this->pool[$data['walletAddress']]['merkleRoot']);

        $lastBlock = $this->getBlockchain()->getLastBlock();
        if (!$lastBlock || $lastBlock->isNextValid($block)) {
            var_dump("Block submitted ok, start blockchain push...");
            if ($this->getBlockchain()->add($block)) {
                var_dump([
                    'done' => 'ok'
                ]);
                return [
                    'done' => 'ok'
                ];
            }
        }

        sleep(5);
        var_dump([
            'error' => 'Rejected block'
        ]);
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
