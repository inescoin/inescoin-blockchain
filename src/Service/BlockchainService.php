<?php

namespace Inescoin\Service;

use Inescoin\BlockchainConfig;
use Inescoin\Entity\Block;
use Inescoin\Entity\Domain;
use Inescoin\Entity\TransactionWallet;
use Inescoin\Entity\Transfer;
use Inescoin\Helper\BlockHelper;
use Inescoin\Helper\MerkleTree;
use Inescoin\Helper\TransactionHelper;
use Inescoin\Manager\BlockchainManager;
use Inescoin\Model\Block as BlockModel;
use Inescoin\Model\Transaction;
use Inescoin\Service\LoggerService;
use Psr\Log\LoggerInterface;

class BlockchainService {

	/**
	 * @var string
	 */
	protected string $prefix;

	/**
	 * @var string
	 */
	protected string $pathToBlockDirectory;

	/**
	 * @var LoggerInterface
	 */
	protected LoggerInterface $logger;

	/**
	 * @var BlockchainManager
	 */
	protected BlockchainManager $blockchainManager;

	/**
	 * @var ?BlockchainService[]
	 */
	static $instance;

    /**
     * @var array
     */
    private array $messagePool = [];

    /**
     * @var array
     */
    private array $transactionPool = [];

    /**
     * @var array
     */
    private array $transferPool = [];

    /**
     * @var ?int
     */
    private ?int $lastTransactionPool = null;

	/**
	 * @param ?string $prefix         			Prefixed blockchain name
	 * @param ?string $pathToBlockDirectory 	Path to database directory
	 */
	public function __construct(
		?string $prefix = BlockchainConfig::NAME,
		?string $pathToBlockDirectory = null,
		bool $resetMode = false,
		bool $showGenensisAndExit = false)
	{
        $this->prefix = $prefix ?? BlockchainConfig::NAME;

        $this->pathToBlockDirectory = $pathToBlockDirectory ?? __DIR__ . '/../../';
        $this->configHash = BlockchainConfig::getHash();

        $this->logger = (LoggerService::getInstance())->getLogger();

        $this->blockchainManager = BlockchainManager::getInstance($this->prefix);

        if(!$this->start($resetMode, $showGenensisAndExit)) {
            $this->logger->error("Init: Invalid blockchain, you need to resync from 0");
            die();
        }
    }

    /**
	 * @param ?string $prefix
	 * @param ?string $pathToBlockDirectory
	 */
	static function getInstance(
        ?string $prefix = BlockchainConfig::NAME,
        ?string $pathToBlockDirectory = null,
        bool $resetMode = false,
        bool $showGenensisAndExit = false): BlockchainService
	{
		if (!isset(self::$instance)) {
			self::$instance = new self($prefix, $pathToBlockDirectory, $resetMode, $showGenensisAndExit);
		}

		return self::$instance;
	}

	public function getPrefix(): ?string
	{
		return $this->prefix;
	}

	public function getPathToBlockDirectory(): ?string
	{
		return $this->pathToBlockDirectory;
	}

	public function getBlockchainManager(): BlockchainManager
	{
		return $this->blockchainManager;
	}

	public function getNextDifficulty(){
        $blockInterval = 10;

        var_dump("              > Check difficulty");
        $lastBlocks = $this->blockchainManager
        	->getBlock()
        	->range(0, $blockInterval, 'height', 'DESC');

        if (!isset($lastBlocks[0])) {
            var_dump("              > lastBlock Not found");
            return BlockchainConfig::MIN_DIFFICULTY;
        }

        $lastBlock = $lastBlocks[0];

        var_dump("              > Last Height:" . $lastBlock->getHeight());

        $count = count($lastBlocks);
        $adjutBlock = $lastBlocks[$count - 1];
        $difficulty = $lastBlock->getDifficulty();

        if ($lastBlock->getHeight() % BlockchainConfig::DIFFICULTY_TARGET === 0 && $lastBlock->getHeight() !== 0) {
            $timeExpected = BlockchainConfig::BLOCK_TARGET * $count;
            $timeTaken = $lastBlock->getCreatedAt() - $adjutBlock->getCreatedAt();

            var_dump("              > timeTaken: $timeTaken, timeExpected: $timeExpected ---------------");
            var_dump("              > " . $timeTaken / $timeExpected * 100);

            $percent = 100 - (int) ($timeTaken / $timeExpected * 100);
            $delta = abs(floor($lastBlock->getDifficulty() * ($percent / 100) / 5));
            var_dump("              > Percent: " . $percent);
            var_dump("              > Delta: " . $delta);

            if ($percent < -10000) {
                return BlockchainConfig::MIN_DIFFICULTY;
            }

            if ($timeTaken < $timeExpected) {
                $difficulty = $lastBlock->getDifficulty() + $delta;
            } else if ($timeTaken > $timeExpected) {
                $difficulty = $lastBlock->getDifficulty() - $delta;
            } else {
                $difficulty = $lastBlock->getDifficulty();
            }
        }

        return $difficulty;
    }

    private function createGenesisBlock($showGenensisAndExit = false, $resetMode = false): void
    {
        $block = BlockHelper::generateGenesisBlock($this->prefix, $showGenensisAndExit);

        if ($this->blockchainManager->getBlock()->exists($block->getHash(), 'hash')) {
            var_dump("Genesis Block already exist :)");
        } else {
            $isDone = $this->addBlock($block, null, false);

            if ($isDone) {
                var_dump("Genesis Block Done !");
            } else {
                var_dump("Genesis Block Creation Error !");
            }
        }
    }

    public function addBlock($block, $lastBlock = null, $resetMode = false): bool
    {
    	if (null !== $lastBlock && $block->getHeight() !== 0) {
	    	// If other peers sending same block
	        if ($lastBlock->getHeight() >= $block->getHeight()) {
	            $this->logger->error('[Blockchain] [addBlock] big height => ' . $block->getHeight());
	            return false;
	        }

	        if (!$lastBlock->isNextValid($block)) {
	            $this->logger->error('[Blockchain] [addBlock] isNextValid => ' . $block->getHeight());
	            return false;
	        }

	        if (!TransactionHelper::isValidTransactions($block, $this->prefix)) {
	            $this->logger->error('[Blockchain] [addBlock] isValidTransactions => ' . $block->getHeight());
	            return false;
	        }
    	}

    	$blockInDB = $this->blockchainManager->getBlock()->selectFisrt($block->getHeight());

    	if (null !== $blockInDB) {
            return false;
    	}

        $block = new Block($block->getInfos());

    	$countRow = BlockHelper::extractBlock([$block], $this->prefix, $resetMode);

        return !!$countRow;
    }

    public function start($resetMode = false, $showGenensisAndExit = false): bool
    {
        if ($resetMode && !$showGenensisAndExit) {
            $this->blockchainManager->resetTables();
        }

        $this->createGenesisBlock($showGenensisAndExit, $resetMode);

        return !$showGenensisAndExit && $this->scanFromZero($resetMode);
    }

    public function scanFromZero($resetMode = false): bool
    {
        $this->logger->info('[Blockchain] Start Scan...');

        $lastBlock = $this->blockchainManager->getBlock()->last();

        $range = 1000;
        $fromBlockHeight = 0;
        $topBlockHeight = $lastBlock ? $lastBlock->getHeight() : 0;

        $toBlockHeight = $fromBlockHeight + $range;
        if ($toBlockHeight > $topBlockHeight) {
            $toBlockHeight = $topBlockHeight;
        }

        $previousBlock = null;

        while(!empty($blocks = $this->blockchainManager->getBlock()->range($fromBlockHeight, $range))) {
            if ($toBlockHeight === $fromBlockHeight && $topBlockHeight === $toBlockHeight) {
                return true;
            }

            var_dump('[Blockchain] Scan from ' . $fromBlockHeight . ' to ' . $toBlockHeight . ' on ' . $topBlockHeight . ' Blocks');
            $this->logger->info('[Blockchain] Scan from ' . $fromBlockHeight . ' to ' . $toBlockHeight . ' on ' . $topBlockHeight . ' Blocks');

            foreach ($blocks as $block) {
            	$blockModel = new BlockModel($block->getDataAsArray());

                if (null !== $previousBlock) {
                    if ($previousBlock->getHeight() === $block->getHeight()) {
                        $this->logger->info('[Blockchain] $previousBlock->getHeight === $block->getHeight');
                        continue;
                    }


                    if (!$previousBlock->isNextValid($blockModel)) {
                        $this->logger->info('[Blockchain] [error] $previousBlock->isNextValid');

                        return false;
                    }
                }

                if ($resetMode && !TransactionHelper::isValidTransactions($blockModel, $this->prefix)) {
                    $this->logger->info('[Blockchain] [add] [ERROR] Block: ' . $block->getHeight());
                    return false;
                }

                $previousBlock = $blockModel;
            }

            $fromBlockHeight = $toBlockHeight;
            $toBlockHeight = $toBlockHeight + $range;
            if ($toBlockHeight > $topBlockHeight) {
                $toBlockHeight = $topBlockHeight;
            }

            if ($resetMode) {
                BlockHelper::extractBlock($blocks, $this->prefix, true);
            }
        }

        return true;
    }

    public function push($data)
    {
        $data = (array) $data;

        $isMultiple = array_key_exists(0, $data) && array_key_exists('fromWalletId', $data[0]);

        if ($isMultiple) {
            $response = [];
            foreach ($data as $newTransaction) {
                $response[] = $this->push($newTransaction);
            }
            return $response;
        }

        if (!$isMultiple && !isset($data['fromWalletId'])) {
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

            if ($action !== BlockchainConfig::WEB_ACTION_UPDATE && !in_array($amount, BlockchainConfig::WEB_COSTS_WITHOUT_UPDATE)) {
                return [
                    'error' => 'Bad domain amount: ' . $amount
                ];
            }

            if ($action === BlockchainConfig::WEB_ACTION_UPDATE && $amount !== BlockchainConfig::WEB_COST_UPDATE) {
                return [
                    'error' => 'Bad domain amount'
                ];
            }

            if (!ctype_alnum($url)) {
                return [
                    'error' => 'Domain name not alphanumeric'
                ];
            }

            if (strlen($url) < BlockchainConfig::WEB_URL_MIN_SIZE) {
                return [
                    'error' => 'Domain name too small < ' . BlockchainConfig::WEB_URL_MIN_SIZE
                ];
            }

            if (strlen($url) > BlockchainConfig::WEB_URL_MAX_SIZE) {
                return [
                    'error' => 'Domain name too big > ' . BlockchainConfig::WEB_URL_MAX_SIZE
                ];
            }

            $domainExists = $this->blockchainManager->getDomain()->exists($url, 'url');

            if ($action === BlockchainConfig::WEB_ACTION_CREATE) {
                if ($domainExists) {
                    return [
                        'error' => 'Domain already exists'
                    ];
                }
            }

            if ($action !== BlockchainConfig::WEB_ACTION_CREATE) {
                if (!$domainExists) {
                    return [
                        'error' => 'Domain not found'
                    ];
                }

                if ($action !== BlockchainConfig::WEB_ACTION_RENEW) {
                    /** @var Domain $domain */
                    $domain = $this->blockchainManager->getDomain()->selectFisrt($url, 'url');

                    if ($domain->getOwnerAddress() !== $data['fromWalletId']) {
                        return [
                            'error' => 'Action not authorized, ownerAddress not same'
                        ];
                    }

                    if ($domain->getOwnerPublicKey() !== $data['publicKey']) {
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

        $transactionWallet = new TransactionWallet($transaction->getInfos());

        $transactionPoolExists = $this->blockchainManager->getTransactionPool()->exists($transaction->getHash(), 'hash');

        if ($transactionPoolExists) {
            return [
                'error' => 'Transaction already exists'
            ];
        }

        $wallet = $this->blockchainManager->getBank()->getAddressBalances($data['fromWalletId'], true);

        if (!isset($wallet[$data['fromWalletId']]) && $data['fromWalletId'] !== BlockchainConfig::NAME) {
            return [
                'error' => 'Wallet address sender not found'
            ];
        }

        if ($transaction->getAmount() > $wallet[$data['fromWalletId']]['amount']) {
            return [
                'error' => 'Insufisante wallet funds, available: ' . $wallet[$data['fromWalletId']]['amount'] . ' ' . BlockchainConfig::SYMBOL
            ];
        }

        if ($transaction->getBankHash() !== $wallet[$data['fromWalletId']]['hash']) {
            return [
                'error' => 'Invalid bank hash ' . $transaction->getBankHash() . ' excpeted ' . $wallet[$data['fromWalletId']]['hash'],
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
            $this->transactionPool[$mTransaction['hash']] = $transactionWallet;

            $mTransfers = $transaction->getTransfersJson();

            foreach ($mTransfers as $transfer) {
                $transfer = (array) $transfer;
                $transfer['fromWalletId'] = $mTransaction['fromWalletId'];
                $transfer['transactionHash'] = $mTransaction['hash'];
                $transfer['createdAt'] = $mTransaction['createdAt'];

                $this->transferPool[$transfer['hash']] = new Transfer($transfer);
            }
        } else {
            $mTransaction = false;
            $this->logger->error('[Blockchain] [ERROR] Invalid transaction sent to blockchain');
            return [
                'error' => 'Invalid transaction sent to blockchain'
            ];
        }

        $limit = 100;
        $countTransactions = count($this->transactionPool);
        $isLimited = $countTransactions >= $limit || (time() - $this->lastTransactionPool) > 10;

        if (!empty($this->transactionPool) && $isLimited) {
            try {
                $this->blockchainManager->getTransferPool()->bulkSave($this->transferPool);
                $this->blockchainManager->getTransactionPool()->bulkSave($this->transactionPool);
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

    public function getDataPool() {
        $response = $this->getMemoryPool();
        return $response['transactions'];
    }

    public function getMemoryPool()
    {
        $response = [
            'count' => 0,
            'transactions' => []
        ];

        $this->clearMemoryPool();

        $transactionPools = $this->blockchainManager->getTransactionPool()->range(0, 100, 'fee');

        if (empty($transactionPools)) {
            return $response;
        }

        $holders = [];
        $addressBalanceFrom = [];
        $filteredTransactions = [];
        $invalidTransactions = [];
        $transactionToSend = [];

        foreach ($transactionPools as $transaction) {
            if ($this->blockchainManager->getTransaction()->exists($transaction->getHash())) {
                $invalidTransactions[] = $transaction->getHash();

                continue;
            }

            if (!in_array($transaction->getFromWalletId(),  $holders)) {
                $holders[] = $transaction->getFromWalletId();
            }

            $filteredTransactions[] = $transaction;
        }


        $holdersData = $this->blockchainManager->getBank()->getAddressBalances($holders, true);

        $errors = [];
        foreach ($filteredTransactions as $transaction) {
            $address = $transaction->getFromWalletId();
            if (isset($holdersData[$address]) && $holdersData[$address]['amount'] >= $transaction->getAmount() &&  $holdersData[$address]['amount'] > 0) {
                $transactionToSend[] = $transaction->getDataAsArray();
                $holdersData[$address]['amount'] -= $transaction->getAmount();
            } else {
                $invalidTransactions[] = $transaction->getHash();
                $errors[] = [$address, $transaction->getAmount()];
            }
        }

        $cCount = count($errors);

        $this->blockchainManager->getTransactionPool()->deleteOldTransactions($invalidTransactions);

        $response['count'] = count($transactionToSend);
        $response['transactions'] = $transactionToSend;

        return $response;
    }

    public function clearMemoryPool() {
        $lastBlock = $this->blockchainManager->getBlock()->last();

        if (null === $lastBlock) {
            return;
        }

        $range = 10;
        $lastBlockHeight = $lastBlock->getHeight();
        $fromBlockHeight = $lastBlockHeight - $range;

        if ($lastBlockHeight - $range < 1) {
            $lastBlockHeight = 1;
        }

        $lastTransactions = $this->blockchainManager->getTransaction()->range($fromBlockHeight, $range);
        $transactionsToRemove = [];

        foreach ($lastTransactions as $transaction) {
            $transactionsToRemove[] = $transaction->getHash();
        }

        $this->blockchainManager->getTransferPool()->deleteOldTransactions($transactionsToRemove);
        $this->blockchainManager->getTransactionPool()->deleteOldTransactions($transactionsToRemove);
    }
}
