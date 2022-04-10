<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\RPC;

use Inescoin\BlockchainConfig;
use Inescoin\Helper\PKI;
use Inescoin\Manager\BlockchainManager;
use Inescoin\Node\Node;
use Inescoin\RPC\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

final class RpcServer
{
    /**
     * @var Node
     */
    private $node;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var BlockchainManager
     */
    private $blockchainManager;

    public function __construct(Node $node)
    {
        $this->node = $node;
        $this->cache = Cache::getInstance($node->getBlockchainService()->getPrefix());
        $this->blockchainManager = $this->node->getBlockchainService()->getBlockchainManager();
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $uri = trim($request->getUri()->getPath(), '/');

        switch ($request->getMethod()) {
            case 'GET':
                $key = md5('GET-' . $request->getUri()->getPath());
                $params = $request->getQueryParams();

                switch ($uri) {
                    case 'top-block':
                        $response = $this->cache->getCache($key);

                        if (null !== $response) {
                            return new JsonResponse($response);
                        }

                        $response = $this->blockchainManager
                            ->getBlock()
                            ->lastAsArray();

                        $this->cache->setCache($key, $response);

                        return new JsonResponse($response);

                    case 'top-height':
                        $response = $this->cache->getCache($key);

                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $response = [
                            'height' => $this->blockchainManager
                                ->getBlock()
                                ->last()
                                ->getHeight()
                        ];

                        $this->cache->setCache($key, $response);

                        return new JsonResponse($response);

                    case 'status':
                        $response = $this->cache->getCache($key);

                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $lastBlock = $this->blockchainManager
                            ->getBlock()
                            ->last();

                        $response = [
                            'height' => $lastBlock->getHeight(),
                            // 'topKnowHeight' => $this->node->getBlockchain()->getTopKnowHeight(),
                            'cumulativeDifficulty' => $lastBlock->getCumulativeDifficulty(),
                            'totalDomains' => $this->blockchainManager->getDomain()->count(),
                            'totalTransfers' => $this->blockchainManager->getTransfer()->count(),
                            'totalTransactions' => $this->blockchainManager->getTransaction()->count(),
                            'bankAdresses' =>  $this->blockchainManager->getBank()->count(),
                            'bankAmount' =>  $this->blockchainManager->getBank()->amount(),
                            'bankValid' =>  $this->blockchainManager->getBank()->isValid(),
                            'localPeerConfig' => $this->node->getLocalPeerConfig(),
                            'peersPersistence' => $this->node->getPeersPersistence(),
                            'configHash' => BlockchainConfig::getHash(),
                            // 'isSync' => $this->node->getBlockchain()->isSync(),
                            // 'peers' => $this->node->getPeers(),
                        ];

                        $this->cache->setCache($key, $response);

                        return new JsonResponse($response);

                    case 'messages':
                        $walletAddresses = isset($params['walletAddresses']) ? $params['walletAddresses'] : null;

                        var_dump($walletAddresses);

                        if (null === $walletAddresses || !ctype_alnum(str_replace(['x', ','], ['', ''], $walletAddresses))) {
                            return new Response(404);
                        }

                        $addresses = explode(',', $walletAddresses);

                        if (count($addresses) > 100) {
                            return new JsonResponse([]);
                        }

                        $wallets = $this->cache->getCache($key);

                        if ($wallets) {
                            return new JsonResponse($wallets);
                        }

                        $response['messages'] = $this->blockchainManager->getMessage()->selectHistory($addresses);

                        $this->cache->setCache($key, $response);

                        return new JsonResponse($response);

                    case 'mempool':
                        $response = $this->cache->getCache($key);

                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $response = [
                            'count' => $this->blockchainManager->getTransactionPool()->count(),
                            'transactions' => $this->blockchainManager->getTransactionPool()->rangeAsArray(),
                        ];

                        $this->cache->setCache($key, $response);
                        return new JsonResponse($response);

                    // case 'peers':
                    //     $response = $this->cache->getCache($key);
                    //     if ($response) {
                    //         return new JsonResponse($response);
                    //     }

                    //     $response = $this->node->getBlockchain()->es->peerService()->getRemoteAddresses();

                    //     $this->cache->setCache($key, $response);
                    //     return new JsonResponse($response);

                    case 'public-key':
                        $response = $this->cache->getCache($key);

                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $response = ['publicKey' => $this->node->getPublicKey()];

                        $this->cache->setCache($key, $response);

                        return new JsonResponse($response);

                    case 'generate-keys':
                        return new JsonResponse($this->node->generateWallet());

                    // case 'generate-keys-test':
                    //     return new JsonResponse([
                    //         'bob' => $this->node->getBlockchain()->generateWallet(),
                    //         'alice' => $this->node->getBlockchain()->generateWallet()
                    //     ]);

                    // case 'generate-certificat':
                    //     return new JsonResponse(PKI::generateCertificat());

                    default:
                        return new Response(404);
                }

                break;
            break;
            // case 'OPTIONS':
            case 'POST':
                $data = (array) json_decode($request->getBody()->getContents(), true);
                $key = md5('POST-' . $request->getUri()->getPath() . '-' . serialize($data));

                switch ($uri) {
                    case 'get-blocks-by-height':
                        $blocks = $this->cache->getCache($key);

                        if ($blocks) {
                            return new JsonResponse($blocks);
                        }

                        $height = isset($data['height']) ? (int) $data['height'] : 0;
                        $limit = isset($data['limit']) ? (int) $data['limit'] : 100;

                        $blocks = $this->blockchainManager
                            ->getBlock()
                            ->rangeAsArray($height, $limit);

                        if (empty($blocks)) {
                            return new Response(404);
                        }

                        $this->cache->setCache($key, $blocks);

                        return new JsonResponse($blocks);

                    case 'get-blocks':
                        $response = $this->cache->getCache($key);

                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $page = isset($data['page']) ? (int) $data['page'] : 1;
                        $limit = isset($data['limit']) ? (int) $data['limit'] : 100;

                        $from = 0;
                        if ($page > 1) {
                            $from = ((int) $page * $limit - 1) - $limit;
                        }

                        $blocks = $this->blockchainManager
                            ->getBlock()
                            ->rangeAsArray($from, $limit, 'height', 'desc');

                        if (empty($blocks)) {
                            return new Response(404);
                        }

                        $this->cache->setCache($key, $blocks)
                        ;
                        return new JsonResponse($blocks);

                    case 'get-block-by-height':
                        $height = isset($data['blockHeight']) ? (int) $data['blockHeight'] : null;

                        if (null === $height) {
                            return new Response(404);
                        }

                        $block = $this->cache->getCache($key);
                        if ($block) {
                            return new JsonResponse($block);
                        }

                        $block = $this->blockchainManager
                            ->getBlock()
                            ->selectFisrtAsArray($height, 'height');

                        if (null === $block) {
                            return new Response(404);
                        }

                        $this->cache->setCache($key, $block);

                        return new JsonResponse($block);

                    case 'get-block-by-hash':
                        $hash = isset($data['blockHash']) ? $data['blockHash'] : null;

                        if (null === $hash || !ctype_alnum($hash)) {
                            return new Response(404);
                        }

                        $block = $this->cache->getCache($key);
                        if ($block) {
                            return new JsonResponse($block);
                        }

                        $block = $this->blockchainManager
                            ->getBlock()
                            ->selectFisrtAsArray($hash, 'hash');

                        if (null === $block) {
                            return new Response(404);
                        }

                        $this->cache->setCache($key, $block);

                        return new JsonResponse($block);

                    case 'get-transaction-by-hash':
                        $hash = isset($data['transactionHash']) ? $data['transactionHash'] : null;

                        if (null === $hash || !ctype_alnum($hash)) {
                            return new Response(404);
                        }

                        $transaction = $this->cache->getCache($key);

                        if ($transaction) {
                            return new JsonResponse($transaction);
                        }

                        $transaction = $this->blockchainManager
                            ->getTransaction()
                            ->selectFisrtAsArray($hash, 'hash');

                        if (null === $transaction) {
                            return new Response(404);
                        }
                        $this->cache->setCache($key, $transaction);

                        return new JsonResponse($transaction);

                    case 'get-transfer-by-hash':
                        $hash = isset($data['transferHash']) ? $data['transferHash'] : null;

                        if (null === $hash || !ctype_alnum($hash)) {
                            return new Response(404);
                        }

                        $response = $this->cache->getCache($key);

                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $transfer = $this->blockchainManager
                            ->getTransfer()
                            ->selectFisrtAsArray($hash, 'hash');

                        if (null === $transfer || !isset($transfer['transactionHash'])) {
                            return new Response(404);
                        }

                        $transaction = $this->blockchainManager
                            ->getTransaction()
                            ->selectFisrtAsArray($transfer['transactionHash'], 'hash');

                        if (null === $transaction) {
                            return new Response(404);
                        }

                        $response = [
                            'transfer' => $transfer,
                            'transaction' => $transaction
                        ];

                        $this->cache->setCache($key, $response);

                        return new JsonResponse($response);

                    case 'get-wallet-address-infos':
                        $page = isset($data['page']) ? (int) $data['page'] : 1;
                        $pageTransferPool = isset($data['pageTransferPool']) ? (int) $data['pageTransferPool'] : 1;
                        $pageDomain = isset($data['pageDomain']) ? (int) $data['pageDomain'] : 1;
                        $walletAddress = isset($data['walletAddress']) ? $data['walletAddress'] : null;

                        if (null === $walletAddress || !ctype_alnum(str_replace('x', 'replace', $walletAddress))) {
                            return new Response(404);
                        }

                        $response = $this->cache->getCache($key);

                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $wallets = $this->blockchainManager
                            ->getBank()
                            ->getAddressBalances($walletAddress, true);

                        if (empty($wallets) && !isset($wallets[$walletAddress])) {
                            $wallets[$walletAddress] = [
                                'address' => $walletAddress,
                                'amount' => 0,
                                'height' => 0,
                                'hash' => ''
                            ];
                        }

                        $transfers = $this->blockchainManager
                            ->getTransfer()
                            ->selectHistory($walletAddress, $page);

                        $countTransfers = $this->blockchainManager
                            ->getTransfer()
                            ->countHistory($walletAddress);

                        $transfersPool = $this->blockchainManager
                            ->getTransferPool()
                            ->selectHistory($walletAddress, $pageTransferPool);

                        $countTransfersPool = $this->blockchainManager
                            ->getTransferPool()
                            ->countHistory($walletAddress);

                        $domains = $this->blockchainManager
                            ->getDomain()
                            ->selectHistory($walletAddress, $pageDomain);

                        $countDomains = $this->blockchainManager
                            ->getDomain()
                            ->countHistory($walletAddress);

                        $response = [
                            'wallet' => $wallets[$walletAddress],
                            'count' => $countTransfers,
                            'transfers' => $transfers,
                            'countPool' => $countTransfersPool,
                            'transfersPool' => $transfersPool,
                            'countDomains' => $countDomains,
                            'domains' => $domains,
                        ];

                        $this->cache->setCache($key, $response);

                        return new JsonResponse($response);

                    case 'get-wallet-addresses-infos':
                        $walletAddresses = isset($data['walletAddresses']) ? $data['walletAddresses'] : null;

                        if (null === $walletAddresses || !ctype_alnum(str_replace(['x', ','], ['', ''], $walletAddresses))) {
                            return new Response(404);
                        }

                        $addresses = explode(',', $walletAddresses);

                        if (count($addresses) > 100) {
                            return new JsonResponse([]);
                        }

                        $wallets = $this->cache->getCache($key);

                        if ($wallets) {
                            return new JsonResponse($wallets);
                        }

                        $wallets = $this->blockchainManager
                            ->getBank()
                            ->getAddressBalances($addresses, true);

                        if (empty($wallets)) {
                            return new Response(404);
                        }

                        $this->cache->setCache($key, $wallets);

                        return new JsonResponse($wallets);

                    case 'get-domain-url':
                        $url = isset($data['url']) ? $data['url'] : null;

                        if (null === $url || !ctype_alnum($url)) {
                            return new Response(404);
                        }

                        $response = $this->cache->getCache($key);

                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $response = $this->blockchainManager->getWebsite()->selectFisrtAsArray($url, 'url');

                        $this->cache->setCache($key, $response);

                        return new JsonResponse($response);

                    case 'get-website-info':
                        $url = isset($data['url']) ? $data['url'] : null;
                        $page = isset($data['page']) ? (int) $data['page'] : 1;

                        if (null === $url || !ctype_alnum($url)) {
                            return new Response(404);
                        }

                        $response = $this->cache->getCache($key);

                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $response = $this->blockchainManager->getDomain()->selectFisrtAsArray($url);
                        $response['transactions'] = $this->blockchainManager
                            ->getTransaction()
                            ->selectHistory($url, $page);

                        $this->cache->setCache($key, $response);
                        return new JsonResponse($response);

                    case 'get-last-domains':
                        $domains = $this->cache->getCache($key);

                        if ($domains) {
                            return new JsonResponse($domains);
                        }

                        $domains = $this->blockchainManager->getDomain()->rangeAsArray(0, 20, 'height', 'desc');

                        $this->cache->setCache($key, $domains);

                        return new JsonResponse($domains);

                    case 'get-wallet-addresses-domain':
                        $page = isset($data['page']) ? (int) $data['page'] : 1;
                        $walletAddresses = isset($data['walletAddresses']) ? $data['walletAddresses'] : null;

                        if (null === $walletAddresses || !ctype_alnum(str_replace(['x', ','], ['', ''], $walletAddresses))) {
                            return new Response(404);
                        }

                        $addresses = explode(',', $walletAddresses);

                        if (count($addresses) > 100) {
                            return new JsonResponse([]);
                        }

                        $response = $this->cache->getCache($key);

                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $domains = $this->blockchainManager
                            ->getDomain()
                            ->selectHistory($addresses, $page);

                        $countDomains = $this->blockchainManager
                            ->getDomain()
                            ->countHistory($addresses);

                        $response = [
                            'countDomains' => $countDomains,
                            'domainList' => $domains,
                        ];

                        $this->cache->setCache($key, $response);

                        return new JsonResponse($response);

                    case 'get-block-template':
                        return new JsonResponse($this->node->getBlockTemplate($data));

                    case 'submit-block-hash':
                        $response = $this->node->submitBlockHash($data);

                        if (isset($response['done'])) {
                            $this->node->broadcastMinedBlock($response['block']);
                        }

                        return new JsonResponse([$response]);

                    case 'message':
                        return new JsonResponse([$this->node->checkFromMessagePool($data)]);

                    case 'transaction':
                        return new JsonResponse([$this->node->checkFromMemoryPool($data)]);

                    default:
                        return new Response(404);
                }

                break;
            break;
        }
    }
}
