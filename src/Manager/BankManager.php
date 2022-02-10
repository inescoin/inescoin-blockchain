<?php

namespace Inescoin\Manager;

use Inescoin\BlockchainConfig;
use Inescoin\Entity\Bank;
use Inescoin\Entity\Transfer;
use Inescoin\Manager\TransferManager;
use Inescoin\Service\SQLiteService;

class BankManager extends AbstractManager
{
	protected $tableName = 'bank';

	const PRIMARY_KEY = 'address';

	/**
	 * @var TransferManager
	 */
	protected $transferManager;


	public function __construct($database = null) {
		parent::__construct($database);

		$this->transferManager = new TransferManager();
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
	 * @param      mixed       $addresses
	 *
	 * @return     Bank[]
	 */
	public function getAddressBalances(mixed $addresses, $asArray = false): array
	{
		if (is_string($addresses)) {
			$addresses = [$addresses];
		}

		$orQuery = implode("' OR address LIKE '", $addresses);

		$sql = "
		SELECT *
			FROM {$this->tableName}
			WHERE address LIKE '$orQuery'
			LIMIT 1000;
		";
		// echo $sql . PHP_EOL;

		$banksResult = $this->query($sql);

		$banks = [];

		if (!empty($banksResult)) {
			foreach ($banksResult as $bank) {
				$banks[$bank['address']] = $asArray
					? $bank
					: (new Bank($bank))->_isNotNew();
			}
		}

		return $banks;
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
	 * @return     int
	 */
	public function amount(): int
	{
		$sql = "
		SELECT sum(amount) as totalAmount
			FROM {$this->tableName}
			WHERE address != '" . BlockchainConfig::NAME . "';
		";

		//echo $sql . PHP_EOL;

		$countResult = $this->query($sql);

		return $countResult[0]['totalAmount'];
	}

	/**
	 * @return     bool
	 */
	public function isValid(): bool
	{
		$sql = "
		SELECT sum(amount) as totalAmount
			FROM {$this->tableName};
		";

		//echo $sql . PHP_EOL;

		$countResult = $this->query($sql);

		return $countResult[0]['totalAmount'] === 0;
	}

	/**
	 * @param      string       $id
	 * @param      string       $idName
	 * @param      null|string  $orderBy
	 * @param      int          $offset
	 * @param      int          $limit
	 *
	 * @return     Bank[]
	 */
	public function select(mixed $id, string $idName = self::PRIMARY_KEY, int $offset = 0, int $limit = 10, ?string $orderBy = self::PRIMARY_KEY, ?string $sortBy = 'ASC'): array
	{
		$banksResult =  parent::select($id, $idName, $offset, $limit, $orderBy, $sortBy);

		$banks = [];

		if (!empty($banksResult)) {
			foreach ($banksResult as $bank) {
				$banks[] = (new Bank($bank))->_isNotNew();
			}
		}

		return $banks;
	}

	/**
	 * @param      string          $id
	 * @param      string          $idName
	 *
	 * @return     Bank|bool|null
	 */
	public function selectFisrt(mixed $id, string $idName = self::PRIMARY_KEY): ?Bank
	{
		$bankResult =  parent::selectFisrt($id, $idName);

		return $bankResult
			? (new Bank($bankResult))->_isNotNew()
			: null ;
	}

	/**
	 * @param      Bank[]   $banks
	 *
	 * @return     int
	 */
	public function bulkSave(array $banks): int
	{
		$data = [];

		foreach ($banks as $bank) {
			$bank =  (!is_array($bank))
				? $bank
				: new Bank($bank);

			$data[] = $bank->getDataAsArray();
		}

		return $this->bulk($data);
	}

	/**
	 * @param      Bank  $bank
	 *
	 * @return     int
	 */
	public function save(Bank $bank): int
	{
		$bankArray = $bank->getDataAsArray();

		return $bank->_getIsNew()
			? $this->insert($bankArray)
			: $this->update(
				$bankArray[self::PRIMARY_KEY],
				$bank->getDataAsArray(), self::PRIMARY_KEY
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
