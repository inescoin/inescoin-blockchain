<?php

namespace Inescoin\Manager;

use Inescoin\Entity\BlockTemp;
use Inescoin\Service\SQLiteService;

class BlockTempManager extends AbstractManager
{
	protected $tableName = 'blockTemp';
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

	/**
	 * @param      string       $id
	 * @param      string       $idName
	 * @param      null|string  $orderBy
	 * @param      int          $offset
	 * @param      int          $limit
	 *
	 * @return     BlockTemp[]
	 */
	public function select(mixed $id, string $idName = self::PRIMARY_KEY, int $offset = 0, int $limit = 10, ?string $orderBy = self::PRIMARY_KEY, ?string $sortBy = 'ASC'): array
	{
		$blockTempsResult =  parent::select($id, $idName, $offset, $limit, $orderBy, $sortBy);

		$blockTemps = [];

		if (!empty($blockTempsResult)) {
			foreach ($blockTempsResult as $blockTemp) {
				$blockTemps[] = (new BlockTemp($blockTemp))->_isNotNew();
			}
		}

		return $blockTemps;
	}

	/**
	 * @param      string          $id
	 * @param      string          $idName
	 *
	 * @return     BlockTemp|bool|null
	 */
	public function selectFisrt(mixed $id, string $idName = self::PRIMARY_KEY): ?BlockTemp
	{
		$blockTempResult =  parent::selectFisrt($id, $idName);

		return $blockTempResult
			? (new BlockTemp($blockTempResult))->_isNotNew()
			: null ;
	}

	/**
	 * @param      BlockTemp[]   $blockTemps
	 *
	 * @return     int
	 */
	public function bulkSave(array $blockTemps): int
	{
		$data = [];

		foreach ($blockTemps as $blockTemp) {
			$data[] = $blockTemp->getDataAsArray();
		}

		return $this->bulk($data);
	}

	/**
	 * @param      BlockTemp  $blockTemp
	 *
	 * @return     int
	 */
	public function save(BlockTemp $blockTemp): int
	{
		$blockTempArray = $blockTemp->getDataAsArray();

		return $blockTemp->_getIsNew()
			? $this->insert($blockTempArray)
			: $this->update(
				$blockTempArray[self::PRIMARY_KEY],
				$blockTemp->getDataAsArray(),
				self::PRIMARY_KEY
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
