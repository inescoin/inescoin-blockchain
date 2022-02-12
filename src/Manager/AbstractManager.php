<?php

namespace Inescoin\Manager;

use Inescoin\BlockchainConfig;
use Inescoin\Service\SQLiteService;

abstract class AbstractManager
{
	protected $tableName;
	protected $dbService;

	public function __construct(?string $database = BlockchainConfig::NAME) {
		$database = $database ?? BlockchainConfig::NAME;
		$this->dbService = SQLiteService::getInstance($database);
	}

	public function count()
	{
		return $this->dbService->count($this->tableName);
	}

	public function queryLite($sql) {
		return $this->dbService->queryLite($sql);
	}

	public function exists(string $id, string $idName = 'id')
	{
		return $this->dbService->exists($id, $this->tableName, $idName);
	}

	public function query(string $sql = '')
	{
		return $this->dbService->query($sql);
	}

	public function range(int $offset = 0, int $limit = 10, string $orderBy = 'height', string $sortBy = 'asc')
	{
		return $this->dbService->range($this->tableName, $offset, $limit, $orderBy, $sortBy);
	}

	public function last(int $limit = 1, string $orderBy = 'height', string $sortBy = 'desc')
	{
		return $this->dbService->last($this->tableName, $limit, $orderBy, $sortBy);
	}

	public function select(mixed $id, string $idName = 'id', int $offset = 0, int $limit = 10, ?string $orderBy = null, ?string $sortBy = null)
	{
		return $this->dbService->select($id, $this->tableName, $idName, $offset, $limit, $orderBy, $sortBy);
	}

	public function selectFisrt(mixed $id, string $idName = 'id')
	{
		return $this->dbService->selectFisrt($id, $this->tableName, $idName);
	}

	protected function insert(array $data)
	{
		return $this->dbService->insert($this->tableName, $data);
	}

	protected function bulk(array $array)
	{
		return $this->dbService->bulk($this->tableName, $array);
	}

	protected function update(mixed $id, array $data, string $idName = 'id')
	{
		return $this->dbService->update($id, $this->tableName, $data, $idName);
	}

	public function delete(mixed $id, string $idName = 'id'): int
	{
		return $this->dbService->delete($id, $this->tableName, $idName);
	}

	public function deleteLess(mixed $id, string $idName = 'id'): int
	{
		return $this->dbService->deleteLess($id, $this->tableName, $idName);
	}

	public function dropTable(): bool
	{
		return $this->dbService->dropTable($this->tableName);
	}

	public function getDbService(): SQLiteService
	{
		return $this->dbService;
	}
}
