<?php

namespace Inescoin\Manager;

use Inescoin\Entity\Peer;
use Inescoin\Service\SQLiteService;

class PeerManager extends AbstractManager
{
	protected $tableName = 'peer';
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
	 * @return     Peer[]
	 */
	public function select(mixed $id, string $idName = self::PRIMARY_KEY, int $offset = 0, int $limit = 10, ?string $orderBy = self::PRIMARY_KEY, ?string $sortBy = 'ASC'): array
	{
		$peersResult =  parent::select($id, $idName, $offset, $limit, $orderBy, $sortBy);

		$peers = [];

		if (!empty($peersResult)) {
			foreach ($peersResult as $peer) {
				$peers[] = (new Peer($peer))->_isNotNew();
			}
		}

		return $peers;
	}

	public function getRemoteAddresses()
	{
		return parent::range(0, 10, 'lastSeen', 'desc');
	}

	/**
	 * @param      string          $id
	 * @param      string          $idName
	 *
	 * @return     Peer|bool|null
	 */
	public function selectFisrt(mixed $id, string $idName = self::PRIMARY_KEY): ?Peer
	{
		$peerResult =  parent::selectFisrt($id, $idName);

		return $peerResult
			? (new Peer($peerResult))->_isNotNew()
			: null ;
	}

	/**
	 * @param      Peer[]   $peers
	 *
	 * @return     int
	 */
	public function bulkSave(array $peers): int
	{
		$data = [];

		foreach ($peers as $peer) {
			$data[] = $peer->getDataAsArray();
		}

		return $this->bulk($data);
	}

	/**
	 * @param      Peer  $peer
	 *
	 * @return     int
	 */
	public function save(Peer $peer): int
	{
		$peerArray = $peer->getDataAsArray();

		return $peer->_getIsNew()
			? $this->insert($peerArray)
			: $this->update(
				$peerArray[self::PRIMARY_KEY],
				$peer->getDataAsArray(), self::PRIMARY_KEY
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
