<?php

use Inescoin\Entity\Block;
use Inescoin\Manager\BlockManager;
use Inescoin\Service\SQLiteService;
use PHPUnit\Framework\TestCase;

class BlockManagerTest extends TestCase
{
	protected function setUp() :void
    {
        $this->dbName = './cache/db/dbBlockManagerTest';
        $this->hash = '00000e5ede543c617cc369a3bb102646a146f231a94a53e406135d1ee441137a';
        $this->tableName = 'bank';

        $this->blockManager = new BlockManager($this->dbName);
        $this->blockManager
            ->getDbService()
            ->getConnection()
            ->dropTables();

        parent::setUp();
    }

	public function testInstance(): void
	{
		$this->assertInstanceOf(SQLiteService::class, $this->blockManager->getDbService());
	}

	public function testInsertSelectUpdateDelete() {
        // Check insert
        $rowCount = $this->blockManager->save($this->getMockBlockInsert());

        $this->assertSame(1, $rowCount);

        // Check select
        $result = $this->blockManager->select($this->hash);
        $this->assertEquals([$this->getMockBlockInsert()->_isNotNew()], $result);

        // Check select first
        $resultFirst = $this->blockManager->selectFisrt($this->hash);
        $this->assertEquals($this->getMockBlockInsert()->_isNotNew(), $resultFirst);

        // // Check Update
        $resultFirst->setDataAsArray($this->getMockBlockUpdate()->getDataAsArray());
        $resultUpdate = $this
        	->blockManager
        	->save($resultFirst);

        $this->assertSame(1, $resultUpdate);

        $resultFirst = $this->blockManager->selectFisrt($this->hash);

        $this->assertEquals(
            $this->getMockBlockUpdate()->_isNotNew(),
            $resultFirst
        );

        // Check delete
        $resultDelete = $this->blockManager->delete($this->hash);
        $this->assertSame(1, $resultDelete);

        // Bulk Insert
        $rows = iterator_to_array($this->getMocksBlockInsert());
        $rowCount = $this->blockManager->bulkSave($rows);
        $this->assertSame(1000, $rowCount);

        $row = $this->blockManager->last();
        $this->assertEquals($rows[count($rows)-1]->_isNotNew(), $row);

        $rangeRows = $this->blockManager->range(0, 10);

        $finalRangeExcepted = array_map(function ($row) {
            return $row->_isNotNew();
        }, array_slice($rows, 0, 10));

        $this->assertEquals($finalRangeExcepted, $rangeRows);
    }

    private function getMockBlockInsert() {
        return new Block([
            "difficulty" => 20,
            "nonce" => "625302",
            "height" => 1,
            "cumulativeDifficulty" => 127907397574,
            "previousHash" => "00000cc50f9b497d6098100871a6abc1e77beede4696f76afcc0d2b8ab2f836a",
            "configHash" => "19924913f08605e99f0a3aeb361b9a00",
            "data" => "W3siaGFzaCI6ImFlYTRiODg0YzI2MTFkNGE2NzU0M2YzZDk4MmI3N2M1NWQ1MDc0MmJhZDA5ZjIyYzJiMTUxZWY5YjI2ZmE5YzEiLCJjb25maWdIYXNoIjoiMTk5MjQ5MTNmMDg2MDVlOTlmMGEzYWViMzYxYjlhMDAiLCJiYW5rSGFzaCI6IjNmOGI1NDIyN2UxMjljMDQ1ZDAyNTE3YWUxZDliMzBmODNhNjExMWUyY2NhMzA5ZmNlODUyM2Q1MTJkNzhjOGYiLCJmcm9tIjoiaW5lc2NvaW4iLCJ0cmFuc2ZlcnMiOiJXM3NpZEc4aU9pSXdlRFU1TmpkaE5EQXhOalV3TVRRMk5VTkVPVFV4WVRGbE16azRORVkzTnpKQlprUmxRalV5TURjaUxDSmhiVzkxYm5RaU9qRXdNREF3TURBd01EQXdNQ3dpYm05dVkyVWlPaUl6TXpNd016VXpORE01TXpFek5UTTJNemt6TVRNNE16TXpOak15TXpneVpUTXpNekF6TlRNek5EWXlNamNpTENKb1lYTm9Jam9pWkRaaE5tSXlaV1UwWW1ReE5tTTNZamN6TWpBM05UZzBOelU0WlRBek5URmhNamRqWmpRMVpqVmlPRGMyTkRreU1HRXpPREV4TXpGaVkyWTJZbU01T0NJc0luZGhiR3hsZEVsa0lqb2lJbjFkIiwiYW1vdW50IjoxMDAwMDAwMDAwMDAsImFtb3VudFdpdGhGZWUiOjEwMDAwMDAwMDAwMCwiZmVlIjowLCJjb2luYmFzZSI6dHJ1ZSwiY3JlYXRlZEF0IjoxNTY5MTgzNjI4LCJwdWJsaWNLZXkiOiIiLCJzaWduYXR1cmUiOiIifV0=",
            "hash" => "00000e5ede543c617cc369a3bb102646a146f231a94a53e406135d1ee441137a",
            "createdAt" => 1569183629,
            "merkleRoot" => "35749ec7ec4835a634b5894aa8450a7c79f3f04269621889e731a1045a45cc37",
            "countTotalTransaction" => 0,
            "countTransaction" => 0
        ]);
    }

    private function getMocksBlockInsert() {
        for ($i=1; $i < 1001; $i++) {
         	yield new Block([
	            "difficulty" => 20,
                "nonce" => "625302",
                "height" => $i,
                "cumulativeDifficulty" => 127907397574,
                "previousHash" => "00000cc50f9b497d6098100871a6abc1e77beede4696f76afcc0d2b8ab2f836a",
                "configHash" => "19924913f08605e99f0a3aeb361b9a00",
                "data" => "W3siaGFzaCI6ImFlYTRiODg0YzI2MTFkNGE2NzU0M2YzZDk4MmI3N2M1NWQ1MDc0MmJhZDA5ZjIyYzJiMTUxZWY5YjI2ZmE5YzEiLCJjb25maWdIYXNoIjoiMTk5MjQ5MTNmMDg2MDVlOTlmMGEzYWViMzYxYjlhMDAiLCJiYW5rSGFzaCI6IjNmOGI1NDIyN2UxMjljMDQ1ZDAyNTE3YWUxZDliMzBmODNhNjExMWUyY2NhMzA5ZmNlODUyM2Q1MTJkNzhjOGYiLCJmcm9tIjoiaW5lc2NvaW4iLCJ0cmFuc2ZlcnMiOiJXM3NpZEc4aU9pSXdlRFU1TmpkaE5EQXhOalV3TVRRMk5VTkVPVFV4WVRGbE16azRORVkzTnpKQlprUmxRalV5TURjaUxDSmhiVzkxYm5RaU9qRXdNREF3TURBd01EQXdNQ3dpYm05dVkyVWlPaUl6TXpNd016VXpORE01TXpFek5UTTJNemt6TVRNNE16TXpOak15TXpneVpUTXpNekF6TlRNek5EWXlNamNpTENKb1lYTm9Jam9pWkRaaE5tSXlaV1UwWW1ReE5tTTNZamN6TWpBM05UZzBOelU0WlRBek5URmhNamRqWmpRMVpqVmlPRGMyTkRreU1HRXpPREV4TXpGaVkyWTJZbU01T0NJc0luZGhiR3hsZEVsa0lqb2lJbjFkIiwiYW1vdW50IjoxMDAwMDAwMDAwMDAsImFtb3VudFdpdGhGZWUiOjEwMDAwMDAwMDAwMCwiZmVlIjowLCJjb2luYmFzZSI6dHJ1ZSwiY3JlYXRlZEF0IjoxNTY5MTgzNjI4LCJwdWJsaWNLZXkiOiIiLCJzaWduYXR1cmUiOiIifV0=",
                "hash" => "00000e5ede543c617cc369a3bb102646a146f231a94a53e406135d1ee441137a$i",
                "createdAt" => 1569183629,
                "merkleRoot" => "35749ec7ec4835a634b5894aa8450a7c79f3f04269621889e731a1045a45cc37",
                "countTotalTransaction" => 0,
                "countTransaction" => 0
	        ]);
        }
    }

    private function getMockBlockUpdate() {
        return new Block([
            "difficulty" => 20,
            "nonce" => "625302",
            "height" => 1,
            "cumulativeDifficulty" => 127907397574,
            "previousHash" => "00000cc50f9b497d6098100871a6abc1e77beede4696f76afcc0d2b8ab2f836a",
            "configHash" => "19924913f08605e99f0a3aeb361b9a00",
            "data" => "W3siaGFzaCI6ImFlYTRiODg0YzI2MTFkNGE2NzU0M2YzZDk4MmI3N2M1NWQ1MDc0MmJhZDA5ZjIyYzJiMTUxZWY5YjI2ZmE5YzEiLCJjb25maWdIYXNoIjoiMTk5MjQ5MTNmMDg2MDVlOTlmMGEzYWViMzYxYjlhMDAiLCJiYW5rSGFzaCI6IjNmOGI1NDIyN2UxMjljMDQ1ZDAyNTE3YWUxZDliMzBmODNhNjExMWUyY2NhMzA5ZmNlODUyM2Q1MTJkNzhjOGYiLCJmcm9tIjoiaW5lc2NvaW4iLCJ0cmFuc2ZlcnMiOiJXM3NpZEc4aU9pSXdlRFU1TmpkaE5EQXhOalV3TVRRMk5VTkVPVFV4WVRGbE16azRORVkzTnpKQlprUmxRalV5TURjaUxDSmhiVzkxYm5RaU9qRXdNREF3TURBd01EQXdNQ3dpYm05dVkyVWlPaUl6TXpNd016VXpORE01TXpFek5UTTJNemt6TVRNNE16TXpOak15TXpneVpUTXpNekF6TlRNek5EWXlNamNpTENKb1lYTm9Jam9pWkRaaE5tSXlaV1UwWW1ReE5tTTNZamN6TWpBM05UZzBOelU0WlRBek5URmhNamRqWmpRMVpqVmlPRGMyTkRreU1HRXpPREV4TXpGaVkyWTJZbU01T0NJc0luZGhiR3hsZEVsa0lqb2lJbjFkIiwiYW1vdW50IjoxMDAwMDAwMDAwMDAsImFtb3VudFdpdGhGZWUiOjEwMDAwMDAwMDAwMCwiZmVlIjowLCJjb2luYmFzZSI6dHJ1ZSwiY3JlYXRlZEF0IjoxNTY5MTgzNjI4LCJwdWJsaWNLZXkiOiIiLCJzaWduYXR1cmUiOiIifV0=",
            "hash" => "00000e5ede543c617cc369a3bb102646a146f231a94a53e406135d1ee441137a",
            "createdAt" => 1569183629,
            "merkleRoot" => "35749ec7ec4835a634b5894aa8450a7c79f3f04269621889e731a1045a45cc37",
            "countTotalTransaction" => 0,
            "countTransaction" => 0
        ]);
    }
}
