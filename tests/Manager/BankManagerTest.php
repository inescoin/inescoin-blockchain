<?php

use Inescoin\Entity\Bank;
use Inescoin\Manager\BankManager;
use Inescoin\Service\SQLiteService;
use PHPUnit\Framework\TestCase;

class BankManagerTest extends TestCase
{
	protected function setUp() :void
    {
        $this->dbName = './cache/db/dbBankManagerTest';
        $this->address = 'INES' . rand(100, 999);
        $this->tableName = 'bank';

        $this->bankManager = new BankManager($this->dbName);
        $this->bankManager
            ->getDbService()
            ->getConnection()
            ->dropTables();

        parent::setUp();
    }

	public function testInstance(): void
	{
		$this->assertInstanceOf(SQLiteService::class, $this->bankManager->getDbService());
	}

	public function testInsertSelectUpdateDelete() {
        // Check insert
        $rowCount = $this->bankManager->save($this->getMockBankInsert());

        $this->assertSame(1, $rowCount);

        // Check select
        $result = $this->bankManager->select($this->address);
        $this->assertEquals([$this->getMockBankInsert()->_isNotNew()], $result);

        // Check select first
        $resultFirst = $this->bankManager->selectFisrt($this->address);
        $this->assertEquals($this->getMockBankInsert()->_isNotNew(), $resultFirst);

        // // Check Update
        $resultFirst->setDataAsArray($this->getMockBankUpdate()->getDataAsArray());
        $resultUpdate = $this
        	->bankManager
        	->save($resultFirst);

        $this->assertSame(1, $resultUpdate);

        $resultFirst = $this->bankManager->selectFisrt($this->address);

        $this->assertEquals(
            $this->getMockBankUpdate()->_isNotNew(),
            $resultFirst
        );

        // Check delete
        $resultDelete = $this->bankManager->delete($this->address);
        $this->assertSame(1, $resultDelete);

        // Bulk Insert
        $rowCount = $this->bankManager->bulkSave(iterator_to_array($this->getMocksBankInsert()));
        $this->assertSame(1000, $rowCount);
    }

    public function testGetAddressBalances()
    {
        // Check insert
        $rowCount = $this->bankManager->save($this->getMockBankInsert());

        $this->assertSame(1, $rowCount);

        $rows = $this->bankManager->getAddressBalances([
            'A',
            'B',
            'C'
        ]);

        $this->assertEquals([], $rows);

        $rowsTwo = $this->bankManager->getAddressBalances($this->address);

        $this->assertEquals([
           $this->getMockBankInsert()->getAddress() => $this->getMockBankInsert()->_isNotNew()
        ], $rowsTwo);
    }

    private function getMockBankInsert() {
        return new Bank([
            'amount' => 1000001,
            'height' => 12,
            'address' => $this->address,
            'hash' => 'DLKQSMKDMQSLKDMQSKMDSKm',
            'transactionHash' => 'SQMLKdmqlskdmqskdmqksmdkm',
            'transferHash' => 'qsmlkdmqlskdmqskdmqskdmqslkdm'
        ]);
    }

    private function getMocksBankInsert() {
        for ($i=0; $i < 1000; $i++) {
         	yield new Bank([
	            'amount' => 100001,
	            'height' => 12,
	            'address' => $this->address . $i,
	            'hash' => 'DLKQSMKDMQSLKDMQSKMDSKv'.$i,
	            'transactionHash' => 'SQMLKdmqlskdmqskdmqksmdkm',
	            'transferHash' => 'qsmlkdmqlskdmqskdmqskdmqslkdm'
	        ]);
        }
    }

    private function getMockBankUpdate() {
        return new Bank([
            'amount' => 1000000,
            'height' => 12,
            'address' => $this->address,
            'hash' => 'UPDATEBBBBBBBBBBBBBBBBB',
            'transactionHash' => 'UPDATEBBBBBBBBBBBBBBBBB',
            'transferHash' => 'UPDATEBBBBBBBBBBBBBBBBB'
        ]);
    }
}
