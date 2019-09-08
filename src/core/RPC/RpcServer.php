<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\RPC;

use React\Http\Response;
use Psr\Http\Message\ServerRequestInterface;
use Inescoin\Node;

final class RpcServer
{
    private $node;

    private $cache;

    public function __construct(Node $node)
    {
        $this->node = $node;
        $this->cache = Cache::getInstance($this->node->getBlockchain()->getPrefix());
    }

    public function __invoke(ServerRequestInterface $request): Response
    {
        $uri = trim($request->getUri()->getPath(), '/');

        switch ($request->getMethod()) {
            case 'GET':
                $key = md5('GET-' . $request->getUri()->getPath());
                $params = $request->getQueryParams();
                switch ($uri) {
                    case 'top-block':
                        $response = $this->cache->getCache($key);
                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $response = $this->node->getBlockchain()->getLastBlock()->getInfos();
                        $this->cache->setCache($key, $response);
                        return new JsonResponse($response);

                    case 'top-height':
                        $response = $this->cache->getCache($key);
                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $response = [
                            'height' => $this->node->getBlockchain()->getTopHeight()
                        ];
                        $this->cache->setCache($key, $response);
                        return new JsonResponse($response);

                    case 'status':
                        $response = $this->cache->getCache($key);
                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $response = [
                            'height' => $this->node->getBlockchain()->getTopHeight(),
                            'topKnowHeight' => $this->node->getBlockchain()->getTopKnowHeight(),
                            'cumulativeDifficulty' => $this->node->getBlockchain()->getTopCumulativeDifficulty(),
                            'totalTransfer' => $this->node->getBlockchain()->getTotalTransfer(),
                            'totalTransaction' => $this->node->getBlockchain()->getTotalTransaction(),
                            'bankAmount' => $this->node->getBlockchain()->getBankAmount(),
                            'localPeerConfig' => $this->node->getLocalPeerConfig(),
                            'isSync' => $this->node->getBlockchain()->isSync(),
                            'peersPersistence' => $this->node->getPeersPersistence(),
                            'peers' => $this->node->getPeers(),
                        ];
                        $this->cache->setCache($key, $response);
                        return new JsonResponse($response);

                    case 'messages':
                        $response = $this->cache->getCache($key);
                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $addresses = isset($params['addresses']) ? $params['addresses'] : null;
                        $response = $this->node->getMessages($addresses);

                        $this->cache->setCache($key, $response);
                        return new JsonResponse($response);

                    case 'mempool':
                        $response = $this->cache->getCache($key);
                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $response = $this->node->getMemoryPool();

                        $this->cache->setCache($key, $response);
                        return new JsonResponse($response);

                    case 'peers':
                        $response = $this->cache->getCache($key);
                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $response = $this->node->getPeers();

                        $this->cache->setCache($key, $response);
                        return new JsonResponse($response);

                    case 'public-key':

                        $response = $this->cache->getCache($key);
                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $response = ['publicKey' => $this->node->getPublicKey()];

                        $this->cache->setCache($key, $response);
                        return new JsonResponse($response);

                    // case 'generate-keys':
                    //     return new JsonResponse($this->node->getBlockchain()->generateWallet());

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

            case 'POST':
                $data = (array) json_decode($request->getBody()->getContents(), true);
                $key = md5('POST-' . $request->getUri()->getPath() . '-' . serialize($data));
                switch ($uri) {
                    case 'get-blocks':
                        $response = $this->cache->getCache($key);
                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $page = isset($data['page']) ? (int) $data['page'] : 1;
                        $limit = isset($data['limit']) ? (int) $data['limit'] : 100;
                        $response = $this->node->getLastBlocks($limit, $page);

                        $this->cache->setCache($key, $response);
                        return new JsonResponse($response);

                    case 'get-block-by-height':
                        $height = isset($data['blockHeight']) ? $data['blockHeight'] : null;
                        if (null === $height) {
                            return new JsonResponse([]);
                        }

                        $response = $this->cache->getCache($key);
                        if ($response && $response[0]) {
                            return new JsonResponse($response[0]);
                        }

                        $block = $this->node->getBlockchain()->getBlockByHeight($height);
                        $response = [$block->getJsonInfos()];
                        $this->cache->setCache($key, $response);
                        return new JsonResponse($block->getJsonInfos());

                    case 'get-block-by-hash':
                        $hash = isset($data['blockHash']) ? $data['blockHash'] : null;
                        if (null === $hash) {
                            return new JsonResponse([]);
                        }

                        $response = $this->cache->getCache($key);
                        if ($response && isset($response[0])) {
                            return new JsonResponse($response[0]);
                        }

                        $block = $this->node->getBlockchain()->getBlockByHash($hash);
                        $response = [$block];
                        $this->cache->setCache($key, $response);
                        return new JsonResponse($block);

                    case 'get-transaction-by-hash':
                        $hash = isset($data['transactionHash']) ? $data['transactionHash'] : null;
                        if (null === $hash) {
                            return new JsonResponse([]);
                        }

                        $response = $this->cache->getCache($key);
                        if ($response && isset($response[0])) {
                            return new JsonResponse($response[0]);
                        }

                        $transaction = $this->node->getBlockchain()->getTransactionByHash($hash);
                        $response = [$transaction];
                        $this->cache->setCache($key, $response);
                        return new JsonResponse($transaction);

                    case 'get-transfer-by-hash':
                        $hash = isset($data['transferHash']) ? $data['transferHash'] : null;
                        if (null === $hash) {
                            return new JsonResponse([]);
                        }

                        $response = $this->cache->getCache($key);
                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $response = [
                            'transfer' => $this->node->getBlockchain()->getTransferByHash($hash),
                            'transaction' => []
                        ];

                        if (!empty($response['transfer']) && isset($response['transfer']['transactionHash'])) {
                            $response['transaction'] = $this->node->getBlockchain()->getTransactionByHash($response['transfer']['transactionHash']);
                        }

                        $this->cache->setCache($key, $response);
                        return new JsonResponse($response);

                    case 'get-wallet-address-infos':
                        $page = isset($data['page']) ? (int) $data['page'] : 1;
                        $walletAddress = isset($data['walletAddress']) ? $data['walletAddress'] : null;
                        if (null === $walletAddress) {
                            return new JsonResponse([]);
                        }

                        $response = $this->cache->getCache($key);
                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $page = isset($data['page']) ? (int) $data['page'] : 1;
                        $response = $this->node->getBlockchain()->getWalletAddressInfos($walletAddress, $page);

                        $this->cache->setCache($key, $response);
                        return new JsonResponse($response);

                    case 'get-wallet-addresses-infos':
                        $walletAddresses = isset($data['walletAddresses']) ? $data['walletAddresses'] : null;
                        if (null === $walletAddresses) {
                            return new JsonResponse([]);
                        }

                        $addresses = explode(',', $walletAddresses);
                        if (count($addresses) > 20) {
                            return new JsonResponse([]);
                        }

                        $response = $this->cache->getCache($key);
                        if ($response) {
                            return new JsonResponse($response);
                        }

                        $response = $this->node->getBlockchain()->getAddressBalances($addresses);

                        $this->cache->setCache($key, $response);
                        return new JsonResponse($response);


                    case 'get-messages':
                        $id = isset($data['id']) ? $data['id'] : null;
                        return new JsonResponse($this->node->getMessages($id));

                    case 'getBlockTemplate':
                        return new JsonResponse($this->node->getBlockTemplate($data));

                    case 'submitBlockHash':
                        $response = $this->node->submitBlockHash($data);
                        if (isset($response['done'])) {
                            $this->node->broadcastMinedBlock();
                        }

                        return new JsonResponse([$response]);

                    case 'message':
                        return new JsonResponse([$this->node->checkFromMessagePool($data)]);

                    case 'transaction':
                        return new JsonResponse([$this->node->checkFromMemoryPool($data)]);

                    // case 'metadata':
                    //     return new JsonResponse([$this->node->checkFromMetadataPool($data)]);

                    // case 'data':
                    //     return new JsonResponse([$this->node->checkFromDataPool($data)]);

                    default:
                        return new Response(404);
                }

                break;
        }
    }
}
