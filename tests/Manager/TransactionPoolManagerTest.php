<?php

use Inescoin\Entity\TransactionPool;
use Inescoin\Manager\TransactionPoolManager;
use Inescoin\Service\SQLiteService;
use PHPUnit\Framework\TestCase;

class TransactionPoolManagerTest extends TestCase
{
	protected function setUp() :void
    {
        $this->dbName = './cache/db/dbTransactionPoolManagerTest';
        $this->address = 'INES' . rand(100, 999);
        $this->tableName = 'transactionPool';

        $this->transactionPoolManager = new TransactionPoolManager($this->dbName);
        $this->transactionPoolManager
            ->getDbService()
            ->getConnection()
            ->dropTables();

        parent::setUp();
    }

	public function testInstance(): void
	{
		$this->assertInstanceOf(SQLiteService::class, $this->transactionPoolManager->getDbService());
	}

	public function testInsertSelectUpdateDelete() {
        // Check insert
        $rowCount = $this->transactionPoolManager->save($this->getMockTransactionPoolInsert());

        $this->assertSame(1, $rowCount);
    }

    private function getMockTransactionPoolInsert() {
        return new TransactionPool([
			"fee" => 1000000,
			"amount" => 1989000000,
			"fromWalletId" => "0x36c66bC58BF277446ec6343AB926B473ABF93413",
			"bankHash" => "ca9fa87c45cb53ca04d445d4a0f40dca336bdd83d33466bb75279b978873c64e",
			"toDo" => "",
			"toDoHash" => "",
			"transfers" => "W3siYW1vdW50IjoxOTg5MDAwMDAwLCJub25jZSI6IjMxMzAzODM2MzIzNDMxMzYzNDM0MzAzMTMxMzQzMzM5MzYzNjMzMzIzODM5MzgzOSIsIndhbGxldElkIjoiSGVtbSIsImhhc2giOiIxYzQ4YzE0MzdhMTNlZTFhZWFhYmE5ODNiNzM5NTFlYzFjYzllYTUxNTc3YjBkMGI0NDc5YzZjNDU3MmQ3MDQwIn1d",
			"signature" => "3046022100acf8677922d8a1c447b63be868a0e7b772040b12fb4fe129bf200017ec8d99bf02210085c0dbca32f9de2730d44018698e3f1b348f6b69903f5ee07ddb0ed18e75b2a7",
			"publicKey" => "04e7c728c24dbd6e624817fc0ce7fde368241dda92e6ccb21536d208d5b8eaefd30fc7973dc86006f45b680bc3d78b7236160c99cd51902aa93c0ac0720eaae8d7",
			"createdAt" => 1644011439
        ]);
    }
}




