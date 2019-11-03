<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

use Inescoin\BlockchainConfig;
use Inescoin\ZeroPrefix;
use Inescoin\MerkleTree;
use Inescoin\LoggerService;

use DateTimeImmutable;
use JsonSerializable;

class Block
{
    private $configHash = '';

    private $difficulty;

    private $previousCumulativeDifficulty;

    private $cumulativeDifficulty;

    private $nonce = '0';

    private $height;

    private $previousHash;

    private $data = '';

    private $transactions = '';

    private $metadata = '';

    private $hash;

    private $createdAt;

    private $countTotalTransaction = 0;

    private $countTransaction = 0;

    private $merkleRoot = '';

    private $logger;

    public function __construct(
        int $height,
        string $hash,
        string $previousHash,
        int $createdAt,
        $data,
        int $difficulty,
        int $nonce,
        int $previousCumulativeDifficulty,
        int $totalTransaction = 0)
    {
        $this->logger = (LoggerService::getInstance())->getLogger();

        $this->height = $height;

        $this->previousHash = $previousHash;

        $this->createdAt = $createdAt;

        $this->hash = $hash;

        $countData = 0;
        if (is_array($data)) {
            $countData = count($data);
        }

        $this->tempData = $data;

        $this->countTransaction = $countData;

        $this->countTotalTransaction = $totalTransaction + $this->countTransaction;

        $this->data = self::getDataEncoded($data);

        $this->difficulty = $difficulty;

        $this->cumulativeDifficulty = $previousCumulativeDifficulty + (2 ** $difficulty);

        $this->hashDifficulty = new ZeroPrefix();

        $this->configHash = BlockchainConfig::getHash();

        $this->nonce = $nonce;
    }

    public static function getDataEncoded($data)
    {
        if (is_array($data)) {
            return base64_encode(json_encode($data));
        } else {
            return $data;
        }
    }

    public static function getDataDecoded($data)
    {
        if (!is_array($data)) {
            return json_decode(base64_decode($data));
        } else {
            return $data;
        }
    }

    public function set($data)
    {
        if (isset($data['difficulty'])) {
            $this->difficulty = $data['difficulty'];
        }

        if (isset($data['cumulativeDifficulty'])) {
            $this->cumulativeDifficulty = $data['cumulativeDifficulty'];
        }

        if (isset($data['nonce'])) {
            $this->nonce = $data['nonce'];
        }

        if (isset($data['height'])) {
            $this->height = $data['height'];
        }

        if (isset($data['previousHash'])) {
            $this->previousHash = $data['previousHash'];
        }

        if (isset($data['configHash'])) {
            $this->configHash = $data['configHash'];
        }

        if (isset($data['merkleRoot'])) {
            $this->merkleRoot = $data['merkleRoot'];
        }

        if (isset($data['data'])) {
            if (is_array($data['data'])) {
                $this->data = base64_encode(json_encode($data['data']));
            } else {
                $this->data = $data['data'];
            }
        }

        if (isset($data['hash'])) {
            $this->hash = $data['hash'];
        }

        if (isset($data['countTotalTransaction'])) {
            $this->countTotalTransaction = $data['countTotalTransaction'];
        }

        if (isset($data['countTransaction'])) {
            $this->countTransaction = $data['countTransaction'];
        }
    }

    public function getPreviousHash()
    {
        return $this->previousHash;
    }

    public function setPreviousHash($hash)
    {
        $this->previousHash = $hash;
        return $this;
    }

    public function getConfigHash()
    {
        return $this->configHash;
    }

    public function setConfigHash($configHash)
    {
        $this->configHash = $configHash;
        return $this;
    }

    public function getMerkleRoot()
    {
        return $this->merkleRoot;
    }

    public function setMerkelRoot($merkleRoot)
    {
        $this->merkleRoot = $merkleRoot;
        return $this;
    }

    public function setDataJson()
    {
        $this->data =  json_decode(base64_decode($this->data));
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getDataJson()
    {
        $transactions = [];
        $data = json_decode(base64_decode($this->data, true));
        foreach ($data as $transaction) {
            $transaction = (array) $transaction;
            $transaction['transfers'] = json_decode(base64_decode($transaction['transfers']), true);

            $transactions[] = $transaction;
        }

        return $transactions;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getTempData()
    {
        $data = [];
        if (is_array($this->tempData)) {
            foreach ($this->tempData as $block) {
                $data[] = $block;
            }
        }

        return $data;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getDifficulty()
    {
        return $this->difficulty;
    }

    public function getPreviousCumulativeDifficulty()
    {
        return $this->previousCumulativeDifficulty;
    }

    public function getCumulativeDifficulty()
    {
        return $this->cumulativeDifficulty;
    }

    public function setDifficulty($newDiff)
    {
        return $this->difficulty = $newDiff;
    }

    public function getNonce()
    {
        return $this->nonce;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function incrementNonce()
    {
        $this->nonce++;
    }

    public function getCountTransaction()
    {
        return $this->countTransaction;
    }

    public function getCountTotalTransaction()
    {
        return $this->countTotalTransaction;
    }

    public static function calculateHash(int $height, string $previousHash, int $createdAt, string $data, int $difficulty, string $nonce, string $merkleRoot): string
    {
        $configHash = BlockchainConfig::getHash();

        return Pow::hash($merkleRoot.$configHash.$height.$previousHash.$createdAt.$difficulty.$nonce);
    }

    public static function calculateHashFromArray(array $block): string
    {
        return self::calculateHash(
            $block['height'],
            $block['previousHash'],
            $block['createdAt'],
            (string) $block['data'],
            (int) $block['difficulty'],
            (string) $block['nonce'],
            (string) $block['merkleRoot']
        );
    }

    public static function generateGenesisBlock($prefix = ''): self
    {
        $genesisTransaction = (new Transaction(null, $prefix))->generateGenesisTansaction(
            BlockchainConfig::GENESIS_MINER_ADDRESS,
            BlockchainConfig::GENESIS_MINER_REWARD,
            BlockchainConfig::GENESIS_DATE
        );

        $transactions = [$genesisTransaction->getInfos()];
        $merkelRoot = MerkleTree::getRoot($transactions);

        $genesisBlock = new self(
            0,                                                                          // Height
            BlockchainConfig::GENESIS_BLOCK_HASH,                                       // Hash
            '86A10E30758FF2D10057256F10CF7DFA5B0E2703DC35FE9C3E49942233BD943B',         // $previousHash
            (new DateTimeImmutable(BlockchainConfig::GENESIS_DATE))->getTimestamp(),    // $createdAt
            $transactions,                                                              // $data
            1,                                                                          // $difficulty
            0,                                                                          // $nonce
            0,                                                                          // $previousCumulativeDifficulty
            0                                                                           // $totalTransaction
        );

        $genesisBlock->setMerkelRoot($merkelRoot);
        $genesisBlockHash = Block::calculateHashFromArray($genesisBlock->getInfos());
        // var_dump($genesisBlockHash); exit();
        return $genesisBlock;
    }

    public function isEqual(self $block): bool
    {
        return $this->height === $block->getHeight()
            && $this->hash === $block->getHash()
            && $this->configHash === $block->getConfigHash()
            && $this->previousCumulativeDifficulty === $block->getConfigHash()
            && $this->previousHash === $block->getPreviousHash()
            && $this->createdAt === $block->getCreatedAt()
            && $this->previousCumulativeDifficulty === $block->getPreviousCumulativeDifficulty()
            && $this->data === $block->getData()
            && $this->difficulty === $block->getDifficulty()
            && $this->nonce === $block->getNonce()
            && $this->merkleRoot === $block->getMerkleRoot()
            && $this->name === $block->getName()
            && $this->symbol === $block->getSymbol()
        ;
    }

    public function isNextValid(self $block): bool
    {
        if ($this->height === 0 && BlockchainConfig::GENESIS_BLOCK_HASH !== $this->getHash()) {
            $this->logger->error('[Block] [isNextValid] !!! ERROR !!! Invalid Genesis Hash ');
            return false;
        }

        // Check height incrementation
        if ($block->getHeight() !== $this->height + 1) {
            $this->logger->error('[Block] [isNextValid] !!! ERROR !!! Block->getHeight() ' . $block->getHeight());

            return false;
        }

        // Check previous hash block
        if ($block->getPreviousHash() !== $this->hash) {
            $this->logger->error('[Block] [isNextValid] !!! ERROR !!! Block->getPreviousHash() ' . $this->getHeight() . " <->  {$block->getHeight()} | {$this->hash} <-> {$block->getPreviousHash()}");
            return false;
        }

        // Check timestamp
        if ($block->getCreatedAt() < $this->createdAt + BlockchainConfig::NEXT_TIMESTAMP) {
            $this->logger->error('[Block] [isNextValid] !!! ERROR !!! Block->getCreatedAt() ' . $this->getHeight() . " <->  {$block->getHeight()} | {$this->createdAt} <-> {$block->getCreatedAt()}");
            return false;
        }

        // Check configHash Network
        if ($block->getConfigHash() !== BlockchainConfig::getHash()) {
            $this->logger->error('[Block] [isNextValid] !!! ERROR !!! Block->getConfigHash() ' . $this->getHeight() . " <->  {$block->getHeight()} | {$this->configHash} <-> {$block->getConfigHash()}");
            return false;
        }

        // Check current hash block
        $hash = self::calculateHash(
            $block->getHeight(),
            $block->getPreviousHash(),
            $block->getCreatedAt(),
            $block->getData(),
            $block->getDifficulty(),
            $block->getNonce(),
            $block->getMerkleRoot());

        if ($block->getHash() !== $hash) {
            $this->logger->error('[Block] [isNextValid] !!! ERROR !!! Block->calculateHash() ' . $hash . " | " . $block->getHash());
            return false;
        }

        return true;
    }



    public function getInfos(): array
    {
        return [
            'difficulty' => $this->difficulty,
            'nonce' => $this->nonce,
            'height' => $this->height,
            'cumulativeDifficulty' => $this->cumulativeDifficulty,
            'previousHash' => $this->previousHash,
            'configHash' => $this->configHash,
            'data' => $this->getData(),
            'hash' => $this->hash,
            'createdAt' => $this->createdAt,
            'merkleRoot' => $this->merkleRoot,
            'countTotalTransaction' => $this->countTotalTransaction,
            'countTransaction' => $this->countTransaction,
        ];



        return $data;
    }

    public function isValid() {
        return $this->coinbase || ($this->imNotEmpty() && PKI::ecVerify(bin2hex($this->getHash()), $this->signature, $this->publicKey));
    }

    public function getJsonInfos()
    {
        return [
            'difficulty' => $this->difficulty,
            'nonce' => $this->nonce,
            'height' => $this->height,
            'cumulativeDifficulty' => $this->cumulativeDifficulty,
            'previousHash' => $this->previousHash,
            'configHash' => $this->configHash,
            'data' => $this->getDataJson(),
            'hash' => $this->hash,
            'createdAt' => $this->createdAt,
            'merkleRoot' => $this->merkleRoot,
            'countTotalTransaction' => $this->countTotalTransaction,
            'countTransaction' => $this->countTransaction,
        ];
    }

    public static function toBlock(array $block)
    {
        if (!$block) {
            return null;
        }

        $height = (int) $block['height'];
        $hash = (string) $block['hash'];
        $merkleRoot = (string) $block['merkleRoot'];
        $previousHash = (string) $block['previousHash'];
        $createdAt = $block['createdAt'];
        $dataBase64 = (string) $block['data'];
        $difficulty = (int) $block['difficulty'];
        $nonce = (int) $block['nonce'];
        $totalTransaction = isset($block['countTransaction']) ? (int) $block['countTransaction'] : 0;
        $cumulativeDifficulty = isset($block['cumulativeDifficulty']) ? (int) $block['cumulativeDifficulty'] : 1;

        $previousCumulativeDifficulty = $cumulativeDifficulty - ( 2 ** $difficulty);

        if (isset($block['previousCumulativeDifficulty'])) {
            $previousCumulativeDifficulty = $block['previousCumulativeDifficulty'];
        }

        $selfBlock = new self(
            $height,
            $hash,
            $previousHash,
            $createdAt,
            $dataBase64,
            $difficulty,
            $nonce,
            $previousCumulativeDifficulty,
            $totalTransaction
        );

        $selfBlock->setMerkelRoot($merkleRoot);

        return $selfBlock;
    }

    public function compress()
    {
        $blockInfos = $this->getInfos();

        $compressed[0] = $blockInfos['difficulty'];
        $compressed[1] = $blockInfos['nonce'];
        $compressed[2] = $blockInfos['height'];
        $compressed[3] = $blockInfos['cumulativeDifficulty'];
        $compressed[4] = $blockInfos['previousHash'];
        $compressed[5] = $blockInfos['configHash'];
        $compressed[6] = $blockInfos['data'];
        $compressed[7] = $blockInfos['hash'];
        $compressed[8] = $blockInfos['createdAt'];
        $compressed[9] = $blockInfos['merkleRoot'];
        $compressed[10] = $blockInfos['countTotalTransaction'];
        $compressed[11] = $blockInfos['countTransaction'];

        return $compressed;
    }

    public static function decompress($compressed)
    {

        if (!is_array($compressed) || count($compressed) < 12) {
            $this->logger->error('!!!! ----> !!!! Error into decompression');
            return null;
        }

        for($i = 0; $i < 12; $i++) {
            if(!array_key_exists($i, $compressed)) {
                $this->logger->error('!!!! ----> !!!! Error into decompression position ' . $i);
                return null;
            }
        }

        $blockInfos['difficulty'] = $compressed[0];
        $blockInfos['nonce'] = $compressed[1];
        $blockInfos['height'] = $compressed[2];
        $blockInfos['cumulativeDifficulty'] = $compressed[3];
        $blockInfos['previousHash'] = $compressed[4];
        $blockInfos['configHash'] = $compressed[5];
        $blockInfos['data'] = $compressed[6];
        $blockInfos['hash'] = $compressed[7];
        $blockInfos['createdAt'] = $compressed[8];
        $blockInfos['merkleRoot'] = $compressed[9];
        $blockInfos['countTotalTransaction'] = $compressed[10];
        $blockInfos['countTransaction'] = $compressed[11];

        return $blockInfos;
    }
}
