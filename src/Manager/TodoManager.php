<?php

namespace Inescoin\Manager;

use Inescoin\Entity\Todo;
use Inescoin\Service\SQLiteService;

class TodoManager extends AbstractManager
{
	protected $tableName = 'todo';
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
	 * @return     Todo[]
	 */
	public function select(mixed $id, string $idName = self::PRIMARY_KEY, int $offset = 0, int $limit = 10, ?string $orderBy = self::PRIMARY_KEY, ?string $sortBy = 'ASC'): array
	{
		$todosResult =  parent::select($id, $idName, $offset, $limit, $orderBy, $sortBy);

		$todos = [];

		if (!empty($todosResult)) {
			foreach ($todosResult as $todo) {
				$todos[] = (new Todo($todo))->_isNotNew();
			}
		}

		return $todos;
	}

	/**
	 * @param      string          $id
	 * @param      string          $idName
	 *
	 * @return     Todo|bool|null
	 */
	public function selectFisrt(mixed $id, string $idName = self::PRIMARY_KEY): ?Todo
	{
		$todoResult =  parent::selectFisrt($id, $idName);

		return $todoResult
			? (new Todo($todoResult))->_isNotNew()
			: null ;
	}

	/**
	 * @param      Todo[]   $todos
	 *
	 * @return     int
	 */
	public function bulkSave(array $todos): int
	{
		$data = [];

		foreach ($todos as $todo) {
			$todo = !is_array($todo)
				? $todo
				: new Todo($todo);

			$data[] = $todo->getDataAsArray();
		}

		return $this->bulk($data);
	}

	/**
	 * @param      Todo  $todo
	 *
	 * @return     int
	 */
	public function save(Todo $todo): int
	{
		$todoArray = $todo->getDataAsArray();

		return $todo->_getIsNew()
			? $this->insert($todoArray)
			: $this->update(
				$todoArray[self::PRIMARY_KEY],
				$todo->getDataAsArray(), self::PRIMARY_KEY
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
