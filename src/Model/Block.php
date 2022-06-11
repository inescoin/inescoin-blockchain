<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\Model;

use DateTimeImmutable;
use Inescoin\BlockchainConfig;
use Inescoin\Helper\BlockHelper;
use Inescoin\Helper\MerkleTree;
use Inescoin\Helper\Pow;
use Inescoin\Helper\ZeroPrefix;
use Inescoin\Service\LoggerService;
use JsonSerializable;

class Block
{
    private $configHash = '';

    private $difficulty = 1;

    private $previousCumulativeDifficulty = 0;

    private $cumulativeDifficulty = 1;

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

    public function __construct(array $block)
    {
        $this->logger = (LoggerService::getInstance())->getLogger();
        $this->configHash = BlockchainConfig::getHash();
        $this->hashDifficulty = new ZeroPrefix();

        $this->set($block);
    }

    public static function getDataEncoded($data)
    {
        return is_array($data)
            ? BlockHelper::transpile(base64_encode(json_encode($data)))
            : $data;
    }

    public static function getDataDecoded($data)
    {
        return is_array($data)
            ? $data
            : json_decode(base64_decode(BlockHelper::transpile($data, false)), true);
    }

    public function set($data)
    {
        if (!is_array($data)) {
            return $this;
        }

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $key === 'data'
                    ? self::getDataEncoded($value)
                    : $value;
            }
        }

        return $this;
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

    public function setDataJson($data)
    {
        $this->data =  json_decode(base64_decode($data));

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

        $data = is_array($this->data)
            ? $this->data
            : self::getDataDecoded($this->data);

        if (is_array($data)) {
            foreach ($data as $transaction) {
                $transaction = (array) $transaction;
                $transaction['transfers'] = is_array($transaction['transfers'])
                    ? $transaction['transfers']
                    : json_decode(base64_decode($transaction['transfers']), true);

                foreach ($transaction['transfers'] as $pos => $transfer) {
                    $transaction['transfers'][$pos] = (array) $transfer;
                }

                $transaction['toDo'] = is_array($transaction['toDo'])
                    ? $transaction['toDo']
                    : @json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', rawurldecode(base64_decode($transaction['toDo']))), true);

                if (null !== $transaction['toDo']) {
                    foreach ($transaction['toDo'] as $pos => $toDo) {
                        $transaction['toDo'][$pos] = (array) $toDo;
                    }
                }

                $transactions[] = $transaction;
            }
        }

        return $transactions;
    }

    public function getData()
    {
        return $this->data;
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

    // public static function calculateHash(int $height, string $previousHash, int $createdAt, string $data, int $difficulty, string $nonce, string $merkleRoot): string
    // {
    //     $configHash = BlockchainConfig::getHash();

    //     return Pow::hash($merkleRoot.$configHash.$height.$previousHash.$createdAt.$difficulty.$nonce);
    // }

    // public static function calculateHashFromArray(array $block): string
    // {
    //     return self::calculateHash(
    //         $block['height'],
    //         $block['previousHash'],
    //         $block['createdAt'],
    //         (string) $block['data'],
    //         (int) $block['difficulty'],
    //         (string) $block['nonce'],
    //         (string) $block['merkleRoot']
    //     );
    // }

    // public static function generateGenesisBlock($prefix = BlockchainConfig::NAME, $showGenensisAndExit = false): self
    // {
    //     $genesisTransaction = (new Transaction(null, $prefix))->generateGenesisTansaction(
    //         BlockchainConfig::GENESIS_MINER_ADDRESS,
    //         BlockchainConfig::GENESIS_MINER_REWARD,
    //         BlockchainConfig::GENESIS_DATE
    //     );

    //     $transactions = [
    //         $genesisTransaction->getInfos()
    //     ];

    //     $merkelRoot = MerkleTree::getRoot($transactions);

    //     $genesisBlock = new self([
    //         'height' => 0,
    //         'hash' => BlockchainConfig::GENESIS_BLOCK_HASH,
    //         'previousHash' => '86A10E30758FF2D10057256F10CF7DFA5B0E2703DC35FE9C3E49942233BD943B',
    //         'createdAt' => (new DateTimeImmutable(BlockchainConfig::GENESIS_DATE))->getTimestamp(),
    //         'data' => $transactions,
    //         'difficulty' => 1,
    //         'nonce' => 0,
    //         'countTransaction' => 1,
    //         'countTotalTransaction' => 1,
    //     ]);

    //     $genesisBlock->setMerkelRoot($merkelRoot);
    //     $genesisBlockHash = Block::calculateHashFromArray($genesisBlock->getInfos());

    //     if ($showGenensisAndExit) {
    //         echo PHP_EOL . 'Genesis hash: ' . $genesisBlockHash . PHP_EOL;
    //         echo 'Config hash: ' . BlockchainConfig::getHash() . PHP_EOL;
    //         echo 'Config: '. PHP_EOL;
    //         foreach (BlockchainConfig::CONFIG as $key => $value) {
    //             echo '----|  ' . $key . ' => ' . $value . PHP_EOL;
    //         }
    //         exit();
    //     }

    //     return $genesisBlock;
    // }

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
            var_dump('[Block] [isNextValid] !!! ERROR !!! Invalid Genesis Hash ' . BlockchainConfig::GENESIS_BLOCK_HASH . ' <-> ' . $this->getHash());
            $this->logger->error('[Block] [isNextValid] !!! ERROR !!! Invalid Genesis Hash ' . BlockchainConfig::GENESIS_BLOCK_HASH . ' <-> ' . $this->getHash());

            return false;
        }

        // Check height incrementation
        if ($block->getHeight() !== $this->height + 1) {
            $this->logger->error('[Block] [isNextValid] !!! ERROR !!! Block->getHeight() ' . $block->getHeight() . " : {$this->height} ");
            var_dump('[Block] [isNextValid] !!! ERROR !!! Block->getHeight() ' . $block->getHeight() . " : {$this->height} ");

            return false;
        }

        // Check previous hash block
        if ($block->getPreviousHash() !== $this->hash) {
            $this->logger->error('[Block] [isNextValid] !!! ERROR !!! Block->getPreviousHash() ' . $this->getHeight() . " <->  {$block->getHeight()} | {$this->hash} <-> {$block->getPreviousHash()}");

            return false;
        }

        // Check timestamp
        if ($block->getCreatedAt() < $this->createdAt + BlockchainConfig::NEXT_TIMESTAMP) {
            $this->logger->error('[Block] [isNextValid] [..] !!! ERROR !!! Block->getCreatedAt() ' . $this->getHeight() . " <->  {$block->getHeight()} | {$this->createdAt} <-> {$block->getCreatedAt()}");

            return false;
        }

        // Check empty block timestamp
        if ($block->getCountTransaction() === 1 && $block->getCreatedAt() < $this->createdAt + BlockchainConfig::NEXT_EMPTY_TIMESTAMP) {
            $this->logger->error('[Block] [isNextValid] !!! ERROR !!! Block->getCreatedAt() ' . $this->getHeight() . " <->  {$block->getHeight()} | {$this->createdAt} <-> {$block->getCreatedAt()}");

            return false;
        }

        if ($block->getCountTransaction() === 0) {
            $this->logger->error('[Block] [isNextValid] [.00] !!! ERROR !!! Not transaction into block');

            return false;
        }

        // Check configHash Network
        if (!in_array(BlockchainConfig::getHash(), [$this->getConfigHash(), $block->getConfigHash()])) {
            var_dump('[Block] [isNextValid] !!! ERROR !!! Block->getConfigHash() ' . $this->getHeight() . " <->  {$block->getHeight()} | " . BlockchainConfig::getHash() . " <-> {$this->configHash} <-> {$block->getConfigHash()}");
            $this->logger->error('[Block] [isNextValid] !!! ERROR !!! Block->getConfigHash() ' . $this->getHeight() . " <->  {$block->getHeight()} | {$this->configHash} <-> {$block->getConfigHash()}");

            return false;
        }

        // Check current hash block
        $hash = BlockHelper::calculateHash(
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

    public function getJsonInfos()
    {
        $infos = $this->getInfos();
        $infos['data'] = $this->getDataJson();

        return $infos;
    }
}
