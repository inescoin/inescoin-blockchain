<?php

// Copyright 2019-2022 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\Helper;

class MerkleTree
{
	static function getRoot($blockTransactions)
	{
		$merkleTree = self::getRootTree($blockTransactions);
		return $merkleTree[count($merkleTree) - 1][0];
	}

	static function getRootTree($blockTransactions)
	{
		if (!is_array($blockTransactions)) {
			return [];
		}

		$transactions = array_map(function($transaction) {
			return bin2hex(hash('SHA256', hash('SHA256', $transaction['hash'], true), true));;
		}, $blockTransactions);

		$tree[0] = $transactions;

		if (count($tree[0]) === 1) {
			return $tree;
		}

		$i = 1;

		while (count($tree[$i - 1]) !== 1) {
			// var_dump('Level: ' . $i);
			$txs = $tree[$i - 1];
			$totalTxs = count($txs);

			$isOddTxs = $totalTxs % 2 !== 0;

			for ($y=0; $y < $totalTxs; $y++) {
				// var_dump('Line: ' . $y);
				if ($y !== 0 && ($y + 1) % 2 === 0) {
					$pair = $txs[$y - 1] . $txs[$y];
					$tree[$i][] = bin2hex(hash('SHA256', hash('SHA256', $pair, true), true));
				}

				if ($isOddTxs && $y === $totalTxs - 1) {
					$pair = $txs[$y] . $txs[$y];
					$tree[$i][] = bin2hex(hash('SHA256', hash('SHA256', $pair, true), true));
				}
			}

			$i++;
		}

		return $tree;
	}
}
