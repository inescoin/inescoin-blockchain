<?php

namespace Inescoin\Manager;

use Inescoin\Entity\Domain;
use Inescoin\Service\SQLiteService;

class DomainManager extends AbstractManager
{
	protected $tableName = 'domain';
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
	 * @param      int     $offset
	 * @param      int     $limit
	 * @param      string  $orderBy
	 * @param      string  $sortBy
	 *
	 * @return     array
	 */
	public function rangeAsArray(int $offset = 0, int $limit = 10, string $orderBy = 'height', string $sortBy = 'asc'): array
	{
		return parent::range($offset, $limit, $orderBy, $sortBy);
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
	 * @param      mixed        $id
	 * @param      int  		$page
	 * @param      int          $size
	 *
	 * @return     Domain[]
	 */
	public function selectHistory(mixed $addresses, int $page = 1, int $size = 500): array
	{
		$from = 0;
		if ($page > 1) {
			$from = ((int) $page * $size - 1) - $size;
		}

		if (is_string($addresses)) {
			$addresses = [$addresses];
		}

		$orQuery = implode("' OR ownerAddress = '", $addresses);

		$sql = "
		SELECT *
			FROM {$this->tableName}
			WHERE ownerAddress = '$orQuery'
			ORDER BY url ASC
			LIMIT $from, $size;
		";

		//echo $sql . PHP_EOL;

		$domainsResult = $this->query($sql);

		$domains = [];

		if (!empty($domainsResult)) {
			foreach($domainsResult as $domain) {
				$domains[] = $domain;
			}
		}

		return $domains;
	}

	/**
	 * @param      mixed        $id
	 *
	 * @return     int
	 */
	public function countHistory(mixed $addresses): int
	{
		if (is_string($addresses)) {
			$addresses = [$addresses];
		}

		$orQuery = implode("' OR ownerAddress = '", $addresses);

		$sql = "
		SELECT count(*) as counter
			FROM {$this->tableName}
			WHERE ownerAddress = '$orQuery';
		";

		//echo $sql . PHP_EOL;

		$countResult = $this->query($sql);

		return $countResult[0]['counter'];
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
	 * @return     Domain[]
	 */
	public function select(mixed $id, string $idName = self::PRIMARY_KEY, int $offset = 0, int $limit = 10, ?string $orderBy = self::PRIMARY_KEY, ?string $sortBy = 'ASC'): array
	{
		$domainsResult =  parent::select($id, $idName, $offset, $limit, $orderBy, $sortBy);

		$domains = [];

		if (!empty($domainsResult)) {
			foreach ($domainsResult as $domain) {
				$domains[] = (new Domain($domain))->_isNotNew();
			}
		}

		return $domains;
	}

	/**
	 * @param      string          $id
	 * @param      string          $idName
	 *
	 * @return     Domain|bool|null
	 */
	public function selectFisrt(mixed $id, string $idName = self::PRIMARY_KEY): ?Domain
	{
		$domainResult =  parent::selectFisrt($id, $idName);

		return $domainResult
			? (new Domain($domainResult))->_isNotNew()
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
	 * @param      Domain[]   $domains
	 *
	 * @return     int
	 */
	public function bulkSave(array $domains): int
	{
		$data = [];

		foreach ($domains as $domain) {
			$data[] = $domain->getDataAsArray();
		}

		return $this->bulk($data);
	}

	/**
	 * @param      Domain  $domain
	 *
	 * @return     int
	 */
	public function save(Domain $domain): int
	{
		$domainArray = $domain->getDataAsArray();

		return $domain->_getIsNew()
			? $this->insert($domainArray)
			: $this->update(
				$domainArray[self::PRIMARY_KEY],
				$domain->getDataAsArray(), self::PRIMARY_KEY
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

	/**
	 * @param      mixed  	$id
	 * @param      string  	$idName
	 *
	 * @return     int
	 */
	public function deleteLess(mixed $id, string $idName = self::PRIMARY_KEY): int
	{
		return parent::deleteLess($id, $idName);
	}
}
