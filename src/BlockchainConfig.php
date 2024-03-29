<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin;

class BlockchainConfig {

	const GENESIS_DATE = '1984-12-31 23:59:59';

	const GENESIS_BLOCK_HASH = '5c678938f98b749189d25c2619d2d0f1b2b7953c2d479b15a79a34d65f336a0f';

	const CONFIG_HASH = '97bc4d0e2cb36e49c885e78f3ae866b7';

	const GENESIS_MINER_REWARD = 900000000000000000;

	const GENESIS_MINER_ADDRESS = '0x118D45F627999f6CC1CE80C98f9ED90b4e2CC1E0';

	const HASH_ALGORITHM = 'SHA256';

    const FIXED_TRANSACTION_FEE = 1000000;

    const FIXED_MINER_REWARD = 100000000000;

	const DISPLAY_DECIMA_POINT = 9;

	const MIN_TRANSACRTION_AMOUNT = self::FIXED_TRANSACTION_FEE * 2;

	const MAX_TRANSACRTION_AMOUNT = 9000000000000000000;

    const NAME = 'inescoin';

    const DIFFICULTY_TARGET = 3;

    const BLOCK_TARGET = 30;

    const SYMBOL = 'ines';

    const VERSION = '2.0';

	const MIN_DIFFICULTY = 20;

	const NEXT_TIMESTAMP = 2;

	const NEXT_EMPTY_TIMESTAMP = 3600;

	const WEB_COST_UNIT_BLOCKS = 10000;

	const WEB_COST_ONE_MONTH = 99999000000;

	const WEB_COST_THREE_MONTH = 199999000000;

	const WEB_COST_SIX_MONTH = 299999000000;

	const WEB_COST_UPDATE = 999000000;

	const WEB_COST_DELETE = 299999000000;

	const WEB_ACTION_CREATE = 'create';

	const WEB_ACTION_UPDATE = 'update';

	const WEB_ACTION_DELETE = 'delete';

	const WEB_ACTION_RENEW = 'renew';

	const WEB_URL_MIN_SIZE = 7;

	const WEB_URL_MAX_SIZE = 70;

	const WEB_COSTS_WITHOUT_UPDATE = [
		self::WEB_COST_ONE_MONTH,
		self::WEB_COST_THREE_MONTH,
		self::WEB_COST_SIX_MONTH,
		self::WEB_COST_DELETE,
	];

	const AUTHORIZED_MINERS = '0x118D45F627999f6CC1CE80C98f9ED90b4e2CC1E0|0xf1757130F3D4BE7455494C73bBdA797486731C77|0x569aa4C1B593D68a5525DaA268DcC6734010F439|0x460fdA7C610580e319E325e0274d1dFA43B3F9c7|0x118C80dc3CB893620278391CfE4188959b57cF17';

    const CONFIG = [
		'GENESIS_DATE' => self::GENESIS_DATE,
    	'GENESIS_MINER_REWARD' => self::GENESIS_MINER_REWARD,
		'GENESIS_MINER_ADDRESS' => self::GENESIS_MINER_ADDRESS,
		'HASH_ALGORITHM' => self::HASH_ALGORITHM,
	    'FIXED_MINER_REWARD' => self::FIXED_MINER_REWARD,
		'DISPLAY_DECIMA_POINT' => self::DISPLAY_DECIMA_POINT,
		'FIXED_TRANSACTION_FEE' => self::FIXED_TRANSACTION_FEE,
		'MIN_TRANSACRTION_AMOUNT' => self::MIN_TRANSACRTION_AMOUNT,
		'MAX_TRANSACRTION_AMOUNT' => self::MAX_TRANSACRTION_AMOUNT,
	    'NAME' => self::NAME,
	    'AUTHORIZED_MINERS' => self::AUTHORIZED_MINERS,
	    'DIFFICULTY_TARGET' => self::DIFFICULTY_TARGET,
	    'BLOCK_TARGET' => self::BLOCK_TARGET,
	    'SYMBOL' => self::SYMBOL,
	    'VERSION' => self::VERSION,
		'MIN_DIFFICULTY' => self::MIN_DIFFICULTY,
		'NEXT_TIMESTAMP' => self::NEXT_TIMESTAMP,
		'NEXT_EMPTY_TIMESTAMP' => self::NEXT_EMPTY_TIMESTAMP,
		'WEB_COST_ONE_MONTH' => self::WEB_COST_ONE_MONTH,
		'WEB_COST_THREE_MONTH' => self::WEB_COST_THREE_MONTH,
		'WEB_COST_SIX_MONTH' => self::WEB_COST_SIX_MONTH,
		'WEB_COST_UPDATE' => self::WEB_COST_UPDATE,
		'WEB_COST_DELETE' => self::WEB_COST_DELETE,
		'WEB_ACTION_CREATE' => self::WEB_ACTION_CREATE,
		'WEB_ACTION_UPDATE' => self::WEB_ACTION_UPDATE,
		'WEB_ACTION_DELETE' => self::WEB_ACTION_DELETE,
		'WEB_ACTION_RENEW' => self::WEB_ACTION_RENEW,
		'WEB_URL_MIN_SIZE' => self::WEB_URL_MIN_SIZE,
		'WEB_URL_MAX_SIZE' => self::WEB_URL_MAX_SIZE,
    ];

	static function getHash(): string
	{
		return md5(implode('', self::CONFIG));
	}
}
