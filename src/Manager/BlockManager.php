<?php

namespace Inescoin\Manager;

use Inescoin\Entity\Block;
use Inescoin\Model\Block as BlockModel;
use Inescoin\Service\SQLiteService;

class BlockManager extends AbstractManager
{
	protected $tableName = 'block';
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

	public function range(int $offset = 0, int $limit = 10, string $orderBy = 'height', string $sortBy = 'asc')
	{
		$blocksResult = parent::range($offset, $limit, $orderBy, $sortBy);

		$blocks = [];

		if (!empty($blocksResult)) {
			foreach ($blocksResult as $block) {
				$blocks[] = (new Block($block))->_isNotNew();
			}
		}

		return $blocks;
	}

	public function rangeAsArray(int $offset = 0, int $limit = 10, string $orderBy = 'height', string $sortBy = 'asc')
	{
		$blocksResult = parent::range($offset, $limit, $orderBy, $sortBy);

		$blocks = [];

		if (!empty($blocksResult)) {
			foreach ($blocksResult as $block) {
				$blocks[] = (new BlockModel($block))->getJsonInfos();
			}
		}

		return $blocks;
	}

	public function last(int $limit = 1, string $orderBy = 'height', string $sortBy = 'desc')
	{
		$blockResult =  parent::last($limit, $orderBy, $sortBy);

		return $blockResult
			? (new Block($blockResult))->_isNotNew()
			: null ;
	}

	public function lastAsArray(int $limit = 1, string $orderBy = 'height', string $sortBy = 'desc')
	{
		$blockResult =  parent::last($limit, $orderBy, $sortBy);

		return $blockResult
			? (new BlockModel($blockResult))->getJsonInfos()
			: null ;
	}

	/**
	 * @param      mixed       $id
	 * @param      string       $idName
	 * @param      null|string  $orderBy
	 * @param      int          $offset
	 * @param      int          $limit
	 *
	 * @return     Block[]
	 */
	public function select(mixed $id, string $idName = self::PRIMARY_KEY, int $offset = 0, int $limit = 10, ?string $orderBy = self::PRIMARY_KEY, ?string $sortBy = 'ASC'): array
	{
		$blocksResult =  parent::select($id, $idName, $offset, $limit, $orderBy, $sortBy);

		$blocks = [];
		if (!empty($blocksResult)) {
			foreach ($blocksResult as $block) {
				$blocks[] = (new Block($block))->_isNotNew();
			}
		}
		return $blocks;
	}

	/**
	 * @param      string          $id
	 * @param      string          $idName
	 *
	 * @return     Block|bool|null
	 */
	public function selectFisrt(mixed $id, string $idName = self::PRIMARY_KEY): ?Block
	{
		$blockResult =  parent::selectFisrt($id, $idName);

		return $blockResult
			? (new Block($blockResult))->_isNotNew()
			: null ;
	}

	public function selectFisrtAsArray(mixed $id, string $idName = self::PRIMARY_KEY): ?array
	{
		$block =  parent::selectFisrt($id, $idName);

		if (null !== $block) {
			$block = (new BlockModel($block))->getJsonInfos();
		}

		return $block;
	}

	/**
	 * @param      Block[]   $blocks
	 *
	 * @return     int
	 */
	public function bulkSave(array $blocks): int
	{
		$data = [];

		foreach ($blocks as $block) {
			$data[] = $block->getDataAsArray();
		}

		return $this->bulk($data);
	}

	/**
	 * @param      Block  $block
	 *
	 * @return     int
	 */
	public function save(Block $block): int
	{
		$blockArray = $block->getDataAsArray();

		return $block->_getIsNew()
			? $this->insert($blockArray)
			: $this->update(
				$blockArray[self::PRIMARY_KEY],
				$block->getDataAsArray(),
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
	 * @param      mixed  $id
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
	 * @param      mixed  $id
	 * @param      string  $idName
	 *
	 * @return     int
	 */
	public function delete(mixed $id, string $idName = self::PRIMARY_KEY): int
	{
		return parent::delete($id, $idName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function dropTable(): bool
	{
		return parent::dropTable();
	}
}
