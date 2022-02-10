<?php

namespace Inescoin\Manager;

use Inescoin\Entity\Message;
use Inescoin\Service\SQLiteService;

class MessageManager extends AbstractManager
{
	protected $tableName = 'message';
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
	 * @return     Message[]
	 */
	public function select(mixed $id, string $idName = self::PRIMARY_KEY, int $offset = 0, int $limit = 10, ?string $orderBy = self::PRIMARY_KEY, ?string $sortBy = 'ASC'): array
	{
		$messagesResult =  parent::select($id, $idName, $offset, $limit, $orderBy, $sortBy);

		$messages = [];

		if (!empty($messagesResult)) {
			foreach ($messagesResult as $message) {
				$messages[] = (new Message($message))->_isNotNew();
			}
		}

		return $messages;
	}

	/**
	 * @param      string          $id
	 * @param      string          $idName
	 *
	 * @return     Message|bool|null
	 */
	public function selectFisrt(mixed $id, string $idName = self::PRIMARY_KEY): ?Message
	{
		$messageResult =  parent::selectFisrt($id, $idName);

		return $messageResult
			? (new Message($messageResult))->_isNotNew()
			: null ;
	}

	/**
	 * @param      Message[]   $messages
	 *
	 * @return     int
	 */
	public function bulkSave(array $messages): int
	{
		$data = [];

		foreach ($messages as $message) {
			$data[] = $message->getDataAsArray();
		}

		return $this->bulk($data);
	}

	/**
	 * @param      Message  $message
	 *
	 * @return     int
	 */
	public function save(Message $message): int
	{
		$messageArray = $message->getDataAsArray();

		return $message->_getIsNew()
			? $this->insert($messageArray)
			: $this->update(
				$messageArray[self::PRIMARY_KEY],
				$message->getDataAsArray(), self::PRIMARY_KEY
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
