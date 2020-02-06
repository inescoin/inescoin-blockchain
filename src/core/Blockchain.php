<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

use Inescoin\ES\ESBlockchainProvider;
use Inescoin\LoggerService;

use Inescoin\BlockchainConfig;

use \DateTimeImmutable;

class Blockchain {

    private $host = '127.0.0.1';

    private $port = 8081;

    private $difficulty = 1;

    private $topHeight = 0;

    private $topKnowHeight = 1;

    private $topCumulativeDifficulty = 1;

    private $prefix = '';

    private $chain = [];

    private $dataPool = [];

    private $dataMessagePool = [];

    private $blockDirectory;

    private $timeBetwenBlocks = 0;

    public $totalTransaction = 0;

    public $DB;

    public $es;

    public $banque = [];

    private $logger;

    private $config;
    private $configHash;

    private $messagePool = [];
    private $transactionPool = [];
    private $transferPool = [];

    private $lastTransactionPool = null;

    public function __construct($blockDirectory = './', $prefix = '', $hardScan = false, $resetMode = false, $showGenensisAndExit = false) {
        $this->configHash = BlockchainConfig::getHash();

        $this->prefix = $prefix;
        $this->blockDirectory = $blockDirectory;

        $this->logger = (LoggerService::getInstance())->getLogger();

        $this->es = ESBlockchainProvider::getInstance($prefix);

        if(!$this->resetBank($hardScan, $resetMode, $showGenensisAndExit)) {
            $this->logger->error("Init: Invalid blockchain, you need to resync from 0, please delete your blockchain folder: " . $blockDirectory);
            die();
        }
    }

    public function getConfigHash()
    {
        return $this->configHash;
    }

    public function getLogger() {
        return $this->logger;
    }

    public function getHost() {
        return $this->host;
    }

    public function getPort() {
        return $this->port;
    }

    public function getPrefix() {
        return $this->prefix;
    }

    public function getAddress() {
        return $this->host . ':' . $this->port;
    }

    public function getTopHeight() {
        return $this->es->blockService()->getTopHeight();
    }

    public function getTopCumulativeDifficulty() {
        return $this->es->blockService()->getTopCumulativeDifficulty();
    }

    public function getNextHeight() {
        return $this->getTopHeight() + 1;
    }

    public function getDataPool() {
        $response = $this->getMemoryPool();
        return $response['transactions'];
    }

    public function getBlockByHeight($height = null) {
        return $this->es->blockService()->getByHeight($height);
    }

    public function getBlockByHash($hash = null) {
        return $this->es->blockService()->getByHash($hash, true);
    }

    public function getTransactionByHash($hash = null) {
        return $this->es->transactionService()->getByHash($hash);
    }

    public function getTransferByHash($hash = null) {
        return $this->es->transferService()->getByHash($hash);
    }

    public function getWalletAddressInfos($walletAddress = null, $page = 1) {
        return $this->es->bankService()->getWalletAddressInfos($walletAddress, $page);
    }

    public function getWalletAddressDomain($walletAddress = null, $page = 1) {
        return $this->es->domainService()->getByAddress($walletAddress, 100, $page);;
    }

    public function getDomainByUrl($url = null) {
        return $this->es->websiteService()->getByUrl($url);;
    }

    public function getDomainInfoByUrl($url = null) {
        return $this->es->domainService()->getByUrl($url);;
    }

    public function getAddressBalances($addressList = []) {
        return $this->es->bankService()->getAddressBalances($addressList);;
    }

    public function getChain($fromHeigth = 1, $toHeigth = 100) {
        return $this->es->blockService()->getChain($fromHeigth, $toHeigth);
    }

    public function getLastBlock($limit = 1, $asArray = false, $page = 1, $original = false) {
        return $this->es->blockService()->getLastBlock($limit, $asArray, $page, $original);
    }

    public function getLastBlocks($limit = 1, $asArray = false, $page = 1, $original = false) {
        return $this->es->blockService()->getLastBlocks($limit, $asArray, $page, $original);
    }

    public function getLastDomains() {
        return $this->es->domainService()->getLastDomains();
    }



    public function getInfos() {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'address' => $this->getAddress(),
            'height' => $this->getTopHeight() + 1,
        ];
    }

    private function createGenesisBlock($showGenensisAndExit = false)
    {
        $block = Block::generateGenesisBlock($this->prefix, $showGenensisAndExit);
        $this->saveBlockToDB($block);
        return $block;
    }

    public function push($data)
    {
        $data = (array) $data;
        if (!$this->isSync()) {
            return [
                'error' => 'Blockchain not syncronized'
            ];
        }

        $isMultiple = array_key_exists(0, $data) && array_key_exists('from', $data[0]);
        if ($isMultiple) {
            $response = [];
            foreach ($data as $newTransaction) {
                $response[] = $this->push($newTransaction);
            }
            return $response;
        }

        if (!$isMultiple && !isset($data['from'])) {
            return [
                'error' => '[PUSH] Sender address not found',
                '$data' => $data
            ];
        }

        $todo = @json_decode(base64_decode($data['toDo']), true);
        $isWeb = false;
        if ($todo && !empty($todo)) {
            $_todo = $todo[0];
            $action = $_todo['action'];
            $amount = $data['amount'];
            $url = strtolower($_todo['name']);

            if ($action !== 'update' && !($amount === 99999000000 || $amount === 199999000000 || $amount === 299999000000)) {
                return [
                    'error' => 'Bad domain amount: ' . $amount
                ];
            }

            if ($action === 'update' && $amount !== 999000000) {
                return [
                    'error' => 'Bad domain amount'
                ];
            }

            if (!ctype_alnum($url)) {
                return [
                    'error' => 'Domain name not alphanumeric'
                ];
            }

            if (strlen($url) < 7) {
                return [
                    'error' => 'Domain name too small < 7'
                ];
            }

            if (strlen($url) > 70) {
                return [
                    'error' => 'Domain name too big > 70'
                ];
            }

            if ($action === 'create') {
                $domainExists = $this->es->domainService()->exists($url);
                if ($domainExists) {
                    return [
                        'error' => 'Domain already exists'
                    ];
                }
            }

            if ($action !== 'create') {
                $domainExists = $this->es->domainService()->exists($url);
                if (!$domainExists) {
                    return [
                        'error' => 'Domain not found'
                    ];
                }

                if ($action !== 'renew') {
                    $domain = $this->es->domainService()->getByUrl($url);
                    if ($domain['ownerAddress'] !== $data['from']) {
                        return [
                            'error' => 'Action not authorized, ownerAddress not same'
                        ];
                    }

                    if ($domain['ownerPublicKey'] !== $data['publicKey']) {
                        return [
                            'error' => 'Action not authorized, ownerPublicKey not same'
                        ];
                    }
                }
            }

            $isWeb = true;
        }

        $transaction = new Transaction(null, $this->prefix);
        $transaction->setData($data);

        $transactionPoolExists = $this->es->transactionPoolService()->exists($transaction->getHash());
        if ($transactionPoolExists) {
            return [
                'error' => 'Transaction already exists'
            ];
        }

        $wallet = $this->getAddressBalances($data['from']);

        if (!isset($wallet[$data['from']]) && $data['from'] !== BlockchainConfig::NAME) {
            return [
                'error' => 'Wallet address sender not found'
            ];
        }

        if ($transaction->getAmount() > $wallet[$data['from']]['amount']) {
            return [
                'error' => 'Insufisante wallet funds, available: ' . $wallet[$data['from']]['amount'] . ' ' . BlockchainConfig::SYMBOL
            ];
        }

        if ($transaction->getBankHash() !== $wallet[$data['from']]['hash']) {
            return [
                'error' => 'Invalid bank hash ' . $transaction->getBankHash() . ' excpeted ' . $wallet[$data['from']]['hash'],
            ];
        }

        $mTransaction = $transaction->getInfos();

        if (isset($this->transactionPool[$mTransaction['hash']])) {
            return [
                'error' => 'Transaction already broadcasted'
            ];
        }

        if ($transaction->isValid(true, $isWeb)) {
            $mTransaction = $transaction->getInfos();
            $this->transactionPool[$mTransaction['hash']] = $mTransaction;

            $mTransfers = $transaction->getTransfersJson();

            foreach ($mTransfers as $transfer) {
                $transfer = (array) $transfer;
                $transfer['from'] = $mTransaction['from'];
                $transfer['transactionHash'] = $mTransaction['hash'];

                $this->transferPool[$transfer['hash']] = $transfer;
            }
        } else {
            $mTransaction = false;
            var_dump('[Blockchain] [ERROR] Invalid transaction sent to blockchain');
            return [
                'error' => 'Invalid transaction sent to blockchain'
            ];
        }

        $limit = 100;
        $countTransactions = count($this->transactionPool);
        if (!empty($this->transactionPool)
            && ($countTransactions >= $limit || (time() - $this->lastTransactionPool) > 10))
        {
            try {
                $this->es->transferPoolService()->bulkIndex($this->transferPool);
                $this->es->transactionPoolService()->bulkIndex($this->transactionPool);
            } catch(\Exception $e) {
                return [
                    'error' => $e->getMessage()
                ];
            }

            // Clean
            $this->transferPool = [];
            $this->transactionPool = [];
            $this->lastTransactionPool = time();
        }

        return $mTransaction;
    }

    public function pushMessage($data)
    {
        if (!$this->isSync()) {
            return false;
        }

        $data = (array) $data;


        $wallet = $this->getAddressBalances([$data['from'], $data['to']]);

        if (!isset($wallet[$data['from']])) {
            return [
                'error' => 'Wallet address sender not found'
            ];
        }

         if (!isset($wallet[$data['to']])) {
            return [
                'error' => 'Wallet address receiver not found'
            ];
        }

        $message = new Message();
        $message->setData($data);

        $messagePoolExists = $this->es->messagePoolService->exists($message->getMessage());

        if ($messagePoolExists) {
            return false;
        }

        $mMessage = $message->getInfos();
        if ($message->isValid()) {
            $mMessage = $message->getInfos();
            $mMessage['hash'] = $message->getMessage();
            $this->es->messagePoolService->index($mMessage['hash'], $mMessage);

            return $message->getInfos();
        }

        return false;
    }

    public function getMessagesByAddresses($id = null)
    {
        return $this->es->messagePoolService->getMessagesByAddresses($id);
    }

    public function getLastMessagesPool($timestamp = null)
    {
        return $this->es->messagePoolService->getLastMessagesPool($timestamp);
    }

    public function getMemoryPool()
    {
        $response = [
            'count' => 0,
            'transactions' => []
        ];

        $this->clearMemoryPool();

        $response = $this->es->transactionPoolService->all();

        if (isset($response['error'])) {
            return $response;
        }

        $holders = [];
        $addressBalanceFrom = [];
        $filteredTransactions = [];
        $invalidTransactions = [];
        $transactionToSend = [];

        foreach ($response['transactions'] as $transaction) {
            if ($this->es->transactionService()->exists($transaction['hash'])) {
                $invalidTransactions[] = $transaction['hash'];
                continue;
            }

            $_transaction = (new Transaction())->setData($transaction);

            if (!in_array($transaction['from'],  $holders)) {
                $holders[] = $transaction['from'];
            }

            $filteredTransactions[] = $transaction;
        }

        $holdersData = $this->es->bankService()->getAddressBalances($holders);

        $errors = [];
        foreach ($filteredTransactions as $transaction) {
            $address = $transaction['from'];
            if (isset($holdersData[$address]) && $holdersData[$address]['amount'] >= $transaction['amount'] &&  $holdersData[$address]['amount'] > 0) {
                $transactionToSend[] = $transaction;
                $holdersData[$address]['amount'] -= $transaction['amount'];
            } else {
                $invalidTransactions[] = $transaction['hash'];
                $errors[] = [$address, $transaction['amount']];
            }
        }

        $cCount = count($errors);

        $this->clearInvalidMemoryPool($invalidTransactions);

        $response['count'] = count($transactionToSend);
        $response['transactions'] = $transactionToSend;

        return $response;
    }

    public function savePeer($remoteAddress) {
        $this->es->peerSerivce()->save($remoteAddress);
    }

    public function getSavedPeers() {
        $this->es->peerSerivce()->getRemoteAddresses();
    }

    public function add($block): bool
    {
        // If other peers sending same block
        if ($this->getLastBlock() && $this->getLastBlock()->getHeight() >= $block->getHeight()) {
            return true;
        }

        if ($this->getLastBlock() && !$this->getLastBlock()->isNextValid($block)) {
            $this->removeBlock($this->getLastBlock());
            return false;
        }

        if (!$this->isValidTransactions($block)) {
            var_dump('[Blockchain] [add] [ERROR] : ' . $block->getHeight());
            return false;
        }

        var_dump('[Blockchain] [add] Height => ' . $block->getHeight());
        $this->logger->info('[Blockchain] [add] Height => ' . $block->getHeight());

        $this->saveBlockToDB($block);
        $this->clearMemoryPool();

        return true;
    }

    public function bulkAdd($blocks): bool
    {
        $previousBlock = $this->getLastBlock();

        if (!isset($blocks[0])) {
            var_dump('----- AA1');
            return false;
        }

        $firstBlock = $blocks[0];

        if (empty($firstBlock)) {
            var_dump('----- AA2');
            return false;
        }


        if ($previousBlock && $firstBlock->getHeight() < $previousBlock->getHeight()) {
            return true;
        }

        $startTime = microtime(true);
        foreach ($blocks as $block) {
            if (null !== $previousBlock) {
                if ($previousBlock->getHeight() === $block->getHeight()) {
                    continue;
                }

                if (!$previousBlock->isNextValid($block)) {
                    var_dump('----- AA3', $previousBlock->getHeight(), $block->getHeight());
                    return false;
                }
            }

            if (!$this->isValidTransactions($block)) {
                var_dump('[Blockchain] [add] [ERROR] : ' . $block->getHeight());
                return false;
            }

            $previousBlock = $block;
            // var_dump('[Blockchain] [buldAdd] Height => ' . $block->getHeight());
            $this->logger->info('[Blockchain] [buldAdd] Height => ' . $block->getHeight());
        }


        $this->saveBlocksToDB($blocks);
        $this->clearMemoryPool();

        $endTime = microtime(true);
        $execTime = $endTime - $startTime;
        $nbBlocks = count($blocks);
        var_dump('[Blockchain] [buldAdd] ' . $nbBlocks . ' blocks added in ' . $execTime .' sec');

        return true;
    }

    public function clearMemoryPool() {
        $lastBlock = $this->getLastBlock();

        if (null === $lastBlock) {
            return;
        }

        $range = 10;
        $lastBlockHeight = $lastBlock->getHeight();
        $fromBlockHeight = $lastBlockHeight - $range;

        if ($lastBlockHeight - $range < 1) {
            $lastBlockHeight = 1;
        }

        $lastTxHash = $this->es->transactionService()->getChain($fromBlockHeight, $lastBlockHeight);
        $this->es->transactionPoolService()->delete($lastTxHash);
        $this->es->transferPoolService()->deleteByTransaction($lastTxHash);
    }

    public function clearInvalidMemoryPool($txHash)
    {
        $this->es->transactionPoolService()->delete($txHash);
    }

    private function saveBlocksToDB($blocks, $resetMode = false) {

        $data = [];
        $height = 1;
        foreach ($blocks as $block) {
            $height = $block->getHeight();
            $data[$height] = $block->getInfos();
        }

        $this->es->blockService()->bulkBlocks($data, $resetMode);
        return true;
    }

    private function saveBlockToDB($block)
    {
        $this->es->blockService()->index($block->getHeight(), $block->getInfos());
        return true;
    }

    public function resetBank($hardScan = true, $resetMode = true, $showGenensisAndExit = false)
    {

        if ($resetMode) {
            if (!$showGenensisAndExit) {
                $this->es->bankService()->reset()->initIndex();
                $this->es->transferService()->reset()->initIndex();
                $this->es->transactionPoolService()->reset()->initIndex();
                $this->es->transferService()->reset()->initIndex();
                $this->es->transferPoolService()->reset()->initIndex();
                $this->es->todoService()->reset()->initIndex();
                $this->es->domainService()->reset()->initIndex();
            }

            $this->createGenesisBlock($showGenensisAndExit);
        } else {
            $showGenensisAndExit && $this->createGenesisBlock($showGenensisAndExit);
        }

        return !$showGenensisAndExit && $this->scanFromZero($hardScan, $resetMode);
    }

    public function scanFromZero($hardScan = false , $resetMode = false)
    {
        var_dump('[Blockchain] Start Scan...');

        $lastBlock = $this->getLastBlock();

        if (null === $lastBlock) {
            var_dump('[Blockchain] Last block is null');
            return true;
        }

        $range = 100;
        $fromBlockHeight = 0;
        $topBlockHeight = $lastBlock->getHeight();

        if ($topBlockHeight < $this->getTopKnowHeight()) {
            $this->setTopKnowHeight($topBlockHeight);
        }

        $toBlockHeight = $fromBlockHeight + $range;
        if ($toBlockHeight > $topBlockHeight) {
            $toBlockHeight = $topBlockHeight;
        }

        $previousBlock = null;

        while(!empty($blocks = $this->es->blockService()->getChain($fromBlockHeight, $toBlockHeight))) {
            if ($toBlockHeight === $fromBlockHeight && $topBlockHeight === $toBlockHeight) {
                return true;
            }

            var_dump('[Blockchain] Scan from ' . $fromBlockHeight . ' to ' . $toBlockHeight . ' on ' . $topBlockHeight . ' Blocks');
            $this->logger->info('[Blockchain] Scan from ' . $fromBlockHeight . ' to ' . $toBlockHeight . ' on ' . $topBlockHeight . ' Blocks');

            foreach ($blocks as $block) {
                if (null !== $previousBlock) {
                    if ($previousBlock->getHeight() === $block->getHeight()) {
                        var_dump('[Blockchain] $previousBlock->getHeight === $block->getHeight');
                        continue;
                    }

                    if (!$previousBlock->isNextValid($block)) {
                        var_dump('[Blockchain] [error] $previousBlock->isNextValid');
                        return false;
                    }
                }

                if ($hardScan && !$this->isValidTransactions($block)) {
                    var_dump('[Blockchain] [add] [ERROR] Block: ' . $block->getHeight());
                    return false;
                }

                $previousBlock = $block;
            }

            $fromBlockHeight = $toBlockHeight;
            $toBlockHeight = $toBlockHeight + $range;
            if ($toBlockHeight > $topBlockHeight) {
                $toBlockHeight = $topBlockHeight;
            }

            if ($resetMode) {
                $this->saveBlocksToDB($blocks, true);
            }

        }

        return true;
    }

    public function isValidTransactions(Block $block)
    {
        $blockInfos = $block->getJsonInfos();

        $transactions = $blockInfos['data'];
        if ($blockInfos['height'] === 0) {
            return true;
        }

        if (!is_array($transactions) || empty($transactions)) {
            var_dump('[Blockchain][isValidTransactions] Empty transactions');
            return false;
        }

        $minerReward = $this->es->transactionService()->getMinerRewardAmount();
        if (!$minerReward) {
            $minerReward = Block::MINER_REWARD;
        }

        foreach ($transactions as $_transation) {
            $transaction = new Transaction(null, $this->prefix);
            $transaction->setData((array) $_transation);

            if (!$transaction->isValid(true, true)) {
                return false;
            }

            if ($transaction->isCoinbase() && (int) $transaction->getAmount() !== (int) $minerReward) {
                return false;
            }
        }

        return true;
    }

    public function setTopKnowHeight(int $height): self
    {
        $topHeight = $this->getTopHeight();
        $this->topKnowHeight = $height > $topHeight ? $height : $topHeight;

        return $this;
    }

    public function setTopCumulativeDifficulty(int $difficulty): self
    {
        $topHeight = $this->getTopHeight();
        $this->topKnowHeight = $height > $topHeight ? $height : $topHeight;

        return $this;
    }

    public function getNextDifficulty(){
        $blockInterval = 10;

        $lastBlocks = $this->getLastBlock(BlockchainConfig::DIFFICULTY_TARGET);
        $lastBlock = $lastBlocks[0];

        if (!$lastBlock) {
            return 1;
        }

        $count = count($lastBlocks);
        $adjutBlock = $lastBlocks[$count - 1];
        $difficulty = $lastBlock->getDifficulty();
        if ($lastBlock->getHeight() % BlockchainConfig::DIFFICULTY_TARGET === 0 && $lastBlock->getHeight() !== 0) {

            $timeExpected = BlockchainConfig::BLOCK_TARGET * $count;
            $timeTaken = $lastBlock->getCreatedAt() - $adjutBlock->getCreatedAt();

            if ($timeTaken < $timeExpected) {
                $difficulty = $adjutBlock->getDifficulty() + 1;
            } else if ($timeTaken > $timeExpected) {
                $difficulty = $adjutBlock->getDifficulty() - 1;
            } else {
                $difficulty = $adjutBlock->getDifficulty();
            }
        }

        return $difficulty;
    }

    public function getTopKnowHeight(): int
    {
        $topHeight = $this->getTopHeight();
        return $topHeight > $this->topKnowHeight ? $topHeight : $this->topKnowHeight;
    }

    public function getTotalTransaction()
    {
        return $this->es->transactionService()->count();
    }

    public function getTotalTransfer()
    {
        return $this->es->transferService()->count();
    }

    public function getBankAmount()
    {
        return $this->es->bankService()->getBankAmount(BlockchainConfig::NAME);
    }

    public function generateWallet()
    {
        return PKI::newEcKeys();
    }

    public function cutBlockchainFromHeight($height) {

    }

    public function isSync() {
        $this->topHeight = $this->getTopHeight();

        $numberLastBlocks = 5;

        return $this->topKnowHeight === $this->topHeight
            || $this->topHeight > $this->topKnowHeight
            || $this->topKnowHeight - $this->topHeight < $numberLastBlocks;
    }
}


