<?php

namespace Inescoin\Entity;

class Transfer extends AbstractEntity
{
	protected $height; // 'height' => 'integer',
	protected $createdAt; // 'createdAt' => 'integer',
	protected $fromWalletId; // 'fromWalletId' => 'text',
    protected $toWalletId; // 'toWalletId' => 'text',
	protected $amount; // 'amount' => 'integer',
	protected $nonce; // 'nonce' => 'text',
	protected $hash; // 'hash' => 'text PRIMARY KEY',
    protected $transactionHash; // 'transactionHash' => 'text',
	protected $reference; // 'reference' => 'text',

    public function __construct(array $data = [])
    {
        parent::__construct($data);
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
    public function getFromWalletId()
    {
        return $this->fromWalletId;
    }

    /**
     * @param mixed $fromWalletId
     *
     * @return self
     */
    public function setFromWalletId($fromWalletId)
    {
        $this->fromWalletId = $fromWalletId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getToWalletId()
    {
        return $this->toWalletId;
    }

    /**
     * @param mixed $toWalletId
     *
     * @return self
     */
    public function setToWalletId($toWalletId)
    {
        $this->toWalletId = $toWalletId;

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
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * @param mixed $nonce
     *
     * @return self
     */
    public function setNonce($nonce)
    {
        $this->nonce = $nonce;

        return $this;
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
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param mixed $reference
     *
     * @return self
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }
}
