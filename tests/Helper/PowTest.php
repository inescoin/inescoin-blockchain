<?php

use PHPUnit\Framework\TestCase;

use Inescoin\Helper\Pow;

class PowTest extends TestCase
{
	public function testHash() {
		$this->assertSame('9e78b43ea00edcac8299e0cc8df7f6f913078171335f733a21d5d911b6999132', Pow::hash('moon', 'sha256')) ;
	}

	public function testFindNonce() {
		$this->assertSame(63, Pow::findNonce('moon', 'sha256'));
	}

	public function testValidNonce() {
		$this->assertTrue(Pow::isValidNonce('moon', 63, 'sha256'));
	}
}
