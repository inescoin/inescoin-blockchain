<?php

namespace Inescoin\Manager;

use Inescoin\Entity\TransactionPool as Transaction;
use Inescoin\Model\Transaction as TransactionModel;
use Inescoin\Service\SQLiteService;

class TransactionPoolManager extends AbstractManager
{
	protected $tableName = 'transactionPool';
	const PRIMARY_KEY = 'fromWalletId';

	public function __construct($database = null) {
		parent::__construct($database);
	}

	public function range(int $offset = 0, int $limit = 10, string $orderBy = 'height', string $sortBy = 'asc', string $where = '')
	{
		$transactionPoolsResult = parent::range($offset, $limit, $orderBy, $sortBy, $where);

		$transactionPools = [];

		if (!empty($transactionPoolsResult)) {
			foreach ($transactionPoolsResult as $transactionPool) {
				$transactionPools[] = (new Transaction($transactionPool))->_isNotNew();
			}
		}

		return $transactionPools;
	}

	public function rangeAsArray(int $offset = 0, int $limit = 10, string $orderBy = 'fee', string $sortBy = 'desc', string $where = '')
	{
		$transactionPoolsResult = parent::range($offset, $limit, $orderBy, $sortBy, $where);

		$transactionPools = [];

		if (!empty($transactionPoolsResult)) {
			foreach ($transactionPoolsResult as $transactionPool) {
				$transactionPool['transfers'] = (array) json_decode(base64_decode($transactionPool['transfers']));
				$transactionPools[] = $transactionPool;
			}
		}

		return $transactionPools;
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

	public function last(int $limit = 1, string $orderBy = 'height', string $sortBy = 'desc')
	{
		$transactionPool =  parent::last($limit, $orderBy, $sortBy);

		return $transactionPool
			? (new Transaction($transactionPool))->_isNotNew()
			: null ;
	}

	/**
	 * @param      string       $id
	 * @param      string       $idName
	 * @param      null|string  $orderBy
	 * @param      int          $offset
	 * @param      int          $limit
	 *
	 * @return     Transaction[]
	 */
	public function select(mixed $id, string $idName = self::PRIMARY_KEY, int $offset = 0, int $limit = 10, ?string $orderBy = self::PRIMARY_KEY, ?string $sortBy = 'ASC'): array
	{
		$transactionPoolsResult =  parent::select($id, $idName, $offset, $limit, $orderBy, $sortBy);

		$transactionPools = [];

		if (!empty($transactionPoolsResult)) {
			foreach ($transactionPoolsResult as $transactionPool) {
				$transactionPools[] = (new Transaction($transactionPool))->_isNotNew();
			}
		}

		return $transactionPools;
	}

	/**
	 * @param      string          $id
	 * @param      string          $idName
	 *
	 * @return     Transaction|bool|null
	 */
	public function selectFisrt(mixed $id, string $idName = self::PRIMARY_KEY): ?Transaction
	{
		$transactionPoolResult =  parent::selectFisrt($id, $idName);

		return $transactionPoolResult
			? (new Transaction($transactionPoolResult))->_isNotNew()
			: null ;
	}

	/**
	 * @param      Transaction[]   $transactionPools
	 *
	 * @return     int
	 */
	public function bulkSave(array $transactionPools): int
	{
		$data = [];

		foreach ($transactionPools as $transactionPool) {
			$data[] = $transactionPool->getDataAsArray();
		}

		return $this->bulk($data);
	}

	/**
	 * @param      Transaction  $transactionPool
	 *
	 * @return     int
	 */
	public function save(Transaction $transactionPool): int
	{
		$transactionPoolArray = $transactionPool->getDataAsArray();

		return $transactionPool->_getIsNew()
			? $this->insert($transactionPoolArray)
			: $this->update(
				$transactionPoolArray[self::PRIMARY_KEY],
				$transactionPool->getDataAsArray(), self::PRIMARY_KEY
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

		$orQuery = implode("' OR hash = '", $transactions);

		$sql = "
		DELETE FROM {$this->tableName}
			WHERE hash = '$orQuery';
		";
		// echo $sql . PHP_EOL;

		return $this->queryLite($sql);
	}
}
