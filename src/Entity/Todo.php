<?php

namespace Inescoin\Entity;

class Todo extends AbstractEntity
{
	protected $hash; // 'hash' => 'text',
	protected $ownerAddress; // 'ownerAddress' => 'text',
    protected $ownerPublicKey; // 'ownerPublicKey' => 'text',
	protected $keyName; // 'ownerPublicKey' => 'text',
	protected $command; // 'command' => 'text',
	protected $height; // 'height' => 'integer',
	protected $amount; // 'amount' => 'real',
	protected $transactionHash; // 'transactionHash' => 'text PRIMARY KEY',
	protected $createdAt; // 'createdAt' => 'text'

	public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param mixed $hash
     *
     * @return self
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOwnerAddress()
    {
        return $this->ownerAddress;
    }

    /**
     * @param mixed $ownerAddress
     *
     * @return self
     */
    public function setOwnerAddress($ownerAddress)
    {
        $this->ownerAddress = $ownerAddress;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOwnerPublicKey()
    {
        return $this->ownerPublicKey;
    }

    /**
     * @param mixed $ownerPublicKey
     *
     * @return self
     */
    public function setOwnerPublicKey($ownerPublicKey)
    {
        $this->ownerPublicKey = $ownerPublicKey;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     *
     * @return self
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     *
     * @return self
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     *
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransactionHash()
    {
        return $this->transactionHash;
    }

    /**
     * @param mixed $transactionHash
     *
     * @return self
     */
    public function setTransactionHash($transactionHash)
    {
        $this->transactionHash = $transactionHash;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * @param mixed $keyName
     *
     * @return self
     */
    public function setKeyName($keyName)
    {
        $this->keyName = $keyName;

        return $this;
    }
}
