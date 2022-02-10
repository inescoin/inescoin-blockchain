<?php
use PHPUnit\Framework\TestCase;

use Inescoin\DB\Schema;
use Inescoin\DB\SQLiteConnection;
use Inescoin\Service\SQLiteService;

class SQLiteServiceTest extends TestCase
{
    private $DBService;

    private $address;

    private $dbName;

    private $tableName;

    protected function setUp() :void
    {
        $this->dbName = './cache/db/dbSQLiteServiceTest';
        $this->address = 'INES' . rand(100, 999);
        $this->tableName = 'bank';

        $this->DBService = SQLiteService::getInstance($this->dbName);
        $this->DBService->getConnection()->dropTables();
        parent::setUp();
    }

    public function testInstance() {
        $this->assertInstanceOf(SQLiteService::class, $this->DBService);
    }

    public function testCreateTables() {
        $this->DBService->getConnection()->createTables();

        $this->assertSame($this->DBService->getConnection()->getTableNames(), array_keys(Schema::DATABASE));
    }

    public function testInsertSelectUpdateDelete() {
        // Check insert
        $rowCount = $this->DBService->insert('bank', $this->getMockBankInsert());

        $this->assertSame(1, $rowCount);

        // Check select
        $result = $this->DBService->select($this->address, 'bank', 'address', 0, 10, 'address');
        $this->assertEquals([$this->getMockBankInsert()], $result);

        // Check select first
        $resultFirst = $this->DBService->selectFisrt($this->address, 'bank', 'address');
        $this->assertEquals($this->getMockBankInsert(), $resultFirst);

        // Check Update
        $this->DBService->update($this->address, 'bank', $this->getMockBankUpdate(), 'address');

        $resultUpdate = $this->DBService->selectFisrt($this->address, 'bank', 'address');

        $this->assertEquals(
            array_merge($this->getMockBankUpdate(), ['address' => $this->address]),
            array_merge($resultUpdate, ['address' => $this->address])
        );

        // Check delete
        $resultDelete = $this->DBService->delete($this->address, 'bank', 'address');
        $this->assertSame(1, $resultDelete);

        // Check bulk
        $rows = iterator_to_array($this->getMockBlockInsert());
        $rowCount = $this->DBService->bulk('block', $rows);
        $this->assertSame(999, $rowCount);

        $row = $this->DBService->last('block');
        $this->assertEquals($rows[count($rows)-1], $row);

        $rangeRows = $this->DBService->range('block', 0, 10, 'height', 'ASC');
        $this->assertEquals(array_slice($rows, 0, 10), $rangeRows);
    }

    private function getMockBankInsert() {
        return [
            'amount' => 10000.00009990000001,
            'height' => 12,
            'address' => $this->address,
            'hash' => 'DLKQSMKDMQSLKDMQSKMDSKm',
            'transactionHash' => 'SQMLKdmqlskdmqskdmqksmdkm',
            'transferHash' => 'qsmlkdmqlskdmqskdmqskdmqslkdm'
        ];
    }

    private function getMockBankUpdate() {
        return [
            'amount' => 1000000,
            'height' => 12,
            'hash' => 'UPDATEBBBBBBBBBBBBBBBBB',
            'transactionHash' => 'UPDATEBBBBBBBBBBBBBBBBB',
            'transferHash' => 'UPDATEBBBBBBBBBBBBBBBBB'
        ];
    }

    private function getMockBlockInsert() {
        for ($i = 1; $i < 1000; $i++) {
            yield [
                "difficulty" => 20,
                "nonce" => '625302',
                "height" => $i,
                "cumulativeDifficulty" => 127907397574,
                "previousHash" => "00000cc50f9b497d6098100871a6abc1e77beede4696f76afcc0d2b8ab2f836a",
                "configHash" => "19924913f08605e99f0a3aeb361b9a00",
                "data" => "W3siaGFzaCI6ImFlYTRiODg0YzI2MTFkNGE2NzU0M2YzZDk4MmI3N2M1NWQ1MDc0MmJhZDA5ZjIyYzJiMTUxZWY5YjI2ZmE5YzEiLCJjb25maWdIYXNoIjoiMTk5MjQ5MTNmMDg2MDVlOTlmMGEzYWViMzYxYjlhMDAiLCJiYW5rSGFzaCI6IjNmOGI1NDIyN2UxMjljMDQ1ZDAyNTE3YWUxZDliMzBmODNhNjExMWUyY2NhMzA5ZmNlODUyM2Q1MTJkNzhjOGYiLCJmcm9tIjoiaW5lc2NvaW4iLCJ0cmFuc2ZlcnMiOiJXM3NpZEc4aU9pSXdlRFU1TmpkaE5EQXhOalV3TVRRMk5VTkVPVFV4WVRGbE16azRORVkzTnpKQlprUmxRalV5TURjaUxDSmhiVzkxYm5RaU9qRXdNREF3TURBd01EQXdNQ3dpYm05dVkyVWlPaUl6TXpNd016VXpORE01TXpFek5UTTJNemt6TVRNNE16TXpOak15TXpneVpUTXpNekF6TlRNek5EWXlNamNpTENKb1lYTm9Jam9pWkRaaE5tSXlaV1UwWW1ReE5tTTNZamN6TWpBM05UZzBOelU0WlRBek5URmhNamRqWmpRMVpqVmlPRGMyTkRreU1HRXpPREV4TXpGaVkyWTJZbU01T0NJc0luZGhiR3hsZEVsa0lqb2lJbjFkIiwiYW1vdW50IjoxMDAwMDAwMDAwMDAsImFtb3VudFdpdGhGZWUiOjEwMDAwMDAwMDAwMCwiZmVlIjowLCJjb2luYmFzZSI6dHJ1ZSwiY3JlYXRlZEF0IjoxNTY5MTgzNjI4LCJwdWJsaWNLZXkiOiIiLCJzaWduYXR1cmUiOiIifV0=",
                "hash" => "00000e5ede543c617cc369a3bb102646a146f231a94a53e406135d1ee441137$i",
                "createdAt" => 1569183629,
                "merkleRoot" => "35749ec7ec4835a634b5894aa8450a7c79f3f04269621889e731a1045a45cc37",
                "countTotalTransaction" => 0,
                "countTransaction" => 0
            ];
        }
    }
}
