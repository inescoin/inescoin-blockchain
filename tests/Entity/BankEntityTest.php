<?php

use Inescoin\Entity\Bank;
use PHPUnit\Framework\TestCase;

class BankEntityTest extends TestCase
{
	public function testInstance()
	{
		$this->assertInstanceOf(Bank::class, new Bank());
	}

	public function testDataArray() {
		$bank = new Bank($this->getMockBank());
		$this->assertEquals($this->getMockBank(), $bank->getDataAsArray());;
	}

	private function getMockBank() {
        return [
            'amount' => 10000.00009990000001,
            'height' => 12,
            'address' => 'fsdfsfsdfsdf',
            'hash' => 'DLKQSMKDMQSLKDMQSKMDSKm',
            'transactionHash' => 'SQMLKdmqlskdmqskdmqksmdkm',
            'transferHash' => 'qsmlkdmqlskdmqskdmqskdmqslkdm'
        ];
    }
}
