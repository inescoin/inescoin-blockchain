<?php

use Inescoin\Entity\BlockTemp;
use Inescoin\Manager\BlockTempManager;
use Inescoin\Service\SQLiteService;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class BlockTempManagerTest extends TestCase
{
	protected function setUp() :void
    {
        $this->dbName = './cache/db/dbBlockTempManagerTest';
        $this->hash = '00000e5ede543c617cc369a3bb102646a146f231a94a53e406135d1ee441137a';
        $this->tableName = 'bank';

        $this->blockTempManager = new BlockTempManager($this->dbName);
        $this->blockTempManager
            ->getDbService()
            ->getConnection()
            ->dropTables();

        parent::setUp();
    }

	public function testInstance(): void
	{
		$this->assertInstanceOf(SQLiteService::class, $this->blockTempManager->getDbService());
	}

	public function testInsertSelectUpdateDelete() {
        // Check insert
        $rowCount = $this->blockTempManager->save($this->getMockBlockTempInsert());

        $this->assertSame(1, $rowCount);

        // Check select
        $result = $this->blockTempManager->select($this->hash);
        $this->assertEquals([$this->getMockBlockTempInsert()->_isNotNew()], $result);

        // Check select first
        $resultFirst = $this->blockTempManager->selectFisrt($this->hash);
        $this->assertEquals($this->getMockBlockTempInsert()->_isNotNew(), $resultFirst);

        // // Check Update
        $resultFirst->setDataAsArray($this->getMockBlockTempUpdate()->getDataAsArray());
        $resultUpdate = $this
        	->blockTempManager
        	->save($resultFirst);

        $this->assertSame(1, $resultUpdate);

        $resultFirst = $this->blockTempManager->selectFisrt($this->hash);

        $this->assertEquals(
            $this->getMockBlockTempUpdate()->_isNotNew(),
            $resultFirst
        );

        // Check delete
        $resultDelete = $this->blockTempManager->delete($this->hash);
        $this->assertSame(1, $resultDelete);

        // Bulk Insert
        $rowCount = $this->blockTempManager->bulkSave(iterator_to_array($this->getMocksBlockTempInsert()));
        $this->assertSame(1000, $rowCount);
    }

    private function getMockBlockTempInsert() {
        return new BlockTemp([
            "difficulty" => 20,
            "nonce" => 625302,
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

    private function getMocksBlockTempInsert() {
        for ($i=1; $i < 1001; $i++) {
         	yield new BlockTemp([
	            "difficulty" => 20,
                "nonce" => 625302,
                "height" => 95027,
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

    private function getMockBlockTempUpdate() {
        return new BlockTemp([
            "difficulty" => 20,
            "nonce" => 625302,
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
