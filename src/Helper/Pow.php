<?php

// Copyright 2019-2022 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\Helper;

class Pow {

	private static $zero = '0';

	public static function hash($message, $algo = 'sha256') {
		return hash($algo, $message);
	}

	public static function findNonce($message, $algo = 'sha256') {
		$nonce = 0;
		while (!self::isValidNonce($message, $nonce, $algo)) {
			++$nonce;
		}

		return $nonce;
	}

	/**
	 * @param  string  $message
	 * @param  int     $nonce
	 * @param  string  $algo
	 *
	 * @return boolean
	 */
	public static function isValidNonce(string $message, int $nonce, string $algo = 'sha256') {
		return 0 === strpos(self::hash($message . $nonce, $algo), self::$zero);
	}
}
