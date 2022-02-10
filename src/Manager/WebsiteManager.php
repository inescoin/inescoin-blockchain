<?php

namespace Inescoin\Manager;

use Inescoin\Entity\Website;
use Inescoin\Service\SQLiteService;

class WebsiteManager extends AbstractManager
{
	protected $tableName = 'website';
	const PRIMARY_KEY = 'url';

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
	 * @return     Website[]
	 */
	public function select(mixed $id, string $idName = self::PRIMARY_KEY, int $offset = 0, int $limit = 10, ?string $orderBy = self::PRIMARY_KEY, ?string $sortBy = 'ASC'): array
	{
		$websitesResult =  parent::select($id, $idName, $offset, $limit, $orderBy, $sortBy);

		$websites = [];

		if (!empty($websitesResult)) {
			foreach ($websitesResult as $website) {
				$websites[] = (new Website($website))->_isNotNew();
			}
		}

		return $websites;
	}

	/**
	 * @param      string          $id
	 * @param      string          $idName
	 *
	 * @return     Website|bool|null
	 */
	public function selectFisrt(mixed $id, string $idName = self::PRIMARY_KEY): ?Website
	{
		$websiteResult =  parent::selectFisrt($id, $idName);

		return $websiteResult
			? (new Website($websiteResult))->_isNotNew()
			: null ;
	}

	/**
	 * @param      string          $id
	 * @param      string          $idName
	 *
	 * @return     array|bool|null
	 */
	public function selectFisrtAsArray(mixed $id, string $idName = self::PRIMARY_KEY): ?array
	{
		return parent::selectFisrt($id, $idName);
	}

	/**
	 * @param      Website[]   $websites
	 *
	 * @return     int
	 */
	public function bulkSave(array $websites): int
	{
		$data = [];

		foreach ($websites as $website) {
			$data[] = $website->getDataAsArray();
		}

		return $this->bulk($data);
	}

	/**
	 * @param      Website  $website
	 *
	 * @return     int
	 */
	public function save(Website $website): int
	{
		$websiteArray = $website->getDataAsArray();

		return $website->_getIsNew()
			? $this->insert($websiteArray)
			: $this->update(
				$websiteArray[self::PRIMARY_KEY],
				$website->getDataAsArray(), self::PRIMARY_KEY
			);
	}

	/**
	 * @param      array  $data
	 *
	 * @return     int
	 */
	public function insert(array $data): int
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
	public function update(mixed $id, array $data, string $idName = self::PRIMARY_KEY): int
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
