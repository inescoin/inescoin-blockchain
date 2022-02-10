<?php

namespace Inescoin\Manager;

use Inescoin\Entity\MessagePool;
use Inescoin\Service\SQLiteService;

class MessagePoolManager extends AbstractManager
{
	protected $tableName = 'messagePool';
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
	 * @return     MessagePool[]
	 */
	public function select(mixed $id, string $idName = self::PRIMARY_KEY, int $offset = 0, int $limit = 10, ?string $orderBy = self::PRIMARY_KEY, ?string $sortBy = 'ASC'): array
	{
		$messagePoolsResult =  parent::select($id, $idName, $offset, $limit, $orderBy, $sortBy);

		$messagePools = [];

		if (!empty($messagePoolsResult)) {
			foreach ($messagePoolsResult as $messagePool) {
				$messagePools[] = (new MessagePool($messagePool))->_isNotNew();
			}
		}

		return $messagePools;
	}

	/**
	 * @param      string          $id
	 * @param      string          $idName
	 *
	 * @return     MessagePool|bool|null
	 */
	public function selectFisrt(mixed $id, string $idName = self::PRIMARY_KEY): ?MessagePool
	{
		$messagePoolResult =  parent::selectFisrt($id, $idName);

		return $messagePoolResult
			? (new MessagePool($messagePoolResult))->_isNotNew()
			: null ;
	}

	/**
	 * @param      MessagePool[]   $messagePools
	 *
	 * @return     int
	 */
	public function bulkSave(array $messagePools): int
	{
		$data = [];

		foreach ($messagePools as $messagePool) {
			$data[] = $messagePool->getDataAsArray();
		}

		return $this->bulk($data);
	}

	/**
	 * @param      MessagePool  $messagePool
	 *
	 * @return     int
	 */
	public function save(MessagePool $messagePool): int
	{
		$messagePoolArray = $messagePool->getDataAsArray();

		return $messagePool->_getIsNew()
			? $this->insert($messagePoolArray)
			: $this->update(
				$messagePoolArray[self::PRIMARY_KEY],
				$messagePool->getDataAsArray(), self::PRIMARY_KEY
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
}
