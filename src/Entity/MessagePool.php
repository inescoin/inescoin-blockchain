<?php

namespace Inescoin\Entity;

class MessagePool extends AbstractEntity
{
	protected $createdAt; // 'createdAt' => 'integer',
	protected $fromWalletId; // 'fromWalletId' => 'text',
	protected $toWalletId; // 'toWalletId' => 'text',
	protected $hash; // 'hash' => 'text PRIMARY KEY',
	protected $publicKey; // 'publicKey' => 'text',
	protected $signature; // 'signature' => 'text',
	protected $message; // 'message' => 'text'

    public function __construct(array $data = [])
    {
        parent::__construct($data);
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
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     *
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }
}
