<?php

namespace Inescoin\Manager;

use Inescoin\Entity\Transfer;
use Inescoin\Service\SQLiteService;

class TransferManager extends AbstractManager
{
	protected $tableName = 'transfer';
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
	 * @param      mixed       $id
	 * @param      string       $idName
	 * @param      null|string  $orderBy
	 * @param      int          $offset
	 * @param      int          $limit
	 *
	 * @return     Transfer[]
	 */
	public function select(mixed $id, string $idName = self::PRIMARY_KEY, int $offset = 0, int $limit = 10, ?string $orderBy = self::PRIMARY_KEY, ?string $sortBy = 'ASC'): array
	{
		$transfersResult =  parent::select($id, $idName, $offset, $limit, $orderBy, $sortBy);

		$transfers = [];

		if (!empty($transfersResult)) {
			foreach ($transfersResult as $transfer) {
				$transfers[] = (new Transfer($transfer))->_isNotNew();
			}
		}

		return $transfers;
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
			ORDER BY height DESC
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
	 * @param      mixed          $id
	 * @param      string          $idName
	 *
	 * @return     Transfer|bool|null
	 */
	public function selectFisrt(mixed $id, string $idName = self::PRIMARY_KEY): ?Transfer
	{
		$transferResult =  parent::selectFisrt($id, $idName);

		return $transferResult
			? (new Transfer($transferResult))->_isNotNew()
			: null ;
	}

	/**
	 * @param      mixed          $id
	 * @param      string          $idName
	 *
	 * @return     Transfer|bool|null
	 */
	public function selectFisrtAsArray(mixed $id, string $idName = self::PRIMARY_KEY): ?array
	{
		return parent::selectFisrt($id, $idName);
	}

	/**
	 * @param      Transfer[]   $transfers
	 *
	 * @return     int
	 */
	public function bulkSave(array $transfers): int
	{
		$data = [];

		foreach ($transfers as $transfer) {
			$transfer = !is_array($transfer)
				? $transfer
				: new Transfer($transfer);

			$data[] = $transfer->getDataAsArray();
		}

		return $this->bulk($data);
	}

	/**
	 * @param      Transfer  $transfer
	 *
	 * @return     int
	 */
	public function save(Transfer $transfer): int
	{
		$transferArray = $transfer->getDataAsArray();

		return $transfer->_getIsNew()
			? $this->insert($transferArray)
			: $this->update(
				$transferArray[self::PRIMARY_KEY],
				$transfer->getDataAsArray(), self::PRIMARY_KEY
			);
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
	 * @param      mixed  $id
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
	 * @param      mixed  $id
	 * @param      string  $idName
	 *
	 * @return     int
	 */
	public function delete(mixed $id, string $idName = self::PRIMARY_KEY): int
	{
		return parent::delete($id, $idName);
	}
}
