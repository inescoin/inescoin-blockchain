<?php

namespace Inescoin\Manager;

use Inescoin\Entity\TransactionWallet as Transaction;
use Inescoin\Model\Transaction as TransactionModel;
use Inescoin\Service\SQLiteService;

class TransactionManager extends AbstractManager
{
	protected $tableName = 'transactionWallet';

	const PRIMARY_KEY = 'hash';

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

	public function range(int $offset = 0, int $limit = 10, string $orderBy = 'height', string $sortBy = 'asc')
	{
		$blocksResult = parent::range($offset, $limit, $orderBy, $sortBy);

		$blocks = [];

		if (!empty($blocksResult)) {
			foreach ($blocksResult as $block) {
				$blocks[] = (new Transaction($block))->_isNotNew();
			}
		}

		return $blocks;
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
		$transactionsResult =  parent::select($id, $idName, $offset, $limit, $orderBy, $sortBy);

		$transactions = [];

		if (!empty($transactionsResult)) {
			foreach ($transactionsResult as $transaction) {
				$transactions[] = (new Transaction($transaction))->_isNotNew();
			}
		}

		return $transactions;
	}

	/**
	 * @param      string          $id
	 * @param      string          $idName
	 *
	 * @return     Transaction|bool|null
	 */
	public function selectFisrt(mixed $id, string $idName = self::PRIMARY_KEY): ?Transaction
	{
		$transactionResult =  parent::selectFisrt($id, $idName);

		return $transactionResult
			? (new Transaction($transactionResult))->_isNotNew()
			: null ;
	}

	/**
	 * @param      string          $id
	 * @param      string          $idName
	 *
	 * @return     Transaction|bool|null
	 */
	public function selectFisrtAsArray(mixed $id, string $idName = self::PRIMARY_KEY): ?array
	{
		$transaction = parent::selectFisrt($id, $idName);

		if (null !== $transaction) {
			$transaction['transfers'] = $this->isSerializedString($transaction['transfers'])
				? unserialize($transaction['transfers'])
				: $transaction['transfers'];

			$transaction['toDo'] = $this->isSerializedString($transaction['toDo'])
				? unserialize($transaction['toDo'])
				: $transaction['toDo'];
		}

		return $transaction;
	}

	/**
	 * @param      Transaction[]   $transactions
	 *
	 * @return     int
	 */
	public function bulkSave(array $transactions): int
	{
		$data = [];

		foreach ($transactions as $transaction) {
			//var_dump($transaction);

			$transaction =  (!is_array($transaction))
				? $transaction
				: new Transaction($transaction);

			//var_dump($transaction->getDataAsArray());

			$data[] = $transaction->getDataAsArray();
		}

		return $this->bulk($data);
	}

	/**
	 * @param      Transaction  $transaction
	 *
	 * @return     int
	 */
	public function save(Transaction $transaction): int
	{
		$transactionArray = $transaction->getDataAsArray();

		return $transaction->_getIsNew()
			? $this->insert($transactionArray)
			: $this->update(
				$transactionArray[self::PRIMARY_KEY],
				$transaction->getDataAsArray(), self::PRIMARY_KEY
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
	 * Check if a string is serialized
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	private function isSerializedString($string)
	{
	    return ($string == 'b:0;' || @unserialize($string) !== false);
	}
}
