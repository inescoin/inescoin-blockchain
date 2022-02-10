<?php

namespace Inescoin\Entity;

class Website extends AbstractEntity
{
	protected $hash; // 'hash' => 'text',
	protected $url; // 'url' => 'text',
	protected $body; // 'body' => 'text',
	protected $ownerAddress; // 'ownerAddress' => 'text',
	protected $ownerPublicKey; // 'ownerPublicKey' => 'text',
	protected $signature; // 'signature' => 'text',
	protected $height; // 'height' => 'integer PRIMARY KEY',
	protected $transactionHash; // 'transactionHash' => 'text',

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
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     *
     * @return self
     */
    public function setBody($body)
    {
        $this->body = $body;

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
}
