<?php

namespace Inescoin\Service;

use Inescoin\BlockchainConfig;
use Inescoin\DB\SQLiteConnection;
use Inescoin\DB\Schema;

final class SQLiteService {

	private $pdo;
	private $connection;
	private $lastInsertId = null;
	private $lastInsertIds = [];

	static $instance = null;

	public function __construct($database = BlockchainConfig::NAME) {
		$this->connection = SQLiteConnection::getInstance($database);
		$this->pdo = $this->connection->getPDO();
	}

	static function getInstance($database = BlockchainConfig::NAME): SQLiteService
	{
		if (null === self::$instance) {
			self::$instance = new self($database);
		}

		return self::$instance;
	}

	public function count($tableName)
	{
		$sql = "
		SELECT count(*) as counter
			FROM $tableName;
		";

		//echo $sql . PHP_EOL;

		$countResult = $this->query($sql);

		return $countResult[0]['counter'];
	}

	public function query($sql)
	{
		if (empty($sql)) {
			return [];
		}

		$query = "$sql";

		$stmt = $this->pdo->prepare($query);

		if ($stmt) {
			$stmt->execute();
			$result = [];

        	while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        		$result[] = $row;
        	}

        	return $result;
		} else {
			return [];
		}
	}

	public function queryLite($sql)
	{
		if (empty($sql)) {
			return 0;
		}

		$query = "$sql";

		$stmt = $this->pdo->prepare($query);

		$rowCount = 0;
		if ($stmt->execute()) {
			$stmt->rowCount();
		}

		return $rowCount;
	}

	public function range($tableName, $offset = 0, $limit = 10, $orderBy = 'height', $sortBy = 'asc', $where = '')
	{
		$sortBy = strtolower($sortBy);
		$sortBy = strtoupper($sortBy === 'desc' ? 'desc' : 'asc');

		$query = "
			SELECT * FROM $tableName
				$where
				ORDER BY $orderBy $sortBy
				LIMIT :offset, :limitTo;
		";
		//echo PHP_EOL . $query . PHP_EOL . ", offset: $offset, limit: $limit" . PHP_EOL;

		$stmt = $this->pdo->prepare($query);

		if ($stmt) {
			$stmt->bindValue(":offset", $offset);
			$stmt->bindValue(":limitTo", $limit);
			$stmt->execute();
			$result = [];

        	while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        		$result[] = $row;
        	}

        	return $result;
		} else {

			var_dump('$stmt error');
			return [];
		}
	}

	public function last($tableName, $limit = 1, $orderBy = 'height', $sortBy = 'DESC')
	{
		$query = "
			SELECT * FROM $tableName
				ORDER BY $orderBy $sortBy
				LIMIT :limitTo;
		";
		//echo PHP_EOL . $query . PHP_EOL . "limit: $limit" . PHP_EOL;

		$stmt = $this->pdo->prepare($query);

		if ($stmt) {
			$stmt->bindValue(":limitTo", $limit);
			$stmt->execute();
			$result = [];

        	while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        		$result[] = $row;
        	}

        	return !empty($result)
        		? $result[0]
        		: null;
		} else {
			return null;
		}
	}

	public function exists($id, $tableName, $idName = 'id')
	{
		$query = "
			SELECT $idName FROM $tableName
				WHERE $idName = :$idName
				LIMIT 1;
		";

		//echo PHP_EOL . $query . PHP_EOL . "$idName: $id" . PHP_EOL;

		$stmt = $this->pdo->prepare($query);

		if ($stmt) {
			$stmt->bindValue(":$idName", $id);
			$stmt->execute();

        	return !empty($stmt->fetch(\PDO::FETCH_ASSOC));
		} else {
			return false;
		}
	}

	public function select($id, $tableName, $idName = 'id', $offset = 0, $limit = 10, $orderBy = 'height', $sortBy = 'ASC')
	{
		$sortBy = $sortBy === 'DESC' ?: 'ASC';

		$query = "
			SELECT * FROM $tableName
				WHERE $idName = :$idName
				ORDER BY $orderBy $sortBy
				LIMIT :offset, :limitTo;
		";

		//echo PHP_EOL . $query . PHP_EOL . "$idName: $id" . PHP_EOL;

		$stmt = $this->pdo->prepare($query);

		if ($stmt) {
			$stmt->bindValue(":$idName", $id);
			$stmt->bindValue(":offset", $offset);
			$stmt->bindValue(":limitTo", $limit);
			$stmt->execute();

			$result = [];

        	while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        		$result[] = $row;
        	}

        	return $result;
		} else {
			return [];
		}
	}

	public function selectFisrt($id, $tableName, $idName = 'id')
	{
		$result = $this->select($id, $tableName, $idName, 0, 1, $idName);

		return !empty($result)
			? $result[0]
			: null;
	}

	public function insert($tableName, $data)
	{
		return $this->bulk($tableName, [$data]);
	}

	public function bulk($tableName, $array)
	{
		if (empty($array)) {
			return 0;
		}

		$query = $this->generateInsertSqlQuery($tableName, $array);

		// echo PHP_EOL . $query . PHP_EOL;

		$this->pdo->beginTransaction();

		$values = [];

		foreach ($array as $position => $data) {
			foreach ($data as $key => $value) {
				if ($this->keyExists($tableName, $key)) {
					$values[":$key" . $position] = $value;
				}
			}
		}

		$stmt = $this->pdo->prepare($query);

		try {
			$stmt->execute($values);
			$this->pdo->commit();
		} catch (\PDOException $e) {
            echo $e->getMessage();
            $this->pdo->rollback();
        }

        return $stmt->rowCount();
	}

	public function update($id, $tableName, $data, $idName = 'id')
	{
		$query = "UPDATE $tableName SET ";

		foreach ($data as $key => $value) {
			if ($this->keyExists($tableName, $key)) {
				$query .= "$key = :$key, ";
			}
		}

		$query = substr($query, 0, -2);

		$query .= " WHERE $idName = :$idName;";
		// echo $query . PHP_EOL;

		$stmt = $this->pdo->prepare($query);

		if (!$stmt) {
			var_dump('Error statment update: ' . $query);
			return;
		}

		$stmt->bindValue(":$idName", $id);

		foreach ($data as $key => $value) {
			if ($this->keyExists($tableName, $key)) {
				$stmt->bindValue(":$key", $value);
			}
		}

		try {
			$stmt->execute();
		} catch (\PDOException $e) {
            echo $e->getMessage();
        }

        // var_dump($idName, $id, $data, $stmt->rowCount());
        return $stmt->rowCount();
	}

	/**
	 * @param  string    $id
	 * @param  string $tableName
	 * @param  string $idName
	 *
	 * @return int
	 */
	public function delete(string $id, string $tableName, string $idName = 'id'): int
	{
		$query = "
			DELETE FROM $tableName
				WHERE $idName = :$idName;
		";

		$stmt = $this->pdo->prepare($query);

		$stmt->bindValue(":$idName", $id);
		$stmt->execute();

		return $stmt->rowCount();
	}

	/**
	 * @param  int    $id
	 * @param  string $tableName
	 * @param  string $idName
	 *
	 * @return int
	 */
	public function deleteLess(int $id, string $tableName, string $idName = 'id'): int
	{
		$query = "
			DELETE FROM $tableName
				WHERE $idName <= :$idName;
		";

		$stmt = $this->pdo->prepare($query);

		$stmt->bindValue(":$idName", $id);
		$stmt->execute();

		return $stmt->rowCount();
	}

	/**
	 * Drop table
	 *
	 * @param      string  $tableName  The table name
	 *
	 * @return     bool
	 */
	public function dropTable(string $tableName): bool
	{
		$query = "DROP TABLE $tableName;";

		$stmt = $this->pdo->prepare($query);

		$isDropped = $stmt->execute();

		$this->connection->createTables();

		return $isDropped;
	}

	/**
	 * @param  string $tableName
	 * @param  array  $fields
	 *
	 * @return string
	 */
	private function generateInsertSqlQuery(string $tableName, array $fields): string
	{
		if (empty($fields)) {
			return '';
		}

		foreach ($fields as $position => $data) {
			foreach ($data as $key => $value) {
				if (!$this->keyExists($tableName, $key)) {
					unset($fields[$position][$key]);
				}
			}
		}

		$query = "INSERT INTO $tableName (" . implode(', ', array_keys($fields[0]))
			. ') VALUES ';

		foreach ($fields as $position => $data) {
			$query .= '(:' . implode($position.', :' , array_keys($data)) . "$position),";
		}

		return substr($query, 0, -1) . ';';
	}

	private function keyExists($tableName, $key)
	{
		return isset(Schema::DATABASE[$tableName][$key]);
	}

	public function getLastInsertId(): int
	{
		return $this->lastInsertId;
	}

	public function getLastInsertIds(): array
	{
		return $this->lastInsertIds;
	}

	public function getConnection(): SQLiteConnection
	{
		return $this->connection;
	}

	public function getPDO(): \PDO
	{
		return $this->pdo;
	}
}
