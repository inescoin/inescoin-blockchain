<?php

namespace Inescoin\Manager;

use Inescoin\Entity\TransferPool;
use Inescoin\Service\SQLiteService;

class TransferPoolManager extends AbstractManager
{
	protected $tableName = 'transferPool';
	const PRIMARY_KEY = 'address';

	public function __construct($database = null) {
		parent::__construct($database);
	}

	/**
	 * @param      string  $id
	 * @param      string  $idName
	 *
	 * @return     bool
	 */
	public function exists(string $id, string $idName = self::PRIMARY_KEY): bool
	{
		return parent::exists($id, $idName);
	}

	/**
	 * @param      string  $sql    Query to execute
	 *
	 * @return     array
	 */
	public function query(string $sql = ''): array
	{
		return parent::query($sql);
	}

	/**
	 * @return     int
	 */
	public function count(): int
	{
		return parent::count();
	}

	/**
	 * @param      string       $id
	 * @param      string       $idName
	 * @param      null|string  $orderBy
	 * @param      int          $offset
	 * @param      int          $limit
	 *
	 * @return     TransferPool[]
	 */
	public function select(mixed $id, string $idName = self::PRIMARY_KEY, int $offset = 0, int $limit = 10, ?string $orderBy = self::PRIMARY_KEY, ?string $sortBy = 'ASC'): array
	{
		$peersResult =  parent::select($id, $idName, $offset, $limit, $orderBy, $sortBy);

		$peers = [];

		if (!empty($peersResult)) {
			foreach ($peersResult as $peer) {
				$peers[] = (new TransferPool($peer))->_isNotNew();
			}
		}

		return $peers;
	}

	/**
	 * @param      string          $id
	 * @param      string          $idName
	 *
	 * @return     TransferPool|bool|null
	 */
	public function selectFisrt(mixed $id, string $idName = self::PRIMARY_KEY): ?TransferPool
	{
		$peerResult =  parent::selectFisrt($id, $idName);

		return $peerResult
			? (new TransferPool($peerResult))->_isNotNew()
			: null ;
	}

	/**
	 * @param      TransferPool[]   $peers
	 *
	 * @return     int
	 */
	public function bulkSave(array $peers): int
	{
		$data = [];

		foreach ($peers as $peer) {
			$data[] = $peer->getDataAsArray();
		}

		return $this->bulk($data);
	}

	/**
	 * @param      TransferPool  $peer
	 *
	 * @return     int
	 */
	public function save(TransferPool $peer): int
	{
		$peerArray = $peer->getDataAsArray();

		return $peer->_getIsNew()
			? $this->insert($peerArray)
			: $this->update(
				$peerArray[self::PRIMARY_KEY],
				$peer->getDataAsArray(), self::PRIMARY_KEY
			);
	}

	/**
	 * @param      mixed        $id
	 * @param      int  		$page
	 * @param      int          $size
	 *
	 * @return     Transfer[]
	 */
	public function selectHistory(mixed $id, int $page = 1, int $size = 500): array
	{
		$from = 0;
		if ($page > 1) {
			$from = ((int) $page * $size - 1) - $size;
		}

		$sql = "
		SELECT *
			FROM {$this->tableName}
			WHERE fromWalletId LIKE '$id' OR toWalletId LIKE '$id'
			ORDER BY createdAt DESC
			LIMIT $from, $size;
		";

		//echo $sql . PHP_EOL;

		$transfersResult = $this->query($sql);

		$transfers = [];

		if (!empty($transfersResult)) {
			foreach($transfersResult as $transfer) {
				$transfers[] = $transfer;
			}
		}

		return $transfers;
	}

	/**
	 * @param      mixed        $id
	 *
	 * @return     int
	 */
	public function countHistory(mixed $id): int
	{
		$sql = "
		SELECT count(*) as counter
			FROM {$this->tableName}
			WHERE fromWalletId LIKE '$id' OR toWalletId LIKE '$id';
		";

		//echo $sql . PHP_EOL;

		$countResult = $this->query($sql);

		return $countResult[0]['counter'];
	}

	/**
	 * @param      array  $data
	 *
	 * @return     int
	 */
	protected function insert(array $data): int
	{
		return parent::insert($data);
	}

	/**
	 * @param      array  $data
	 *
	 * @return     int
	 */
	protected function bulk(array $data): int
	{
		return parent::bulk($data);
	}

	/**
	 * @param      string  $id
	 * @param      array   $data
	 * @param      string  $idName
	 *
	 * @return     int
	 */
	protected function update(mixed $id, array $data, string $idName = self::PRIMARY_KEY): int
	{
		if (isset($data[self::PRIMARY_KEY])) {
			unset($data[self::PRIMARY_KEY]);
		}

		return parent::update($id, $data, $idName);
	}

	/**
	 * @param      string  $id
	 * @param      string  $idName
	 *
	 * @return     int
	 */
	public function delete(mixed $id, string $idName = self::PRIMARY_KEY): int
	{
		return parent::delete($id, $idName);
	}

	/**
	 * @param      mixed       $transactions
	 *
	 * @return     int
	 */
	public function deleteOldTransactions(mixed $transactions): int
	{
		if (is_string($transactions)) {
			$transactions = [$transactions];
		}

		$orQuery = implode("' OR transactionHash = '", $transactions);

		$sql = "
		DELETE FROM {$this->tableName}
			WHERE transactionHash = '$orQuery';
		";
		// echo $sql . PHP_EOL;

		return $this->queryLite($sql);
	}
}
