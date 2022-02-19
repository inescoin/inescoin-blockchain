<?php


namespace Inescoin\Manager;

class BlockchainManager
{
	private $block;
	private $blockTemp;
	private $bank;
	private $transaction;
	private $transactionPool;
	private $transfer;
	private $transferPool;
	private $todo;
	private $peer;
	private $website;
	private $domain;
	private $message;
	private $messagePool;

	static $instance;

	public function __construct($database)
	{
		$this->block = new BlockManager($database);
		$this->blockTemp = new BlockTempManager($database);
		$this->bank = new BankManager($database);
		$this->transaction = new TransactionManager($database);
		$this->transactionPool = new TransactionPoolManager($database);
		$this->transfer = new TransferManager($database);
		$this->transferPool = new TransferPoolManager($database);
		$this->todo = new TodoManager($database);
		$this->peer = new PeerManager($database);
		$this->website = new WebsiteManager($database);
		$this->domain = new DomainManager($database);
		$this->message = new MessageManager($database);
		$this->messagePool = new MessagePoolManager($database);
	}

	static function getInstance($database): BlockchainManager
	{
		if (null === self::$instance) {
			self::$instance = new self($database);
		}

		return self::$instance;
	}

    /**
     * @return BlockManager
     */
    public function getBlock(): BlockManager
    {
        return $this->block;
    }

    /**
     * @return BlockTempManager
     */
    public function getBlockTemp(): BlockTempManager
    {
        return $this->blockTemp;
    }

    /**
     * @return BankManager
     */
    public function getBank(): BankManager
    {
        return $this->bank;
    }

    /**
     * @return TransactionManager
     */
    public function getTransaction(): TransactionManager
    {
        return $this->transaction;
    }

    /**
     * @return TransactionPoolManager
     */
    public function getTransactionPool(): TransactionPoolManager
    {
        return $this->transactionPool;
    }

    /**
     * @return TransferManager
     */
    public function getTransfer(): TransferManager
    {
        return $this->transfer;
    }

    /**
     * @return TransferPoolManager
     */
    public function getTransferPool(): TransferPoolManager
    {
        return $this->transferPool;
    }

    /**
     * @return TodoManager
     */
    public function getTodo(): TodoManager
    {
        return $this->todo;
    }

    /**
     * @return mixed
     */
    public function getPeer(): PeerManager
    {
        return $this->peer;
    }

    /**
     * @return WebsiteManager
     */
    public function getWebsite(): WebsiteManager
    {
        return $this->website;
    }

    /**
     * @return DomainManager
     */
    public function getDomain(): DomainManager
    {
        return $this->domain;
    }

    /**
     * @return MessageManager
     */
    public function getMessage(): MessageManager
    {
        return $this->message;
    }

    /**
     * @return MessagePoolManager
     */
    public function getMessagePool(): MessagePoolManager
    {
        return $this->messagePool;
    }

    public function dropTables()
    {
        $this->block->dropTable();
        $this->resetTables();
    }

    public function resetTables()
    {
        $this->blockTemp->dropTable();
        $this->bank->dropTable();
        $this->transaction->dropTable();
        $this->transactionPool->dropTable();
        $this->transfer->dropTable();
        $this->transferPool->dropTable();
        $this->todo->dropTable();
        // $this->peer->dropTable();
        $this->website->dropTable();
        $this->domain->dropTable();
        $this->message->dropTable();
        $this->messagePool->dropTable();
    }
}
