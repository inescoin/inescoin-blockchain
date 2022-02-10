<?php

namespace Inescoin\Entity;

class Bank extends AbstractEntity
{

    protected $amount; // 'amount' => 'real',
	protected $height; // 'lastHeight' => 'integer',
	protected $address; // 'address' => 'text PRIMARY KEY',
	protected $hash; // 'hash' => 'text',
	protected $transactionHash; // 'transactionHash' => 'text',
	protected $transferHash; // 'transferHash' => 'text'

    public function __construct(array $data = [])
    {
        parent::__construct($data);
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
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     *
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;

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
    public function getTransferHash()
    {
        return $this->transferHash;
    }

    /**
     * @param mixed $transferHash
     *
     * @return self
     */
    public function setTransferHash($transferHash)
    {
        $this->transferHash = $transferHash;

        return $this;
    }
}
