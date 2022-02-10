<?php

namespace Inescoin\Entity;

class Block extends AbstractEntity
{
	protected $countTotalTransaction; // 'countTotalTransaction' => 'integer',
	protected $configHash; // 'configHash' => 'text',
	protected $merkleRoot; // 'merkleRoot' => 'text',
	protected $countTransaction; // 'countTransaction' => 'integer',
	protected $createdAt; // 'createdAt' => 'integer',
	protected $data; // 'data' => 'text',
	//protected $previousCumulativeDifficulty; // 'previousCumulativeDifficulty' => 'integer',
	protected $cumulativeDifficulty; // 'cumulativeDifficulty' => 'integer',
	protected $difficulty; // 'difficulty' => 'integer',
	protected $hash; // 'hash' => 'text PRIMARY KEY',
	protected $height; // 'height' => 'integer',
	//protected $name; // 'name' => 'text',
	protected $nonce; // 'nonce' => 'text',
	protected $previousHash; // 'previousHash' => 'text',
	//protected $symbol; // 'symbol' => 'text',
	//protected $status; // 'status' => 'text'

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * @return mixed
     */
    public function getCountTotalTransaction()
    {
        return $this->countTotalTransaction;
    }

    /**
     * @param mixed $countTotalTransaction
     *
     * @return self
     */
    public function setCountTotalTransaction($countTotalTransaction)
    {
        $this->countTotalTransaction = $countTotalTransaction;

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
    public function getMerkleRoot()
    {
        return $this->merkleRoot;
    }

    /**
     * @param mixed $merkleRoot
     *
     * @return self
     */
    public function setMerkleRoot($merkleRoot)
    {
        $this->merkleRoot = $merkleRoot;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountTransaction()
    {
        return $this->countTransaction;
    }

    /**
     * @param mixed $countTransaction
     *
     * @return self
     */
    public function setCountTransaction($countTransaction)
    {
        $this->countTransaction = $countTransaction;

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
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     *
     * @return self
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCumulativeDifficulty()
    {
        return $this->cumulativeDifficulty;
    }

    /**
     * @param mixed $cumulativeDifficulty
     *
     * @return self
     */
    public function setCumulativeDifficulty($cumulativeDifficulty)
    {
        $this->cumulativeDifficulty = $cumulativeDifficulty;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDifficulty()
    {
        return $this->difficulty;
    }

    /**
     * @param mixed $difficulty
     *
     * @return self
     */
    public function setDifficulty($difficulty)
    {
        $this->difficulty = $difficulty;

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
    public function getPreviousHash()
    {
        return $this->previousHash;
    }

    /**
     * @param mixed $previousHash
     *
     * @return self
     */
    public function setPreviousHash($previousHash)
    {
        $this->previousHash = $previousHash;

        return $this;
    }
}
