<?php

use Inescoin\BlockchainConfig;
use Inescoin\Entity\Block;
use Inescoin\Helper\BlockHelper;
use Inescoin\Manager\BlockchainManager;
use Inescoin\Model\Block as BlockModel;
use Monolog\Test\TestCase;

class BlockHelperTest extends TestCase
{
	protected function setUp() :void
    {
        $this->dbName = './cache/db/dbBlockHelperTest';
        $this->hash = '00000e5ede543c617cc369a3bb102646a146f231a94a53e406135d1ee441137a';
        $this->tableName = 'bank';

        $this->blockchainManager = BlockchainManager::getInstance($this->dbName);
        $this->blockchainManager->dropTables();

        parent::setUp();
    }

	public function testExtractBlocks()
	{
		// $rownCount = BlockHelper::extractBlock(iterator_to_array($this->getBlocksMock()));

		// $this->assertSame(21, $rownCount);

		// $this->assertTrue($this->blockchainManager->getDomain()->exists('dsfdfsfsdfsfsd', 'url'));
		// $this->assertTrue($this->blockchainManager->getDomain()->exists('aaaaaaaaa', 'url'));

		// $this->assertSame(21, $this->blockchainManager->getBlock()->count());
		// $this->assertSame(31, $this->blockchainManager->getTransaction()->count());
		// $this->assertSame(48, $this->blockchainManager->getTransfer()->count());
		// $this->assertSame(2, $this->blockchainManager->getDomain()->count());
		// $this->assertSame(1, $this->blockchainManager->getWebsite()->count());
		// $this->assertSame(5, $this->blockchainManager->getBank()->count());
	}

	public function testGenerateGenesisBlock()
	{
		$block = BlockHelper::generateGenesisBlock('inesc', false);

		$this->assertInstanceOf(BlockModel::class, $block);
		$this->assertSame($block->getConfigHash(), BlockchainConfig::getHash());
	}

	private function getBlocksMock()
	{
		$blocks = json_decode(file_get_contents(__DIR__ . '/../blocks.json'));

		foreach ($blocks as $block) {
			yield new Block((array) $block);
		}
	}
}
