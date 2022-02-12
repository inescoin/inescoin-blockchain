<?php

// Copyright 2019-2022 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\Helper;

use DateTimeImmutable;
use Inescoin\BlockchainConfig;
use Inescoin\Entity\Block;
use Inescoin\Helper\MerkleTree;
use Inescoin\Helper\TransactionHelper;
use Inescoin\Manager\BlockchainManager;
use Inescoin\Model\Block as BlockModel;
use Inescoin\Model\Transaction;
use Inescoin\Service\LoggerService;

class BlockHelper
{
    /**
     * @param      array        $blocks
     * @param      null|string  $database
     * @param      bool         $resetMode
     *
     * @return     int
     */
    public static function extractBlock(array $blocks, ?string $database = null, bool $resetMode = false): int
    {
        if (empty($blocks)) {
            return 0;
        }

        $blocksToSave = [];

        $blockchainManager = new BlockchainManager($database);
        $logger = (LoggerService::getInstance())->getLogger();

        $blocksList = [];
        $transactionsList = [];
        $transferInTransaction = [];
        $addressBalanceTo = [];
        $addressBalanceFrom = [];
        $finalHolders = [BlockchainConfig::NAME];

        $wallets = [];

        $walletBank = $blockchainManager->getBank()->getAddressBalances([BlockchainConfig::NAME]);

        if (empty($walletBank)) {
            $wallets[BlockchainConfig::NAME] =  [
                'amount' => 0,
                'transactionHash' => '',
                'transferHash' => '',
                'height' => 0,
                'hash' => ''
            ];
        } else {
            $wallets[BlockchainConfig::NAME] = $walletBank[BlockchainConfig::NAME]->getDataAsArray();
        }

        $lastBlock = $blockchainManager->getBlock()->last();
        $lastBlockHeight = $blocks[0]->getHeight();

        $previousBlock = !$resetMode
            ? $lastBlock
            : null;

        if (null !== $previousBlock) {
            $previousBlock = new BlockModel($previousBlock->getDataAsArray());
        }

        foreach ($blocks as $block) {
            $blockModel = new BlockModel($block->getDataAsArray());

            if (null !== $previousBlock) {
                if ($previousBlock->getHeight() === $block->getHeight()) {
                    $logger->error('[BlockHelper] [extractBlock] $previousBlock->getHeight === $block->getHeight');
                    continue;
                }

                if (!$previousBlock->isNextValid($blockModel)) {
                    $logger->error('[BlockHelper] [extractBlock] $previousBlock->isNextValid');
                    continue;
                }
            }

            $previousBlock = $blockModel;

            $_block = $blockModel->getJsonInfos();

            $hasDomain = false;

            $blockFee = 0;
            $blocksList[$_block['height']] = $_block;
            $transactions = $_block['data'];

            $holders = [];
            $minerReward = 0;
            $minerAddress = '';
            $totalBlockAmount = 0;
            $totalBlockTransactions = count($transactions);

            $minerTransaction = [];
            $minerTransfer = [];

            foreach ($transactions as $transaction) {
                // var_dump($_block['height'] . ' -> ' . $transaction['hash']);
                $transactionsList[$transaction['hash']] = [
                    "hash" => $transaction['hash'],
                    "configHash" => $transaction['configHash'],
                    "bankHash" => $transaction['bankHash'],
                    "height" => $_block['height'],
                    "fromWalletId" => isset($transaction['fromWalletId']) ? $transaction['fromWalletId'] : $transaction['from'],
                    "toDo" => $transaction['toDo'],
                    "toDoHash" => $transaction['toDoHash'],
                    "transfers" => $transaction['transfers'],
                    "amount" => $transaction['amount'],
                    "amountWithFee" => $transaction['amountWithFee'],
                    "createdAt" => $transaction['createdAt'] ?? null,
                    "coinbase" => $transaction['coinbase'] ?? false,
                    "fee" => $transaction['fee'],
                    "publicKey" => $transaction['publicKey'],
                    "signature" => $transaction['signature'],
                    'status' => 'pending'
                ];

                $fromWalletId = $transactionsList[$transaction['hash']]['fromWalletId'];

                $blockFee += $transaction['fee'];

                if ($transaction['coinbase'] && $fromWalletId === BlockchainConfig::NAME) {
                    if ($_block['height'] !== 1 && $wallets[BlockchainConfig::NAME]['hash'] !== '' && $wallets[BlockchainConfig::NAME]['hash'] !== $transaction['bankHash']) {
                        $logger->error('[BlockHelper] [extractBlock] Bank Hash ERROR >> '. $_block['height']);
                        $logger->error('[BlockHelper] [extractBlock] ' . $wallets[BlockchainConfig::NAME]['hash'] .' <=> ' . $transaction['bankHash']);

                        break 2;
                        // exit();
                    }

                    $transfers = $transaction['transfers'];

                    if (empty($transfers)) {
                        $logger->error('[BlockHelper] [extractBlock] Transfer empty', $transactionsList[$transaction['hash']]);

                        break 2;
                    }

                    $minerAddress = isset($transaction['transfers'][0]['toWalletId'])
                        ? $transaction['transfers'][0]['toWalletId']
                        : $transaction['transfers'][0]['to'];

                    $minerReward = $transaction['transfers'][0]['amount'];
                    $minerTransfer = $transaction['transfers'][0];
                    $minerTransaction = $transactionsList[$transaction['hash']];
                }

                if (!in_array($fromWalletId, $wallets) && !in_array($fromWalletId, $holders)) {
                    $holders[] = $fromWalletId;
                }

                if (!in_array($fromWalletId, $finalHolders)) {
                    $finalHolders[] = $fromWalletId;
                }
            }

            if (empty($minerTransaction)) {
                $logger->error('[BlockHelper] [extractBlock] !! FATAL ERROR !! - No miner transaction');
                continue;
            }

            $minerHash = Pow::hash($minerTransaction['fromWalletId']
                . (string) $_block['height']
                . $_block['merkleRoot']
                . $_block['hash']
                . (string) $_block['createdAt']
                . (string) ($minerTransfer['amount'] + $blockFee)
                . $minerTransaction['hash']
                . (string) $minerTransfer['amount']
                . $minerAddress
                . (string) true);

            $addressBalanceTo[$minerAddress][] = [
                'amount' => $minerTransfer['amount'] + $blockFee,
                'height' => $_block['height'],
                'hash' => $minerHash,
                'transactionHash' => $minerTransaction['hash'],
                'transferHash' => $minerTransfer['hash']
            ];

            if (!empty($blockFee)) {
                $transferInTransaction[] = [
                    'amount' => $blockFee,
                    'height' => $_block['height'],
                    'fromWalletId' => BlockchainConfig::NAME,
                    'toWalletId' => $minerAddress,
                    'transactionHash' => $minerTransaction['hash'],
                    'createdAt' => $minerTransaction['createdAt'],
                ];
            }

            $holdersData = [];

            if (!empty($holders)) {
                $holdersData = $blockchainManager->getBank()->getAddressBalances($holders, true);
            }

            if (!empty($holdersData)) {
                $wallets = array_merge(
                    $holdersData,
                    $wallets
                );
            }

            foreach ($transactions as $transaction) {
                $addressFrom = $transactionsList[$transaction['hash']]['fromWalletId'];

                if (!isset($wallets[$addressFrom]) || $wallets[$addressFrom]['amount'] <= 0 && BlockchainConfig::NAME !== $addressFrom) {
                    $logger->error('[BlockHelper] [extractBlock] Invalid amount:' . $wallets[$addressFrom]['amount'] . ' | '. $_block['height'] . ' |  ' . $transaction['hash'] . ' | ' . $addressFrom);
                    continue;
                }

                $doNext = BlockchainConfig::NAME === $addressFrom
                    || isset($wallets[$addressFrom])
                        && (int) $wallets[$addressFrom]['amount'] >= (int) $transaction['amountWithFee']
                        && $wallets[$addressFrom]['amount'] > 0;

                if ($doNext) {
                    if (BlockchainConfig::NAME !== $addressFrom) {
                        $wallets[$addressFrom]['amount'] -= ($transaction['amountWithFee']);
                    }

                    if (!array_key_exists($addressFrom, $addressBalanceFrom)) {
                        $addressBalanceFrom[$addressFrom] = [];
                    }

                    $walletHash = Pow::hash(
                        $transactionsList[$transaction['hash']]['fromWalletId']
                        . (string) $_block['height']
                        . $_block['merkleRoot']
                        . $_block['hash']
                        . (string) $_block['createdAt']
                        . (string) ($transaction['amountWithFee'])
                        . $transaction['hash']
                        . (string) $transaction['coinbase']);

                    $addressBalanceFrom[$addressFrom][] = [
                        'amount' => $transaction['amountWithFee'],
                        'height' => $_block['height'],
                        'hash' => $walletHash,
                        'transactionHash' => $transaction['hash'],
                        'transferHash' => ''
                    ];

                    $wallets[$addressFrom]['hash'] = $walletHash;
                    $wallets[$addressFrom]['height'] = $_block['height'];

                    if (is_string($transaction['transfers'])) {
                        $transfers = (array) json_decode(base64_decode($transaction['transfers']), true);
                    } else {
                        $transfers = $transaction['transfers'];
                    }

                    if (empty($transfers)) {
                        $logger->error('[BlockHelper] [extractBlock] Empty transfer '. $_block['height'] . ' ' . $transaction['hash']);
                        break;
                    }

                    $transfersList = [];

                    foreach ($transfers as $transfer) {
                        $toWalletId = isset($transfer['toWalletId'])
                            ? $transfer['toWalletId']
                            : $transfer['to'];

                        $reference = isset($transfer['walletId'])
                            ? $transfer['walletId']
                            : $transfer['reference'];


                        if (!in_array($toWalletId, $finalHolders)) {
                            $finalHolders[] = $toWalletId;
                        }

                        if (!array_key_exists($toWalletId, $wallets)) {
                            $wallets[$toWalletId] = [
                                'amount' => $transaction['amount'],
                                'transactionHash' => $transaction['hash'],
                                'height' => $_block['height'],
                                'hash' => ''
                            ];
                        } else {
                            $wallets[$toWalletId]['amount'] += $transaction['amount'];
                        }

                        if (!$transactionsList[$transaction['hash']]['coinbase']) {
                            if (!array_key_exists($toWalletId, $addressBalanceTo)) {
                                $addressBalanceTo[$toWalletId] = [];
                            }

                            $bankWalletHash = Pow::hash(
                                $transactionsList[$transaction['hash']]['fromWalletId']
                                . (string) $_block['height']
                                . $_block['merkleRoot']
                                . $_block['hash']
                                . (string) $_block['createdAt']
                                . (string) ($transaction['amount'] + $transaction['fee'])
                                . $transaction['hash']
                                . (string) $transfer['amount']
                                . $toWalletId
                                . (string) $transaction['coinbase']);

                            $addressBalanceTo[$toWalletId][] = [
                                'amount' => $transfer['amount'],
                                'height' => $_block['height'],
                                'hash' => $bankWalletHash,
                                'transactionHash' => $transaction['hash'],
                                'transferHash' => $transfer['hash']

                            ];

                            $wallets[$toWalletId]['hash'] = $bankWalletHash;

                        } else {
                            $wallets[$toWalletId]['hash'] = $minerHash;
                        }

                        $transfersList[$transfer['hash']] = $transfer;
                        $transfersList[$transfer['hash']]['transactionHash'] = $transaction['hash'];
                        $transfersList[$transfer['hash']]['toWalletId'] = $toWalletId;
                        $transfersList[$transfer['hash']]['reference'] = $reference;
                        $transfersList[$transfer['hash']]['fromWalletId'] = $transactionsList[$transaction['hash']]['fromWalletId'];
                        $transfersList[$transfer['hash']]['height'] = $_block['height'];
                        $transfersList[$transfer['hash']]['createdAt'] = $transaction['createdAt'];

                        if (isset($transfersList[$transfer['hash']]['walletId'])) {
                            unset($transfersList[$transfer['hash']]['walletId']);
                        }

                        if (isset($transfersList[$transfer['hash']]['to'])) {
                            unset($transfersList[$transfer['hash']]['to']);
                        }

                        $transferInTransaction[$transfer['hash']] = $transfersList[$transfer['hash']];
                    }

                    $jsonTranfserList = $transfersList;
                    $jsonTranfserList = $jsonTranfserList
                        ? $jsonTranfserList
                        : [];

                    $transactionsList[$transaction['hash']]['transfers'] = $jsonTranfserList;

                    if (isset($transaction['toDo'])) {
                        $toDos = $transaction['toDo'];

                        foreach ($toDos as $toDo) {
                            if (isset($toDo['hash'])) {
                                $_todo = json_encode($toDo);

                                $transactionsList[$transaction['hash']]['url'] = $toDo['name'];
                                $transactionsList[$transaction['hash']]['urlAction'] = $toDo['action'];

                                $keyName = $toDo['hash'] . '-' . md5($_todo) . '-' . $transaction['hash'];

                                self::extractDomain([
                                    'hash' => $toDo['hash'],
                                    'keyName' => $keyName,
                                    'command' => $_todo,
                                    'height' => $_block['height'],
                                    'amount' => $transaction['amount'],
                                    'transactionHash' => $transaction['hash'],
                                    'ownerAddress' => $transactionsList[$transaction['hash']]['fromWalletId'],
                                    'ownerPublicKey' => $transaction['publicKey'],
                                    'createdAt' => time()
                                ]);

                                if (!$hasDomain) {
                                    $hasDomain = true;
                                    $block->setHasDomain($hasDomain);
                                }
                            }
                        }
                    }
                } else {
                    $logger->error("[BlockHelper] [extractBlock] Invalid amount spent: address => " . $addressFrom . " | amount => " . $transaction['amount'] . " | Height => " . $block['height']);
                }
            }

            $blocksToSave[] = $block;
        }

        if (!empty($transactionsList)) {
            $blockchainManager->getTransaction()->bulkSave($transactionsList);
        }

        if (!empty($transferInTransaction)) {
            $blockchainManager->getTransfer()->bulkSave($transferInTransaction);
        }

        $finalUpdateWallets = [];
        $finalHoldersData = [];

        if (!empty($finalHolders)) {
            $finalHoldersData = $blockchainManager->getBank()->getAddressBalances($finalHolders, true);
        }

        foreach ($addressBalanceTo as $address => $trans) {
            $amount = 0;
            $height = 0;
            $hash = '';

            foreach ($trans as $data) {
                $amount += $data['amount'];
                $height = $data['height'];
                $hash = $data['hash'];
                $transactionHash = $data['transactionHash'];
                $transferHash = $data['transferHash'];

                if (isset($data['blockFee'])) {
                    $amount += $data['blockFee'];
                }
            }

            if ($amount) {
                if (!isset($finalHoldersData[$address])) {
                    $finalHoldersData[$address] = [];
                    $finalHoldersData[$address]['amount'] = 0;
                }

                $finalHoldersData[$address]['amount'] += $amount;
                $finalHoldersData[$address]['height'] = $height;
                $finalHoldersData[$address]['hash'] = $hash;
                $finalHoldersData[$address]['address'] = $address;
                $finalHoldersData[$address]['transactionHash'] = $transactionHash;
                $finalHoldersData[$address]['transferHash'] = $transferHash;
            }
        }

        foreach ($addressBalanceFrom as $address => $trans) {
            $amount = 0;
            $height = 0;
            $hash = '';

            foreach ($trans as $data) {
                $amount += $data['amount'];
                $height = $data['height'];
                $hash = $data['hash'];
                $transactionHash = $data['transactionHash'];
                $transferHash = $data['transferHash'];
            }

            if ($amount) {
                if (!isset($finalHoldersData[$address])) {
                    $finalHoldersData[$address] = [];
                    $finalHoldersData[$address]['amount'] = 0;
                }

                $finalHoldersData[$address]['amount'] -= $amount;
                $finalHoldersData[$address]['height'] = $height;
                $finalHoldersData[$address]['hash'] = $hash;
                $finalHoldersData[$address]['address'] = $address;
                $finalHoldersData[$address]['transactionHash'] = $transactionHash;
                $finalHoldersData[$address]['transferHash'] = $transferHash;
            }
        }

        foreach ($finalHoldersData as $address => $bank) {
            $blockchainManager->getBank()->delete($address);
        }

        $blockchainManager->getBank()->bulkSave($finalHoldersData);

        if (!$resetMode) {
            $blockchainManager->getBlock()->bulkSave($blocksToSave);
        }

        $range = 100;

        var_dump("LastBlockHeight => $lastBlockHeight *------- ");
        while(!empty($blocks = $blockchainManager->getBlock()->range($lastBlockHeight - 1, $range, 'height', 'asc', ' WHERE hasDomain = 1 '))) {
            var_dump(' ------* ' . count($blocks) . " | $lastBlockHeight *------- ");

            $lastBlockHeight += $range;
        }

        return count($blocksToSave);
    }

    /**
     * @param      array        $todo
     * @param      null|string  $database
     *
     * @return     bool
     */
    public static function extractDomain(array $todo, ?string $database = null): bool
    {
        $blockchainManager = new BlockchainManager($database);

        $logger = (LoggerService::getInstance())->getLogger();

        $command = (array) json_decode($todo['command']);

        if (!isset($command['name'])) {
            $logger->error($todo['hash'] . ' - $command[\'name\'] not found');
            return false;
        }

        if (!isset($command['action'])) {
            $logger->error($todo['hash'] . ' - $command[\'action\'] not found');
            return false;
        }

        if (!isset($command['signature'])) {
            $logger->error($todo['hash'] . ' - $command[\'signature\'] not found');
            return false;
        }

        if (!isset($todo['transactionHash'])) {
            $logger->error($todo['hash'] . ' - $command[\'transactionHash\'] not found');
            return false;
        }

        if (!isset($todo['ownerAddress'])) {
            $logger->error($todo['hash'] . ' - $command[\'ownerAddress\'] not found');
            return false;
        }

        if (!isset($todo['ownerPublicKey'])) {
            $logger->error($todo['hash'] . ' - $command[\'ownerPublicKey\'] not found');
            return false;
        }

        if (!isset($todo['height'])) {
            $logger->error($todo['hash'] . ' - $command[\'height\'] not found');
            return false;
        }

        if (!isset($todo['keyName'])) {
            $logger->error($todo['hash'] . ' - $command[\'keyName\'] not found');
            return false;
        }

        $exec = [
            'hash' => $todo['hash'],
            'height' => $todo['height'],
            'url' => strtolower($command['name']),
            'ownerAddress' => $todo['ownerAddress'],
            'ownerPublicKey' => $todo['ownerPublicKey'],
            'signature' => $command['signature'],
            'transactionHash' => $todo['transactionHash'],
        ];

        $errorAmount = false;
        switch ($command['action']) {
            case 'create':
                switch ((int) $todo['amount']) {
                    case BlockchainConfig::WEB_COST_ONE_MONTH:
                        $exec['heightEnd'] = $exec['height'] + BlockchainConfig::WEB_COST_UNIT_BLOCKS;
                        break;
                    case BlockchainConfig::WEB_COST_THREE_MONTH:
                        $exec['heightEnd'] = $exec['height'] + (BlockchainConfig::WEB_COST_UNIT_BLOCKS * 3);
                        break;
                    case BlockchainConfig::WEB_COST_SIX_MONTH:
                        $exec['heightEnd'] = $exec['height'] + (BlockchainConfig::WEB_COST_UNIT_BLOCKS * 6);
                        break;

                    default:
                        $errorAmount = true;
                        break;
                }

                if ($errorAmount) {
                    $logger->error('[ERROR] [create] Bad action amount: ' . $todo['hash']);
                    return false;
                }

                if (!$blockchainManager->getDomain()->exists($command['name'], 'url')) {
                    $blockchainManager->getDomain()->insert($exec);
                    $logger->info('[SUCCESS] [create] ' . $exec['url']);
                } else {
                    $logger->error('[ERROR] [create] already exists ' . $exec['url']);
                }


                break;

            case 'renew':
                switch ((int) $todo['amount']) {
                    case BlockchainConfig::WEB_COST_ONE_MONTH:
                        $exec['heightEnd'] = $exec['height'] + BlockchainConfig::WEB_COST_UNIT_BLOCKS;
                        break;
                    case BlockchainConfig::WEB_COST_THREE_MONTH:
                        $exec['heightEnd'] = $exec['height'] + (BlockchainConfig::WEB_COST_UNIT_BLOCKS * 3);
                        break;
                    case BlockchainConfig::WEB_COST_SIX_MONTH:
                        $exec['heightEnd'] = $exec['height'] + (BlockchainConfig::WEB_COST_UNIT_BLOCKS * 6);
                        break;

                    default:
                        $errorAmount = true;
                        break;
                }

                if ($errorAmount) {
                    $logger->error('[ERROR] [create] Bad action amount: ' . $todo['hash']);
                    return false;
                }

                if ($errorAmount) {
                    $logger->error('[ERROR] [create] Bad action amount: ' . $todo['hash']);
                    return false;
                }

                if (!$blockchainManager->getDomain()->exists($command['name'], 'url')) {
                    $logger->error('[ERROR] [renew] not found ' . $exec['url']);
                    return false;
                }

                $website = $blockchainManager->getDomain()->selectFisrt($command['name'], 'url');

                if (empty($website)) {
                    $logger->error('[ERROR] Domain not found: ' . $exec['url']);
                    return false;
                }

                $websiteSource = $website->getDataAsArray();

                switch ((int) $todo['amount']) {
                    case BlockchainConfig::WEB_COST_ONE_MONTH:
                        $exec['heightEnd'] = $websiteSource['heightEnd']  + BlockchainConfig::WEB_COST_UNIT_BLOCKS;
                        break;
                    case BlockchainConfig::WEB_COST_THREE_MONTH:
                        $exec['heightEnd'] = $websiteSource['heightEnd'] + (BlockchainConfig::WEB_COST_UNIT_BLOCKS * 3);
                        break;
                    case BlockchainConfig::WEB_COST_SIX_MONTH:
                        $exec['heightEnd'] = $websiteSource['heightEnd'] + (BlockchainConfig::WEB_COST_UNIT_BLOCKS * 6);
                        break;

                    default:
                        $errorAmount = true;
                        break;
                }

                $upExec = [
                    'heightEnd' => $exec['heightEnd'],
                ];

                $updateDomain = $blockchainManager->getDomain()->update($exec['url'], $upExec, 'url');
                if (isset($updateDomain['error'])) {
                    $logger->error('[ERROR] [$updateDomain] : ' . $todo['hash'] . ' | ' . $exec['url']);
                    return false;
                }

                $logger->info('[SUCCESS] [renew] ' . $exec['url']);
                break;

            case 'update':
                if((int) $todo['amount'] !== BlockchainConfig::WEB_COST_UPDATE) {
                    $logger->error('[ERROR] [update] Bad action amount: ' . $todo['hash'] . ' | Given: ' . $todo['amount'] . ' | Excepted: ' . BlockchainConfig::WEB_COST_UPDATE);
                    return false;
                }

                if (!$blockchainManager->getDomain()->exists($exec['url'], 'url')) {
                    $logger->error('[ERROR] [update] not found ' . $exec['url']);
                    return false;
                }

                $domain = $blockchainManager->getDomain()->selectFisrt($exec['url'], 'url');

                if (empty($domain)) {
                    $logger->error('[ERROR] Domain not found: ' . $exec['url']);
                    $blockchainManager->getTodo()->delete($todo['keyName'], 'keyName');

                    return false;
                } else {
                    $domainSource = $domain->getDataAsArray();
                    $exec = [
                        'hash' => $domainSource['hash'],
                        'url' => strtolower($domainSource['url']),
                        'body' => base64_encode(json_encode($command['data'])),
                        'ownerAddress' => $domainSource['ownerAddress'],
                        'ownerPublicKey' => $domainSource['ownerPublicKey'],
                        'signature' => $command['signature'],
                        'height' => $todo['height'],
                        'transactionHash' => $todo['transactionHash'],
                    ];

                    if ($blockchainManager->getWebsite()->exists($domainSource['url'], 'url')) {
                        $blockchainManager->getWebsite()->update($domainSource['url'], $exec, 'url');
                    } else {
                        $blockchainManager->getWebsite()->insert($exec);
                    }

                    $logger->info('[SUCCESS] [update] ' . $exec['url']);
                    $blockchainManager->getTodo()->delete($todo['keyName'], 'keyName');
                }
                break;

            case 'delete':
                if((int) $todo['amount'] !== BlockchainConfig::WEB_COST_DELETE) {
                    $logger->error('[ERROR] [delete] Bad action amount: ' . $todo['hash'] . ' | ' . $exec['url']);
                    return false;
                }

                if (!$blockchainManager->getDomain()->exists($exec['url'], 'url')) {
                    $logger->error('[ERROR] [update] not found ' . $exec['url']);
                    return false;
                }

                $blockchainManager->getDomain()->delete($exec['url'], 'url');
                $blockchainManager->getWebsite()->delete($exec['url'], 'url');

                $logger->info('[SUCCESS] [delete] ' . $exec['url']);
                break;
        }

        $blockchainManager->getTodo()->delete($todo['keyName'], 'keyName');

        return true;
    }

    /**
     * @param      int         $height
     * @param      string      $previousHash
     * @param      int         $createdAt
     * @param      string      $data
     * @param      int         $difficulty
     * @param      string      $nonce
     * @param      string      $merkleRoot
     *
     * @return     string
     */
    public static function calculateHash(int $height, string $previousHash, int $createdAt, string $data, int $difficulty, string $nonce, string $merkleRoot): string
    {
        $configHash = BlockchainConfig::getHash();

        return Pow::hash($merkleRoot.$configHash.$height.$previousHash.$createdAt.$difficulty.$nonce);
    }

    /**
     * @param      array   $block
     *
     * @return     string
     */
    public static function calculateHashFromArray(array $block): string
    {
        return self::calculateHash(
            $block['height'],
            $block['previousHash'],
            $block['createdAt'],
            (string) $block['data'],
            (int) $block['difficulty'],
            (string) $block['nonce'],
            (string) $block['merkleRoot']
        );
    }

    /**
     * @param      string      $prefix
     * @param      bool        $showGenensisAndExit
     *
     * @return     BlockModel
     */
    public static function generateGenesisBlock(string $prefix = BlockchainConfig::NAME, $showGenensisAndExit = false): BlockModel
    {
        $genesisTransaction = TransactionHelper::generateGenesisTansaction(
            BlockchainConfig::GENESIS_MINER_ADDRESS,
            BlockchainConfig::GENESIS_MINER_REWARD,
            BlockchainConfig::GENESIS_DATE,
            $prefix
        );

        $transactions = [
            $genesisTransaction->getInfos()
        ];

        $merkelRoot = MerkleTree::getRoot($transactions);

        $genesisBlock = new BlockModel([
            'height' => 0,
            'hash' => BlockchainConfig::GENESIS_BLOCK_HASH,
            'previousHash' => '86A10E30758FF2D10057256F10CF7DFA5B0E2703DC35FE9C3E49942233BD943B',
            'createdAt' => (new \DateTimeImmutable(BlockchainConfig::GENESIS_DATE))->getTimestamp(),
            'data' => $transactions,
            'difficulty' => 1,
            'nonce' => 0,
            'countTransaction' => 1,
            'countTotalTransaction' => 1,
        ]);

        $genesisBlock->setMerkelRoot($merkelRoot);
        $genesisBlockHash = self::calculateHashFromArray($genesisBlock->getInfos());

        if ($showGenensisAndExit) {
            echo PHP_EOL . 'Genesis hash: ' . $genesisBlockHash . PHP_EOL;
            echo 'Config hash: ' . BlockchainConfig::getHash() . PHP_EOL;
            echo 'Config: '. PHP_EOL;
            foreach (BlockchainConfig::CONFIG as $key => $value) {
                echo '----|  ' . $key . ' => ' . $value . PHP_EOL;
            }
            exit();
        }

        return $genesisBlock;
    }

    /**
     * @param      null|string       $string
     * @param      bool         $encode
     *
     * @return     string
     */
    public static function transpile(?string $string, bool $encode = true): string
    {
        if (null === $string) {
            return '';
        }

        $keys = [
            'aWNLZXkiOiIiLCJzaWduYXR1cmUiOiIifV0=' => '#',
            'W3siaGFzaCI6I' => '*',
            'iLCJmcm9tIjoiaW5lc2NvaW4iLCJ0cmFuc2ZlcnMiOiJXM3NpZEc4aU9pSXdlRVZEUVVZelJXTXlOelV3TmtVeVpUY3hPR1V6WlRSa1ptSXpZVFpDTVRBMU1qZzBZa0pHTWtVaUxDSmhiVzkxYm5RaU9qRXdNREF3TURBd01EQXdNQ3dpYm05dVkyVWlPaUl6T' => '!',
            'TXpFek5UTTRN' => '&',
            'Jc0luZGhiR3hsZEVsa0lqb2lJbjFkIiwidG9EbyI6IlcxMD0iLCJ0b0RvSGFzaCI6IiIsImFtb3VudCI6MTAwMDAwMDAwMDAwLCJhbW91bnRXaXRoRmVlIjoxMDAwMDAwMDAwMDAsImZlZSI6MCwiY29pbmJhc2UiOnRydWUsImNyZWF0ZWRBdCI6MTU4' => ')',
            'wicHVibGljS2V5IjoiIiwic2lnbmF0dXJlIjoiIn1d' => '(',
            'b364cdc9fdd0a14eaefca865630b091c' => '_',
            'iLCJjb25maWdIYXNoIjoiYjM2NGNkYzlmZGQwYTE0ZWFlZmNhODY1NjMwYjA5MWMiLCJiYW5rSGFzaCI6I' => '-',
            'pTENKb1lYTm9Jam9p' => '@'

        ];

        return $encode
            ? str_replace(array_keys($keys), array_values($keys), $string)
            : str_replace(array_values($keys), array_keys($keys), $string);
    }

    /**
     * @param      array
     *
     * @return     array
     */
    public static  function compress(array $block): array
    {
        $compressed[0] = $block['difficulty'];
        $compressed[1] = $block['nonce'];
        $compressed[2] = $block['height'];
        $compressed[3] = $block['cumulativeDifficulty'];
        $compressed[4] = $block['previousHash'];
        $compressed[5] = self::transpile($block['configHash']);
        $compressed[6] = self::transpile($block['data']);
        $compressed[7] = $block['hash'];
        $compressed[8] = $block['createdAt'];
        $compressed[9] = $block['merkleRoot'];
        $compressed[10] = $block['countTotalTransaction'];
        $compressed[11] = $block['countTransaction'];

        return $compressed;
    }

    /**
     * @param      array  $compressed
     * @param      bool   $decode
     *
     * @return     array
     */
    public static function decompress(array $compressed, bool$decode = false): array
    {
        if (!is_array($compressed) || count($compressed) < 12) {
            $this->logger->error('!!!! ----> !!!! Error into decompression');
            return null;
        }

        for($i = 0; $i < 12; $i++) {
            if(!array_key_exists($i, $compressed)) {
                $this->logger->error('!!!! ----> !!!! Error into decompression position ' . $i);
                return null;
            }
        }

        $blockInfos['difficulty'] = $compressed[0];
        $blockInfos['nonce'] = $compressed[1];
        $blockInfos['height'] = $compressed[2];
        $blockInfos['cumulativeDifficulty'] = $compressed[3];
        $blockInfos['previousHash'] = $compressed[4];
        $blockInfos['configHash'] = $decode ? self::transpile($compressed[5], false) : $compressed[5];
        $blockInfos['data'] = $decode ? self::transpile($compressed[6], false) : $compressed[6];
        $blockInfos['hash'] = $compressed[7];
        $blockInfos['createdAt'] = $compressed[8];
        $blockInfos['merkleRoot'] = $compressed[9];
        $blockInfos['countTotalTransaction'] = $compressed[10];
        $blockInfos['countTransaction'] = $compressed[11];

        return $blockInfos;
    }

    /**
     * @param      array
     *
     * @return     \Iterator
     */
    public static function bulkDecompress(array $blocks): \Iterator
    {
        foreach ($blocks as $block) {
            yield new Block(self::decompress($block));
        }
    }

    /**
     * @param      array   $blockArray
     * @param      string  $transactionHash
     *
     * @return     array
     */
    public static function cleanTodoFromBlock(array $blockArray, string $transactionHash): array
    {
        if (!isset($transactionHash)) {
            return $blockArray;
        }

        $block = self::fromArrayToBlockModel($blockArray);
        $transactions = $block->getDataJson();


        $transactionHash = $transactionHash;

        foreach ($transactions as $k => $transaction) {
            if ($transaction['hash'] === $transactionHash) {
                $transactions[$k]['toDo'] = 'W10=';
            }

            $transactions[$k]['transfers'] = base64_encode(json_encode($transactions[$k]['transfers']));
        }

        $transactions = base64_encode(json_encode($transactions));

        $block->set([
            'data' => $transactions
        ]);

        return $block->getInfos();
    }

    // public function clean

    /**
     * @param      array       $block
     *
     * @return     BlockModel
     */
    public static function fromArrayToBlockModel(array $block): BlockModel
    {
        if (!$block) {
            return null;
        }

        $height = (int) $block['height'];
        $hash = (string) $block['hash'];
        $merkleRoot = (string) $block['merkleRoot'];
        $previousHash = (string) $block['previousHash'];
        $createdAt = $block['createdAt'];
        $dataBase64 = BlockModel::getDataEncoded($block['data']);
        $difficulty = (int) $block['difficulty'];
        $nonce = (int) $block['nonce'];

        $data = BlockModel::getDataDecoded($dataBase64);

        $totalTransaction = isset($block['countTransaction'])
            ? (int) $block['countTransaction']
            : count($data);

        $cumulativeDifficulty = isset($block['cumulativeDifficulty'])
            ? (int) $block['cumulativeDifficulty']
            : BlockchainConfig::MIN_DIFFICULTY;

        $previousCumulativeDifficulty = $cumulativeDifficulty - ( 2 ** $difficulty);

        if (isset($block['previousCumulativeDifficulty'])) {
            $previousCumulativeDifficulty = $block['previousCumulativeDifficulty'];
        }

        $countTotalTransaction = $block['previousBlockTotalTxCount'] + $totalTransaction;

        $selfBlock = new BlockModel([
            'height' => $height,
            'hash' => $hash,
            'previousHash' => $previousHash,
            'createdAt' => $createdAt,
            'data' => $dataBase64,
            'difficulty' => $difficulty,
            'nonce' => $nonce,
            'cumulativeDifficulty' => $cumulativeDifficulty,
            'previousCumulativeDifficulty' => $previousCumulativeDifficulty,
            'countTransaction' => $totalTransaction,
            'countTotalTransaction' => $countTotalTransaction,
        ]);

        return $selfBlock->setMerkelRoot($merkleRoot);
    }
}
