<?php

use Inescoin\BlockchainConfig;
use Inescoin\Service\BlockchainService;
use PHPUnit\Framework\TestCase;

class BlockchainServiceTest extends TestCase
{
	public function testInstance()
	{
		$blockchain = BlockchainService::getInstance();
		$this->assertInstanceOf(BlockchainService::class, $blockchain);
		$this->assertSame(BlockchainConfig::NAME, $blockchain->getPrefix());
		$this->assertSame(str_replace('tests/Service/', '', __DIR__ . '/src/Service/../../'), $blockchain->getPathToBlockDirectory());

		$reflection = new ReflectionClass($blockchain);
		$instance = $reflection->getProperty('instance');
		$instance->setAccessible(true); // now we can modify that :)
		$instance->setValue(null, null); // instance is gone
		$instance->setAccessible(false); // clean up

		$blockchain = BlockchainService::getInstance('moon', '/opt/');
		$this->assertSame('moon', $blockchain->getPrefix());
		$this->assertSame('/opt/', $blockchain->getPathToBlockDirectory());
	}

	public function testClearMemoryPool()
	{
		$blockchain = BlockchainService::getInstance();

		// $blockchain->clearMemoryPool();
	}
}
