<?php

namespace Inescoin\Entity;

class TransactionWallet extends AbstractEntity
{
    protected $amount;
    protected $bankHash;
    protected $configHash;
    protected $amountWithFee;
    protected $height;
    protected $coinbase;
    protected $createdAt;
    protected $fee;
    protected $fromWalletId;
    protected $hash;
    protected $publicKey;
    protected $signature;
    protected $transfers;
    protected $toDo;
    protected $toDoHash;
    protected $url;
    protected $urlAction;

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
    public function getBankHash()
    {
        return $this->bankHash;
    }

    /**
     * @param mixed $bankHash
     *
     * @return self
     */
    public function setBankHash($bankHash)
    {
        $this->bankHash = $bankHash;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfigHash()
    {
        return $this->configHash;
    }

    /**
     * @param mixed $configHash
     *
     * @return self
     */
    public function setConfigHash($configHash)
    {
        $this->configHash = $configHash;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmountWithFee()
    {
        return $this->amountWithFee;
    }

    /**
     * @param mixed $amountWithFee
     *
     * @return self
     */
    public function setAmountWithFee($amountWithFee)
    {
        $this->amountWithFee = $amountWithFee;

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
    public function getCoinbase()
    {
        return $this->coinbase;
    }

    /**
     * @param mixed $coinbase
     *
     * @return self
     */
    public function setCoinbase($coinbase)
    {
        $this->coinbase = $coinbase;

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
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * @param mixed $fee
     *
     * @return self
     */
    public function setFee($fee)
    {
        $this->fee = $fee;

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
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param mixed $publicKey
     *
     * @return self
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param mixed $signature
     *
     * @return self
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransfers()
    {
        return $this->transfers;
    }

    /**
     * @param mixed $transfers
     *
     * @return self
     */
    public function setTransfers($transfers)
    {
        $this->transfers = is_array($transfers)
            ? serialize($transfers)
            : $transfers;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getToDo()
    {
        return $this->toDo;
    }

    /**
     * @param mixed $toDo
     *
     * @return self
     */
    public function setToDo($toDo)
    {
        $this->toDo = is_array($toDo)
            ? serialize($toDo)
            : $toDo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getToDoHash()
    {
        return $this->toDoHash;
    }

    /**
     * @param mixed $toDoHash
     *
     * @return self
     */
    public function setToDoHash($toDoHash)
    {
        $this->toDoHash = $toDoHash;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     *
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrlAction()
    {
        return $this->urlAction;
    }

    /**
     * @param mixed $urlAction
     *
     * @return self
     */
    public function setUrlAction($urlAction)
    {
        $this->urlAction = $urlAction;

        return $this;
    }
}
