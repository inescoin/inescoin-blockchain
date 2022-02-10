<?php

namespace Inescoin\DB;

use Inescoin\BlockchainConfig;

class SQLiteConnection {

	private $pdo;
	private static $database = BlockchainConfig::NAME;

	static $instance = null;

	public function __construct(?string $database = BlockchainConfig::NAME)
	{
		self::$database = $database ?? BlockchainConfig::NAME;
		$dbFile = "sqlite:" . __DIR__ . '/../../' . self::$database . '.dbi';
		try {
			$this->pdo = new \PDO($dbFile);
		    $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
	        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); // ERRMODE_WARNING | ERRMODE_EXCEPTION | ERRMODE_SILENT
	    } catch(Exception $e) {
            echo "Impossible d'accéder à la base de données SQLite : ".$e->getMessage();
            die();
        }

        $this->createTables();
	}

	static function getInstance(?string $database = BlockchainConfig::NAME)
	{
		if (null === self::$instance) {
			self::$database = $database;
			self::$instance = new self($database);
		}

		return self::$instance;
	}

	/**
	 * @return array
	 */
	public function getSchema(): array {
		return Schema::DATABASE;
	}

	/**
	 * Creation of all tables needed
	 * @return void
	 */
	public function createTables(): void {
		$sql = '';
		foreach ($this->getSchema() as $table => $schema) {
			if (!empty($schema)) {
				$sql .= "CREATE TABLE IF NOT EXISTS $table (";
				foreach ($schema as $fieldName => $type) {
					$sql .= "$fieldName $type, ";
				}
				$sql = substr($sql, 0, -2);
				$sql .= ");" . PHP_EOL;
			}
		}

		$this->pdo->exec($sql);
	}

	/**
	 * Drop all tables
	 * @return void
	 */
	public function dropTables(): void {
		$sql = '';
		foreach ($this->getSchema() as $table => $schema) {
			if (!empty($schema)) {
				$sql .= "DROP TABLE $table;" . PHP_EOL;
			}
		}

		$this->pdo->exec($sql);

		$this->createTables();
	}

	/**
	 * Select all table names into database
	 *
	 * @return array<string> Liste of tables into database
	 */
	public function getTableNames(): array {
		$stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite%'");
		$result = [];
    	while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
    		$result[] = $row['name'];
    	}

    	return $result;
	}

	public function getPDO(): \PDO
	{
		return $this->pdo;
	}
}
