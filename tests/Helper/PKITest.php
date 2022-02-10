<?php

use Inescoin\EC\AddressValidator;
use Inescoin\Helper\PKI;
use PHPUnit\Framework\TestCase;


class PKITest extends TestCase
{

	public function testWallet()
	{
		$wallet = PKI::newEcKeys();

		$valid = AddressValidator::isValid($wallet['address'], $wallet['publicKey']);

		$this->assertArrayHasKey('publicKey', $wallet);
		$this->assertArrayHasKey('privateKey', $wallet);
		$this->assertArrayHasKey('address', $wallet);
		$this->assertSame(AddressValidator::ADDRESS_VALID, $valid);
	}
}
