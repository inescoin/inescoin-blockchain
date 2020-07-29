# Inescoin Blockchain
## Create your domain name and website into blockchain, with encrypted messenger

* [`Get started`](#Get-started)
    * Installation
    * Start a node
* [`Inescoin Node API`](#Inescoin-Node-API)
    * Node infos
    * Explorer
    * Send transaction
    * Miner
    * Website
* [`Backup blockchain`](#Backup-blockchain)
* [`Docker dev env`](#Docker-dev-env)

# Get started

  1 - Install node with ansible (Ubuntu)

  ```
    git clone https://github.com/inescoin/inescoin-ansible
    
    # Update your /etc/ansible/hosts file  with remote IP
    cd inescoin-ansible && ansible-playbook inescoin.yml
     
    # New systemctrl (/etc/systemd/system/)
        - inescoin-node.service
        - inescoin-sync-node.service
        - inescoin-messenger.service
        - inescoin-web-consumer.service
    
  ```

  2 - Start inescoin node

  ```
    # With service
    systemctrl start inescoin-node.service
    
    # With bin
    cd /opt/
    src/bin/inescoin-node --rpc-bind-port=8087 --p2p-bind-port=3031 --network=MAINNET --prefix=moon --rpc-bind-ip=IP --p2p-bind-ip=IP
    
  ```

  3 - Monitoring
  ```
    journalctl -u inescoin-node.service -f
    journalctl -u inescoin-sync-node.service -f
    journalctl -u inescoin-messenger.service -f
    journalctl -u inescoin-web-consumer.service -f
  ```

**[Back to top](#Get-started)**

# Inescoin Node API

HTTP request to an entry point:
```
http://<ip>:<port>/<uri>
```
where:
* `<ip>` is IPv4 address of `inescoin-node` service. 
* `<port>` is TCP port of `inescoin-node`. By default the service is bound to `8087`.


## Methods

### Node infos

| #   | URI                                                             | Method    | Description                                                |
|-----|-----------------------------------------------------------------|-----------|------------------------------------------------------------|
| 1.  | [`/status`](#status)                                            | GET       | Get node status                                            |
| 2.  | [`/top-block`](#top-block)                                      | GET       | Get top block data                                         |
| 3.  | [`/top-height`](#top-height)                                    | GET       | Get top height.                                            |
| 4.  | [`/public-key`](#public-key)                                    | GET       | Get node public key.                                       |
| 5.  | [`/mempool`](#memory-transactions-pool)                         | GET       | Get memory transactions pool.                              |
| 6.  | [`/peers`](#peers)                                              | GET       | Get node peers list.                                       |

### Explorer

| #   | URI                                                             | Method    | Description                                                |
|-----|-----------------------------------------------------------------|-----------|------------------------------------------------------------|
| 7.  | [`/get-blocks`](#get-blocks)                                    | POST      | Get blocks                                                 |
| 8.  | [`/get-block-by-height`](#get-block-by-height)                  | POST      | Get block by height                                        |
| 9.  | [`/get-block-by-hash`](#get-block-by-hash)                      | POST      | Get block by hash                                          |
| 10. | [`/get-transaction-by-hash`](#get-transaction-by-hash)          | POST      | Get transaction by hash                                    |
| 11. | [`/get-transfer-by-hash`](#get-transfer-by-hash)                | POST      | Get transfer by hash                                       |
| 12. | [`/get-wallet-address-infos`](#get-wallet-address-infos)        | POST      | Get wallet address details                                 |
| 13. | [`/get-wallet-addresses-infos`](#get-wallet-addresses-infos)    | POST      | Get wallet addresses balance                               |
### Send transaction

| #   | URI                                                             | Method    | Description                                                |
|-----|-----------------------------------------------------------------|-----------|------------------------------------------------------------|
| 14. | [`/transaction`](#transaction)                                  | POST      | Send transaction                                           |
### Miner

| #   | URI                                                             | Method    | Description                                                |
|-----|-----------------------------------------------------------------|-----------|------------------------------------------------------------|
| 15. | [`/getBlockTemplate`](#get-block-template)                        | POST      | Get block template with transactions pool                  |
| 16. | [`/submitBlockHash`](#submit-block-hash)                          | POST      | Submit block hash                                          |

### Website
| #   | URI                                                             | Method    | Description                                                |
|-----|-----------------------------------------------------------------|-----------|------------------------------------------------------------|
| 17. | [`/get-domain-url`](#get-domain-url)                            | POST      | Get domain details by url                 |
| 18. | [`/get-website-info`](#get-website-info)                        | POST      | Get website data by url                         |
| 19. | [`/get-wallet-addresses-domain`](#get-wallet-addresses-domain)  | POST      | Get domain details by wallet addresses                        |

**[Back to top](#Get-started)**

## Status
| #   | URI                   | Method    | Description                                                   |
|-----|-----------------------|-----------|---------------------------------------------------------------|
| 1.  | `/status`             | GET       | Get node status . |


Request

```
    http://<ip>:<port>/status # https://node.inescoin.org/status
```

Response

```
{
  "height": 76,
  "topKnowHeight": 76,
  "cumulativeDifficulty": 154,
  "totalTransfer": 80,
  "totalTransaction": 80,
  "bankAmount": 9.000076e+17,
  "localPeerConfig": {
    "host": "188.165.211.215",
    "port": "3031",
    "rpcHost": "188.165.211.215",
    "rpcPort": "8087"
  },
  "isSync": true,
  "peersPersistence": [],
  "peers": []
}
```

**[Back to top](#Get-started)**

## Top block

| #   | URI                   | Method    | Description                                                   |
|-----|-----------------------|-----------|---------------------------------------------------------------|
| 2.  | `/top-block`          | GET       | Get top block data .                                          |

Request

```
    http://<ip>:<port>/top-block # https://node.inescoin.org/top-block
```

Response

```
{
  "difficulty": 20,
  "nonce": 625302,
  "height": 95027,
  "cumulativeDifficulty": 127907397574,
  "previousHash": "00000cc50f9b497d6098100871a6abc1e77beede4696f76afcc0d2b8ab2f836a",
  "configHash": "19924913f08605e99f0a3aeb361b9a00",
  "data": "W3siaGFzaCI6ImFlYTRiODg0YzI2MTFkNGE2NzU0M2YzZDk4MmI3N2M1NWQ1MDc0MmJhZDA5ZjIyYzJiMTUxZWY5YjI2ZmE5YzEiLCJjb25maWdIYXNoIjoiMTk5MjQ5MTNmMDg2MDVlOTlmMGEzYWViMzYxYjlhMDAiLCJiYW5rSGFzaCI6IjNmOGI1NDIyN2UxMjljMDQ1ZDAyNTE3YWUxZDliMzBmODNhNjExMWUyY2NhMzA5ZmNlODUyM2Q1MTJkNzhjOGYiLCJmcm9tIjoiaW5lc2NvaW4iLCJ0cmFuc2ZlcnMiOiJXM3NpZEc4aU9pSXdlRFU1TmpkaE5EQXhOalV3TVRRMk5VTkVPVFV4WVRGbE16azRORVkzTnpKQlprUmxRalV5TURjaUxDSmhiVzkxYm5RaU9qRXdNREF3TURBd01EQXdNQ3dpYm05dVkyVWlPaUl6TXpNd016VXpORE01TXpFek5UTTJNemt6TVRNNE16TXpOak15TXpneVpUTXpNekF6TlRNek5EWXlNamNpTENKb1lYTm9Jam9pWkRaaE5tSXlaV1UwWW1ReE5tTTNZamN6TWpBM05UZzBOelU0WlRBek5URmhNamRqWmpRMVpqVmlPRGMyTkRreU1HRXpPREV4TXpGaVkyWTJZbU01T0NJc0luZGhiR3hsZEVsa0lqb2lJbjFkIiwiYW1vdW50IjoxMDAwMDAwMDAwMDAsImFtb3VudFdpdGhGZWUiOjEwMDAwMDAwMDAwMCwiZmVlIjowLCJjb2luYmFzZSI6dHJ1ZSwiY3JlYXRlZEF0IjoxNTY5MTgzNjI4LCJwdWJsaWNLZXkiOiIiLCJzaWduYXR1cmUiOiIifV0=",
  "hash": "00000e5ede543c617cc369a3bb102646a146f231a94a53e406135d1ee441137a",
  "createdAt": 1569183629,
  "merkleRoot": "35749ec7ec4835a634b5894aa8450a7c79f3f04269621889e731a1045a45cc37",
  "countTotalTransaction": 0,
  "countTransaction": 0
}
```

**[Back to top](#Get-started)**

## Top height

| #   | URI                   | Method    | Description                                                   |
|-----|-----------------------|-----------|---------------------------------------------------------------|
| 3.  | `/top-height`          | GET       | Get top height.                                          |

Request

```
    http://<ip>:<port>/top-height # https://node.inescoin.org/top-height
```

Response

```
{
  "height": 95044
}
```

## Public key

| #   | URI                   | Method    | Description                                                   |
|-----|-----------------------|-----------|---------------------------------------------------------------|
| 4.  | `/public-key`          | GET       | Get node public key.                                          |

Request

```
    http://<ip>:<port>/public-key # https://node.inescoin.org/public-key
```

Response

```
{
  "publicKey": "LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUZ3d0RRWUpLb1pJaHZjTkFRRUJCUUFEU3dBd1NBSkJBTmZPZ2xhTkFua1FIUFE0azJnbDRSL3hHU2NYd2lRTwp6b2RHTXNITENKWXVHTVNhRlRHZlpYenhTZWJ3VVpWRi9FT0JTMERDZllCdXNVc2NNcXVmTm44Q0F3RUFBUT09Ci0tLS0tRU5EIFBVQkxJQyBLRVktLS0tLQo="
}
```

**[Back to top](#Get-started)**

## Memory transactions pool

| #   | URI                   | Method    | Description                                                   |
|-----|-----------------------|-----------|---------------------------------------------------------------|
| 5.  | `/mempool`            | GET       | Get memory transactions pool.                                 |

Request

```
    http://<ip>:<port>/mempool # https://node.inescoin.org/mempool
```

Response

```
{
  "count": 1,
  "transactions": [
    {
      "hash": "837e2d5776261fab7652710d54163f1da0b9a99e2e6860c8c032c8cc6f314d47",
      "configHash": "19924913f08605e99f0a3aeb361b9a00",
      "bankHash": "3ef3439170006659472e60d94e8da6193843fe01bba08ebcd034c05fa66b1924",
      "from": "0x9c7983ae76A0371fFce50Df3383eF53Dea0647b8",
      "transfers": "W3sidG8iOiIweDhGYTJGODM0MkUzNGNiNjNkNzhlYjc2NDE1ODU3QjRhN0NkNzY3Q2QiLCJhbW91bnQiOjc3NzAwMDAwMDAwMCwibm9uY2UiOiIzNzM3MzYzODM1MzEzNTM2MzkzMjMyMzUzNDM2MzUzMTM4MzAzOTMzMzkzMzM0Iiwid2FsbGV0SWQiOiIiLCJoYXNoIjoiMzQwNDc1Mjk2YTlkNjJjNWNjOGJlYTljODM1MjYzY2M5MGY4NGQwODNkN2M3MGM0YzM3NDYyNGQzZmZlMzNmNiJ9XQ==",
      "amount": 777000000000,
      "amountWithFee": 777001000000,
      "fee": 1000000,
      "coinbase": false,
      "createdAt": 1569225465,
      "publicKey": "03bdb43bc05c030507cff24f53447b10c4b43bcb2ef3ce18bf64a2c23d2fa1db22",
      "signature": "3045022100e0bdb0f0ca33ccdf4bd485e5eb53dbde815774dc9e56121b5092e4831875784f022028bcd872e33d09e65ad7e6591809de0652a8f35a83d238ee4811fc1ce1a66580"
    }
  ]
}
```

**[Back to top](#Get-started)**

## Peers

| #   | URI                   | Method    | Description                                                   |
|-----|-----------------------|-----------|---------------------------------------------------------------|
| 6.  | `/peers`              | GET       | Get node peers list.                                          |

Request

```
    http://<ip>:<port>/peers # https://node.inescoin.org/peers
```

Response

```
[
  "<ip>:<port>",
  "<ip>:<port>"
]
```

**[Back to top](#Get-started)**

## Get blocks

| #   | URI                   | Method    | Description                                                   |
|-----|-----------------------|-----------|---------------------------------------------------------------|
| 7.  | `/get-blocks`         | POST      | Get blocks                                                    |

Request

```
    curl -X POST \
       https://node.inescoin.org/get-blocks \
       -H 'Content-Type: application/json' \
       -d '{
       "page": 1,
       "limit": 1
    }'
```

Response

```
[
    {
        "difficulty": 21,
        "nonce": 930360,
        "height": 102677,
        "cumulativeDifficulty": 138007281606,
        "previousHash": "0000049051bd01937de37e6f72d119f809977418abb919676530fa9051929da0",
        "configHash": "19924913f08605e99f0a3aeb361b9a00",
        "data": [
            {
                "hash": "a105b4f0b5656a5c620aa7afe045b89c14bc1b123b51cc84fd5b5cd5cbffe37c",
                "configHash": "19924913f08605e99f0a3aeb361b9a00",
                "bankHash": "1e33d9c8d5b0dc4fa9b8cdbf2aed5e70a7f74703e4dc9507d5403c1fdef8fe24",
                "from": "inescoin",
                "transfers": [
                    {
                        "to": "0x5967a4016501465CD951a1e3984F772AfDeB5207",
                        "amount": 100000000000,
                        "nonce": "3539323337313536393237303636392e3832303915193",
                        "hash": "562fb40dd7c976dc3888bcce0add956069cfac75e0314b52ec5447c0eb59e394",
                        "walletId": ""
                    }
                ],
                "amount": 100000000000,
                "amountWithFee": 100000000000,
                "fee": 0,
                "coinbase": true,
                "createdAt": 1569270669,
                "publicKey": "",
                "signature": ""
            }
        ],
        "hash": "00000305af4bd0856886a636c28c379de953eb6231a10323c13cf838928e0383",
        "createdAt": 1569270670,
        "merkleRoot": "8fb12e9d0dd23b8573f76d34fa27c822a18d180a809b8a035cfaa311634c942b",
        "countTotalTransaction": 0,
        "countTransaction": 0
    },
    {
        "difficulty": 21,
        "nonce": 217006,
        "height": 102676,
        "cumulativeDifficulty": 138005184454,
        "previousHash": "000007b81c000060139254c8114c33b4051d452af324157527034a976953539d",
        "configHash": "19924913f08605e99f0a3aeb361b9a00",
        "data": [
            {
                "hash": "ad1851c9d99ff7da49bbb3dd4bfc694c5048a5a2c6d0095dd20efecfb4738364",
                "configHash": "19924913f08605e99f0a3aeb361b9a00",
                "bankHash": "141a33b8557a047b1e67ff3a8ec55688171c46951c00791bc4e63f962eec924a",
                "from": "inescoin",
                "transfers": [
                    {
                        "to": "0x5967a4016501465CD951a1e3984F772AfDeB5207",
                        "amount": 100000000000,
                        "nonce": "3132303230313536393237303636332e3731303764276",
                        "hash": "10331eb2dee814e9b866d483776b00a99a88bc4d9c9cb30c0cbb989624899805",
                        "walletId": ""
                    }
                ],
                "amount": 100000000000,
                "amountWithFee": 100000000000,
                "fee": 0,
                "coinbase": true,
                "createdAt": 1569270663,
                "publicKey": "",
                "signature": ""
            }
        ],
        "hash": "0000049051bd01937de37e6f72d119f809977418abb919676530fa9051929da0",
        "createdAt": 1569270664,
        "merkleRoot": "9354f6fcb02387d9440b331cc0fbaecde35a3541b09eaa4a59dfe0ab2ece93f4",
        "countTotalTransaction": 0,
        "countTransaction": 0
    }
]
```

**[Back to top](#Get-started)**

## Get block by height

| #   | URI                       | Method    | Description                                                   |
|-----|---------------------------|-----------|---------------------------------------------------------------|
| 8.  | `/get-block-by-height`    | POST      | Get block by height                                           |

Request

```
     curl -X POST \
       https://node.inescoin.org/get-block-by-height \
       -H 'Content-Type: application/json' \
       -d '{
       "blockHeight": 45739
     }'
```

Response

```
{
    "difficulty": 19,
    "nonce": 1115322,
    "height": 45739,
    "cumulativeDifficulty": 59920351174,
    "previousHash": "0000009043c501aaeedf4ee5789f309ae2b1d8bb29d8fc904570e52f2533489b",
    "configHash": "19924913f08605e99f0a3aeb361b9a00",
    "data": [
        {
            "hash": "94ed17a7e10c4a674947200df6b35b8c38cd2b36dd7498a4bfb990f765f093de",
            "configHash": "19924913f08605e99f0a3aeb361b9a00",
            "bankHash": "62ef0b376a0a34d30da7fabd9ce81852b38cad9e5d10085d24a226d354e8f2f2",
            "from": "0xfb645CDF914E081C85831f3631Dea45B5bE2B1f6",
            "transfers": [
                {
                    "to": "0xECAF3Ec27506E2e718e3e4dfb3a6B105284bBF2E",
                    "amount": 3997000000,
                    "nonce": "3330333330313536383439303732393138333135393332",
                    "walletId": "",
                    "hash": "9333dad322adb2f8ca6667000d9ec07f8418eae97cecc0b7c74f8e6c55c3142e"
                }
            ],
            "amount": 3997000000,
            "amountWithFee": 3998000000,
            "fee": 1000000,
            "coinbase": false,
            "createdAt": 1568490729,
            "publicKey": "02b748307b4c07707b29618149f905b5c014b9811dd3d7118421f24885df8bec2e",
            "signature": "3046022100899cfaeddb64c6a2c4d58028d05033a368c1094eb37bd69aae0c93468a5e6a4a022100b774ea44a6c46569c24890a51cb5473e86d19b6bf4c7359f7f606db2eff2dbd3"
        },
        {
            "hash": "25079794a8929794734fa443e49f0899294a1123424f9a6e94a487b3d7db4c78",
            "configHash": "19924913f08605e99f0a3aeb361b9a00",
            "bankHash": "c579a465771a8e19d9460d1e47169ce7054823dbaf0eee81f53750adaf5659be",
            "from": "inescoin",
            "transfers": [
                {
                    "to": "0x5967a4016501465CD951a1e3984F772AfDeB5207",
                    "amount": 100000000000,
                    "nonce": "3835373831313536383439303734302e3030333972944",
                    "hash": "8eaeb124f41cef8cce29dc01db09bf5c8b7fd5147794f39d544264fcd0690463",
                    "walletId": ""
                }
            ],
            "amount": 100000000000,
            "amountWithFee": 100000000000,
            "fee": 0,
            "coinbase": true,
            "createdAt": 1568490740,
            "publicKey": "",
            "signature": ""
        }
    ],
    "hash": "000001dad1d5be66e1496e9030c8c14967e3945aa18b2592bc95c0d56b46dcaa",
    "createdAt": 1568490740,
    "merkleRoot": "cdbfacf88f1098e4705adcde74da932878b4cc8e2a4029d0732c1e8f232f2b01",
    "countTotalTransaction": 0,
    "countTransaction": 0
}
```

**[Back to top](#Get-started)**

## Get block by hash

| #   | URI                       | Method    | Description                                                   |
|-----|---------------------------|-----------|---------------------------------------------------------------|
| 9.  | `/get-block-by-hash`      | POST      | Get block by hash                                             |

Request

```
    curl -X POST \
      https://node.inescoin.org/get-block-by-hash \
      -H 'Content-Type: application/json' \
      -d '{
      "blockHash": "000001dad1d5be66e1496e9030c8c14967e3945aa18b2592bc95c0d56b46dcaa"
    }'
```

Response

```
{
    "difficulty": 19,
    "nonce": 1115322,
    "height": 45739,
    "cumulativeDifficulty": 59920351174,
    "previousHash": "0000009043c501aaeedf4ee5789f309ae2b1d8bb29d8fc904570e52f2533489b",
    "configHash": "19924913f08605e99f0a3aeb361b9a00",
    "data": [
        {
            "hash": "94ed17a7e10c4a674947200df6b35b8c38cd2b36dd7498a4bfb990f765f093de",
            "configHash": "19924913f08605e99f0a3aeb361b9a00",
            "bankHash": "62ef0b376a0a34d30da7fabd9ce81852b38cad9e5d10085d24a226d354e8f2f2",
            "from": "0xfb645CDF914E081C85831f3631Dea45B5bE2B1f6",
            "transfers": [
                {
                    "to": "0xECAF3Ec27506E2e718e3e4dfb3a6B105284bBF2E",
                    "amount": 3997000000,
                    "nonce": "3330333330313536383439303732393138333135393332",
                    "walletId": "",
                    "hash": "9333dad322adb2f8ca6667000d9ec07f8418eae97cecc0b7c74f8e6c55c3142e"
                }
            ],
            "amount": 3997000000,
            "amountWithFee": 3998000000,
            "fee": 1000000,
            "coinbase": false,
            "createdAt": 1568490729,
            "publicKey": "02b748307b4c07707b29618149f905b5c014b9811dd3d7118421f24885df8bec2e",
            "signature": "3046022100899cfaeddb64c6a2c4d58028d05033a368c1094eb37bd69aae0c93468a5e6a4a022100b774ea44a6c46569c24890a51cb5473e86d19b6bf4c7359f7f606db2eff2dbd3"
        },
        {
            "hash": "25079794a8929794734fa443e49f0899294a1123424f9a6e94a487b3d7db4c78",
            "configHash": "19924913f08605e99f0a3aeb361b9a00",
            "bankHash": "c579a465771a8e19d9460d1e47169ce7054823dbaf0eee81f53750adaf5659be",
            "from": "inescoin",
            "transfers": [
                {
                    "to": "0x5967a4016501465CD951a1e3984F772AfDeB5207",
                    "amount": 100000000000,
                    "nonce": "3835373831313536383439303734302e3030333972944",
                    "hash": "8eaeb124f41cef8cce29dc01db09bf5c8b7fd5147794f39d544264fcd0690463",
                    "walletId": ""
                }
            ],
            "amount": 100000000000,
            "amountWithFee": 100000000000,
            "fee": 0,
            "coinbase": true,
            "createdAt": 1568490740,
            "publicKey": "",
            "signature": ""
        }
    ],
    "hash": "000001dad1d5be66e1496e9030c8c14967e3945aa18b2592bc95c0d56b46dcaa",
    "createdAt": 1568490740,
    "merkleRoot": "cdbfacf88f1098e4705adcde74da932878b4cc8e2a4029d0732c1e8f232f2b01",
    "countTotalTransaction": 0,
    "countTransaction": 0
}
```

**[Back to top](#Get-started)**

## Get transaction by hash

| #   | URI                             | Method    | Description                                                |
|-----|---------------------------------|-----------|------------------------------------------------------------|
| 10. | `/get-transaction-by-hash`      | POST      | Get transaction by hash                                    |

Request

```
    curl -X POST \
      https://node.inescoin.org/get-transaction-by-hash \
      -H 'Content-Type: application/json' \
      -d '{
      "transactionHash": "94ed17a7e10c4a674947200df6b35b8c38cd2b36dd7498a4bfb990f765f093de"
    }'
```

Response

```
{
    "hash": "94ed17a7e10c4a674947200df6b35b8c38cd2b36dd7498a4bfb990f765f093de",
    "configHash": "19924913f08605e99f0a3aeb361b9a00",
    "bankHash": "62ef0b376a0a34d30da7fabd9ce81852b38cad9e5d10085d24a226d354e8f2f2",
    "blockHeight": 45739,
    "from": "0xfb645CDF914E081C85831f3631Dea45B5bE2B1f6",
    "transfers": {
        "9333dad322adb2f8ca6667000d9ec07f8418eae97cecc0b7c74f8e6c55c3142e": {
            "to": "0xECAF3Ec27506E2e718e3e4dfb3a6B105284bBF2E",
            "amount": 3997000000,
            "nonce": "3330333330313536383439303732393138333135393332",
            "walletId": "",
            "hash": "9333dad322adb2f8ca6667000d9ec07f8418eae97cecc0b7c74f8e6c55c3142e",
            "transactionHash": "94ed17a7e10c4a674947200df6b35b8c38cd2b36dd7498a4bfb990f765f093de",
            "from": "0xfb645CDF914E081C85831f3631Dea45B5bE2B1f6",
            "height": 45739,
            "createdAt": 1568490729
        }
    },
    "amount": 3997000000,
    "amountWithFee": 3998000000,
    "createdAt": 1568490729,
    "coinbase": false,
    "fee": 1000000,
    "publicKey": "02b748307b4c07707b29618149f905b5c014b9811dd3d7118421f24885df8bec2e",
    "signature": "3046022100899cfaeddb64c6a2c4d58028d05033a368c1094eb37bd69aae0c93468a5e6a4a022100b774ea44a6c46569c24890a51cb5473e86d19b6bf4c7359f7f606db2eff2dbd3",
    "status": "pending"
}
```

**[Back to top](#Get-started)**

## Get transfer by hash

| #   | URI                             | Method    | Description                                                |
|-----|---------------------------------|-----------|------------------------------------------------------------|
| 11. | `/get-transfer-by-hash`         | POST      | Get transfer by hash                                       |

Request

```
    curl -X POST \
      https://node.inescoin.org/get-transfer-by-hash \
      -H 'Content-Type: application/json' \
      -d '{
      "transferHash": "9333dad322adb2f8ca6667000d9ec07f8418eae97cecc0b7c74f8e6c55c3142e"
    }'
```

Response

```
{
    "transfer": {
        "to": "0xECAF3Ec27506E2e718e3e4dfb3a6B105284bBF2E",
        "amount": 3997000000,
        "nonce": "3330333330313536383439303732393138333135393332",
        "walletId": "",
        "hash": "9333dad322adb2f8ca6667000d9ec07f8418eae97cecc0b7c74f8e6c55c3142e",
        "transactionHash": "94ed17a7e10c4a674947200df6b35b8c38cd2b36dd7498a4bfb990f765f093de",
        "from": "0xfb645CDF914E081C85831f3631Dea45B5bE2B1f6",
        "height": 45739,
        "createdAt": 1568490729
    },
    "transaction": {
        "hash": "94ed17a7e10c4a674947200df6b35b8c38cd2b36dd7498a4bfb990f765f093de",
        "configHash": "19924913f08605e99f0a3aeb361b9a00",
        "bankHash": "62ef0b376a0a34d30da7fabd9ce81852b38cad9e5d10085d24a226d354e8f2f2",
        "blockHeight": 45739,
        "from": "0xfb645CDF914E081C85831f3631Dea45B5bE2B1f6",
        "transfers": {
            "9333dad322adb2f8ca6667000d9ec07f8418eae97cecc0b7c74f8e6c55c3142e": {
                "to": "0xECAF3Ec27506E2e718e3e4dfb3a6B105284bBF2E",
                "amount": 3997000000,
                "nonce": "3330333330313536383439303732393138333135393332",
                "walletId": "",
                "hash": "9333dad322adb2f8ca6667000d9ec07f8418eae97cecc0b7c74f8e6c55c3142e",
                "transactionHash": "94ed17a7e10c4a674947200df6b35b8c38cd2b36dd7498a4bfb990f765f093de",
                "from": "0xfb645CDF914E081C85831f3631Dea45B5bE2B1f6",
                "height": 45739,
                "createdAt": 1568490729
            }
        },
        "amount": 3997000000,
        "amountWithFee": 3998000000,
        "createdAt": 1568490729,
        "coinbase": false,
        "fee": 1000000,
        "publicKey": "02b748307b4c07707b29618149f905b5c014b9811dd3d7118421f24885df8bec2e",
        "signature": "3046022100899cfaeddb64c6a2c4d58028d05033a368c1094eb37bd69aae0c93468a5e6a4a022100b774ea44a6c46569c24890a51cb5473e86d19b6bf4c7359f7f606db2eff2dbd3",
        "status": "pending"
    }
}
```

**[Back to top](#Get-started)**

## Get wallet address infos

| #   | URI                             | Method    | Description                                                |
|-----|---------------------------------|-----------|------------------------------------------------------------|
| 12. | `/get-wallet-address-infos`     | POST      | Get wallet address details                                 |

Request

```
    curl -X POST \
      https://node.inescoin.org/get-wallet-address-infos \
      -H 'Content-Type: application/json' \
      -d '{
      "walletAddress": "0xECAF3Ec27506E2e718e3e4dfb3a6B105284bBF2E"
    }'
```

Response

```
{
    "amount": 12996000000,
    "address": "0xECAF3Ec27506E2e718e3e4dfb3a6B105284bBF2E",
    "firstHeight": 39699,
    "lastHeight": 45799,
    "hash": "4e57cf2a897b03db15a8a7682889f901b4f6e014003f2609b432f08b621fa238",
    "transfers": {
        "transactions": [
            {
                "to": "0xECAF3Ec27506E2e718e3e4dfb3a6B105284bBF2E",
                "amount": 3997000000,
                "nonce": "3330333330313536383439303732393138333135393332",
                "walletId": "",
                "hash": "9333dad322adb2f8ca6667000d9ec07f8418eae97cecc0b7c74f8e6c55c3142e",
                "transactionHash": "94ed17a7e10c4a674947200df6b35b8c38cd2b36dd7498a4bfb990f765f093de",
                "from": "0xfb645CDF914E081C85831f3631Dea45B5bE2B1f6",
                "height": 45739,
                "createdAt": 1568490729
            },
            {
                "to": "0xECAF3Ec27506E2e718e3e4dfb3a6B105284bBF2E",
                "amount": 1000000000,
                "nonce": "3635343931313536383333313930323631363736323630",
                "walletId": "",
                "hash": "b0f2bb49c11c31e795dd46ee7a3bb8a0cbb3fb3a8b161d0612dc96647e56834f",
                "transactionHash": "0ff3c27eb2ed161c27ab79ab96c8b6f4a8a2e9b50bcf27f16877712a430dece5",
                "from": "0xfb645CDF914E081C85831f3631Dea45B5bE2B1f6",
                "height": 40488,
                "createdAt": 1568331902
            },
            {
                "to": "0xECAF3Ec27506E2e718e3e4dfb3a6B105284bBF2E",
                "amount": 5000000000,
                "nonce": "3231393431313536383332363339313931313938313231",
                "walletId": "",
                "hash": "7cd67c24445fceefcd40bbaf000f4eed331c5fe0b0bdbf0f8999063fbdbce445",
                "transactionHash": "20f0acf6fee921971928b6478b6f4e61795d0e12c4d882053273a090e75502b9",
                "from": "0xfb645CDF914E081C85831f3631Dea45B5bE2B1f6",
                "height": 39991,
                "createdAt": 1568326391
            },
            {
                "to": "0xECAF3Ec27506E2e718e3e4dfb3a6B105284bBF2E",
                "amount": 2999000000,
                "nonce": "3230343732313536383332323738313635383836333534",
                "walletId": "",
                "hash": "7e2a32adb13449dbe2932271de882ee6578f5249b8ffba6c402783450be924b5",
                "transactionHash": "1f776d76e337e312c62b502859bfbaa7597ccafb5e453b3f61ca5826fbd92d4b",
                "from": "0xfb645CDF914E081C85831f3631Dea45B5bE2B1f6",
                "height": 39652,
                "createdAt": 1568322781
            }
        ],
        "total": 4
    },
    "transfersPool": []
}
```

**[Back to top](#Get-started)**

## Get wallet addresses infos

| #   | URI                             | Method    | Description                                                |
|-----|---------------------------------|-----------|------------------------------------------------------------|
| 13. | `/get-wallet-addresses-infos`   | POST      | Get wallet addresses infos                               |

Request

```
    curl -X POST \
      https://node.inescoin.org/get-wallet-addresses-infos \
      -H 'Content-Type: application/json' \
      -d '{
      "walletAddresses": "0xfb645CDF914E081C85831f3631Dea45B5bE2B1f6,0xECAF3Ec27506E2e718e3e4dfb3a6B105284bBF2E"
    }'
```

Response

```
{
    "0xfb645CDF914E081C85831f3631Dea45B5bE2B1f6": {
        "amount": 0,
        "address": "0xfb645CDF914E081C85831f3631Dea45B5bE2B1f6",
        "firstHeight": 36099,
        "lastHeight": 45799,
        "hash": "b495ed5bc99e24a4c814efed65149d03fb85b98b82880ac355332d22c4aab014",
        "previousHash": "62ef0b376a0a34d30da7fabd9ce81852b38cad9e5d10085d24a226d354e8f2f2"
    },
    "0xECAF3Ec27506E2e718e3e4dfb3a6B105284bBF2E": {
        "amount": 12996000000,
        "address": "0xECAF3Ec27506E2e718e3e4dfb3a6B105284bBF2E",
        "firstHeight": 39699,
        "lastHeight": 45799,
        "hash": "4e57cf2a897b03db15a8a7682889f901b4f6e014003f2609b432f08b621fa238"
    }
}
```

**[Back to top](#Get-started)**

## Transaction

| #   | URI                             | Method    | Description                                                |
|-----|---------------------------------|-----------|------------------------------------------------------------|
| 14. | `/transaction`                  | POST      | Send transaction                                           |

Request

```
curl 'https://node.inescoin.org/transaction' \
  -H 'Content-Type: application/json' \
  --data-binary '{"message":[{"d":"772b507948565634747a7852644e4559344d2f6e70736c456274796e415171664e6a657536397359477441644358616d5144527975494a4571395465775a7255784c364e534e336c586a47536b314c396c7a533674673d3d","s":"304402200bfb918578aecb8f58b3274e382212a0e5c45c21b496c62f018d84c721167713022029892b1693deb79a596e0a33b3285047f08e53852ab872637b7b8e863c55e99a"},{"d":"5771376936363458787751616b645354315a305650553763713875646562634f686c6a42434c492f79634c41593156326c6e6c576b4a464e434877553869326d4f59742f51327a4c5131484c7a4d67453744593866513d3d","s":"3046022100b0435e4073207369ffdf878f3aa1b2f5bc77e879e876baa97e1b077fcd8f5b77022100ed0c802510b1216ee850949399066e4e749a5fc4182a4b0ce7ad7649c7a70819"},{"d":"6d677667586e2b6231556a76726278326c78672b704e6b4c55637359554872694d37395566542b6c395046347763766f39734651687036783252736d3979565579723937714b62794f4264335145397945386a4d44773d3d","s":"3045022012475010cf0d263d2f67f3315584ee244a2850ae8e071ebc2e5fa119a51eaa28022100fb63b6aa9d51bf083d233714376f7ed0aba46286ff839034aac59f2a7e18da72"},{"d":"496b35357a584d50333547764b707850454c6859394c676e77424852774e6f37576975524f637279356243727a506e51567a574e2b553772367a4a71665951524d69336379616b5556534a4c2f3575744365626939513d3d","s":"3045022062a17420a0075126647934f44ec43d0623ca857b7d67343c7d007e2565570e68022100d38371164a710cc4c8a4a2e2206077fa395eeb89688bd57bf363a178c3fd54be"},{"d":"6d6b676d7461384e704d4866554a2b3456792b386d76427562742b73713132656a49724c41466e44393155557272465152493551546c2f734249324530507154746e5a4877594b794a767945465770654459663379773d3d","s":"3046022100d0892e1dee5c80525327146fad4058a62392ff9b3435c8da4fcaeda7f8bc307d022100acefb6b4ab724d0947e91283e19408bd41429210913c06992119915487c7fa97"},{"d":"70777345414375314c46614c715766413732436768364a66352f673078356a59362f4f37383774515764784d695164454d6473725070393864306b4562634b67314165327a59516b4c554d4967673246644a2f4d70413d3d","s":"3046022100e1edec9a156bc6acb45602940c671027395913e3247ac4c095b3ebc564925603022100a3b793d5ae751b0a1d2805d82340fe6edbe51d53876313cefcf5fc513575500a"},{"d":"705031546d4f516e77566f4a6f696e77443270445173352f4273646157526a4739746730522b646e5252517430434c3669486361766236357561654c79456c5a4455554553636e55776567693272774b426c71624e413d3d","s":"3045022007eeb1ac79c9e3a0584d073fec87b1bd1e4e38db9cc106cfb0ed9922e3c27d36022100d25c1835e024042e75c0251761d53679948bf70c2748761c4bba22ef0c4dcec6"},{"d":"46535350536d624e726f4b634533595736684e6a36304733386f6f7656642f766b4d7665396f6558526d594b6d5247485766436a47544f52533630684b49424339453536425332664b6b536a625a33537155417862673d3d","s":"30460221008e18ed480e041d111491ca15c7479f57b271d1cfbe94a8ef45749f1a184e768a022100e037faf5220491562a268c9f8db3b836a8495435f028ad117f4bbce0e221c6d9"},{"d":"5838612b4f714d4a655068734d4e67746b4f76383435624f4c4250684359473430626b457237364f6b69614667756a3675396130376242496b502f32785674434e4d6a427741744834686939484d484e2b2b596463413d3d","s":"304402204002696969da81d1099b4643895d510f7822e2215fda4c4f7a0415c6b5bdbe4e02200d8dd28252ae70f4c478cd9758bc0dbfd7ec70c291232b25362739ef07090504"},{"d":"797a2b644f6e634f475467734f36334f525350576744307656382f316e4a5956644c694e4a66752b574c4c2b444d4d70653135364d767658356553682b316a683351652b65344f2f6b7a6e67564d3155466c50545a413d3d","s":"30440220649f80ffb6f4dd6ae10ab5de9378bccfe6dd22914e5da566c7de7c2baaaaebd502200a67cff5fbaea164183bab5eccc9923b29fb95c3bbdde379ecf2d742c2ed43f8"},{"d":"4a45493378686e68657641594363646277517272747a5338634d704b75316b7244784b475448677a4462324d736e414a576a656839705472624c6e486b34322f2b46443930343774514b496947794b4f5042397245673d3d","s":"30460221008c82cb52b234a946b9dd774272c9dbba94e92f1ebf163dad804f462a2a386f2a0221009bed88fb7bae72e74d6c80f6abe4f851a6c036283f5985e8ee9eb67a940e6bc9"},{"d":"6567594d6b2f6d7662586673316570343159642b50704678786d4e64536359574551743152696842526b35362f6f36464537572f79316b6f53513932664f445678594a7164473030644d49327849627364754c4773413d3d","s":"304502200160d1544af57bf4b57356f99437e72d77bc1a10a914b4ee4236a6a7f99d5a66022100f6397fdd3908fb39f5c9f0c18f022005d458dddb2efd884bc985bf0f34fe4353"},{"d":"7153685a5065347a6368703935355664447651776c41656a625965566c4f4f436f4c753144414936305979747769474b684d69562b34665a656e37424a5a3557777359794c31614379524a2f4158694f4169786a4a673d3d","s":"304402207b2041fc7e84b44fee28fde83bda8a18b8e8f861306dddcbaf43c646fd794db80220715605a44aa4529a1edd76999d8830e7e9925a182ed6aaacfa66c0b081e96872"},{"d":"735447743073347041466c3549314e38435879762f6a69304d38737a455a7a4f795a61304b516346324a4b653834716f702b4a5a63714976336c525855756a6d57444763755961527a4d47797a6c62543638497969673d3d","s":"304402207e78e5cc72f62bde7028c28c8ca6f792e22d76b840a1b747b42ab2ba122defb50220101f1e5e831f6b9535eef05f25bf1783f070416d7799549d5624b6d5fbbf2233"},{"d":"554b7a6d7043776670694d65354e427150522b72593259676f7a764b34504b4f7374357961614d4c507276704c2b6c52614433647458574f5676464c67564c57546b627030577335523332557a2b64554738377073513d3d","s":"30440220239deedc18a1676347393e433777b5ebc8a067fd2ac4f410315d50a781aed64d022043a678b8f87908e87ade55a1d791840b134a569b3de6a115e22df59b44d34116"},{"d":"4550344b70387431484a592b777a48376f706270552b593142795349427a37466c6d3335676a2b5167645052594475355572557754505a58393164525a34352f6176717a4350484c7362416c2b4147445341766744413d3d","s":"3046022100b01aa8fc5a204e83709e4fbb695c4f84234920a1ea4a72862484c9f7a04997d7022100e5ab88a8be4d515fd2e86da70f6d0d8c54b35aa7f71a23bb63de02ade2d87c5d"},{"d":"473359437470523741557a5352436b596542565974715456455a30324d7a5477442f6c7936444b6153565277322b68355a456f614b316c413950727079417233786878636a314c71677a737a4a506e48367733772b773d3d","s":"3046022100f9f23bef3952524a6e16ec3066e2ad12b24e7075bafeedb45426d750c67e913f022100976465a5d0d7953082f52f4fd9d36e26c0bdf60291b3f8f7738b4c518454854b"},{"d":"793837765334355a656f566e78344b4b6661384b492b435533565176546a592b304246516a2b67433637387870614537366733534d2f51534745697176567a7547585734454e58512b7078656a76446c58692b6958513d3d","s":"304502201e0d3015b19822899517d4a368e4fd5bee423c19fa5a9dd8955afc1ae5e701a0022100effe5120ee8eca268076bf3b1dbad0c9673a6520a2a7343c1dfd47e41e31c892"},{"d":"446f636e715631305335686139503548754e5254774d593955582b58644a515057776b576636464449623969616d4b4a334a746f496379632f506a4156654645567a3644616d6875372b35575251647a71767a4a34413d3d","s":"3046022100f92ba9abb63614f0ea90d38227ae47312aeb191548fca90ef6d4b7aa321a1269022100eb3b8da5f6d406258a8f2a7b06917c6d94662e4dccc119459a5b6dfc5492c08d"},{"d":"7a6567486c484d784438687658436839775567396d6543695746472b6f6c3470624e483772466f354e61454f4a724b524e6c4144793363636c5a474b417454456f6b52734f454f414b4f79357570455954784b7779673d3d","s":"304402203a5cc4df20fcf257ed79615dfe3089ccc3d7f775f5a881d020ac63bb8323caff02201a31a05630721d3bc4bff9e4d650e72bb5c6bc55272f457c29e5cfe554efa7e1"},{"d":"542f4a4a336c6b6673306c393037376169397956425267674f4546682f565a50484b4d7537486945584d6461586b73646b6977736d63446645655a4d54614e745633726e6b6e37332b4637346f454a6c565a6f6f4a773d3d","s":"3044022073e6887af45ca0d25bc51f853258d10f0c5621b4e3465eefbee6d7858d1f51ca022038ce08bde694334fdbad75d899e66e9e9012d5e454c92b73782c70c0fcdc0a55"},{"d":"566838536a4f4e6e415a45726e36682f6675466d6265316c643837332b775548665a66314b31796266744b73717679572b66524a5877595661424d7a34626c376f6a6f41677741765842453244524f46794a357a43773d3d","s":"3046022100f75ea0d3e7b7f3b94381911cce8dc8764616ac3a007de023b171c45f5a499efe022100c81d7c6f3738af9e4e7599bbcb6b37174e62a07e57f9680847994d3ca3e0d5b1"},{"d":"55524b2f55694351433934336a4e6568687972665a64556e44545756646237696c6c6470505647704c54667536774472636c2f4b786c395043763477624b345950783470376743432b36666c645247316d76696672513d3d","s":"3045022100974b1632cd3da7fd2abeb776450dc9937e85196b16cd6e369532cebeedc42b3d02207c61bd849a4688c2fbe131325e169468bf95df24348f5c476fdd68020f025922"},{"d":"7a34774a3661696c434c31573073643052376f356b673166742b4f504d495642347957782f452b523549526a6c6b4d654553436a744b58785870525844326747486943724431435364694b693852387246466b516f413d3d","s":"3045022100bfc32d85a6f998bbcc3223836b3064229b2c95f9916c57b4ddfcdadf42fc746402206b1b45b89f8f66163538e69e3edbc091978678ac15230e18610dbd29242099bf"},{"d":"6b494d5a514c5745356e54686f34713442753230745042484253755775596f625650726d536a5537634945724133635753513965346e4a477142345478335767394d507541326865416277386a34333949326d6b37413d3d","s":"304402202b10f30769af9d7880bdda59996e119b4c974d7edcdf679178ec32879a814bfa022065ceb1455c0d380fbb40213ed8b83359c7910b1c96fef7357b4ad9a16591c829"},{"d":"784478336f71624f65562f62544c2f774e4234754b627633676959357a6d63723679767850724b345132564d4575527a4b785a6a6e4a5a426977484856672b494d6a6a5631752b4d334a7953636b4345754f4d696f413d3d","s":"304502204e7820539641401c38ebe29685d5407401aa2e5bc511811ba73a1f90f001aad9022100a1af1657444cc00b135576d797da28c1a2c60f67d4ac2c361b5cf9a946a546f4"},{"d":"77696c33585a6b616869356865646c4d617673486337627034395368356f5130615046484a506875674a73547172397a7462616b4b66705a564a48326f705a49684e62445556366f65583044317836463047383554773d3d","s":"3045022068cffb5631eecdd9bc6b6763b9ec8350235e0758b95ca5d1bbfdcfa0ced10a8e022100d997c111ff8a23a0d2a7ae1554efb19264afda0326109238c4e4e497438a0b15"},{"d":"622f693641344f386961412f614c4139755a41537a51704f6d61754f45696973437535587958432b54614241494175586751466d536c775336395975696e6871616369334c59556b6d31304e744e576b7455757255413d3d","s":"304502207a6491a684ceba453a442d93ad62061af9c4c86189008a6180c9ebdda25df185022100f0a436150c3ee11c1b22f2578cb74801101d9ffa960ee7cc972c5edcdd8bd5e5"},{"d":"7951374e794d7849393863434b78617974792b556c30775349304854717562474778746c325a45783251526745376d6f706d6d6243513168472b6b4f6f6c5566684a714b466d48476e56335670716a6678496a4461773d3d","s":"30440220297f35f02193d9f63050afd61c30de81dd16ab539aa129e5acd3e0b011bc90df0220213c3b100893125deb5324711b548cb217788ca516c38aeabcc82877b92d248d"},{"d":"68727467754f63574d2b46504a65372f6e506b524b594a48427630756f62306857384f62472b4d415066794e2b577466464c5348496a6a46486a5135677355447a6556587770737235657931564a6461504e616b4d513d3d","s":"3046022100b545a24a7a4dd82e528f435717a93e14056e51e2ddea07f2ae718944775fa643022100c3fa1729405bbc75420b3e09532cc74910a557a1240cdb7c9f3e1251b01715a2"},{"d":"536931446c2b4730314836654b7a562f79563632446b4b336a75465375476d6a30683777625149312b42414762696b57634931523837453073465a65584f7969736c6d434d324743745477436d446149754a496c57413d3d","s":"304502205f392ae02be350485b357ffa0a60590ec637ea50e12439b65468c4d3300f378e022100b10a24d79c6a744d2d12ae1280169eff36b62cf6ab61b8f42b1f0302741956be"},{"d":"524b41567454472f4d77446543597a746370506d513452526f794f4b574c543431445a73454d69504859692f4b2f2f6c2b624f4f71673933715338736a4947364f625a6e4e34456c314f743941326e385972486137673d3d","s":"304502203aa4185f89c0e066a0980e402dc548bf388ace09d939bbb13d968e1cfada70f4022100a684fb2b3abdda18b5100387f6b89fd69bb36f7ea0e3df9885f345ac83229517"},{"d":"664b703655624a4e707479794a75737166734e772b325154477177642f6d616e344d73736d6c4538564f6841534854636e644561597967583466637853396f487a3965712f7953494e6b41505059464e4150373357413d3d","s":"304502203de2015e05e4919536c3866ef8f0a8a26ed852e25fa41e2c0009b20266c56e83022100d15a272adde7ec7052f40b7106189e8e70b3e033d13121cc83adafdf51cb8a73"},{"d":"6b7335784a7338576838414f6777584551387a5838662f3444464249614f695871744a474a6f6a4c4971676b6d49793458557a50376c5268534b6266352f6b5961643366753835337a2b6f68412f3868624e7a4d6d773d3d","s":"304402203bb5dbee8b69d1a802a88ba81e4acf651511b2c69b31ab2c85c76cf4b443eabf02200324596a0a499e553d8fdd997daf4ac8f153e179225c0e1054e36d47779b1812"},{"d":"315650756445395869687a6a4b556a766b4e66696c5932364a6f4634394b44376956615966417669615434515a6b7550395a44705263744d476c413141694835373878305061726865666b4c454b32423461704e66413d3d","s":"3045022056499bd9d2c122bae6b17e2de10a104467e7417f88f2f6d8b0254f6832412ab2022100e67ccf6ea334cb8845e1f83812e4b7c3c3efbd5de442a4c06a4bf0dc2aa67fc2"},{"d":"65482f3850747a3459783955716134336a6c336d6859704b6b67646b4c565253436e7675587436675134456835707a6564736e31656339726d576d786b34734862735050345579666d4539487953413475356f6661513d3d","s":"3045022010762591bc892697b7847cffa7bbcd156507a0fa15f990afd44906e596d694400221008e1adb295dc38ccb6fb3da923cd8dab449a9a82fc4cd6753914534b4f8d9e3a6"},{"d":"6c67634c2b7a625241584f7466617469556d4d6b4c682f657849704b41623545446a376e4939794471656b56653437557266376d3248433348677945396b354261775836797062486d3551566e796a6d5066774e7a773d3d","s":"304502205aedbce89ea702572a7bf904fc154864da33fb1dc609fcbc6eff2357022ebe7f022100ae2237da27cff03ad5863a6012b5107e4d91039dcc5251272e4abf21e3095484"},{"d":"6a526368312f70395477367847626762487958413074614434664b3071615a376e6854326c463564735932365755643933766538307a5a4e4a4959437879635a634c4c4d334f2b67473345422f47494a6a4a4f4b42513d3d","s":"3045022012d838a0ffb045531e7ca6ea6d2f186feef599c785d5116eda5a603c2914086d022100a1b9382d0eb8f832f323e21480e19c061ad9bec9b6db8cd6245ac550a66dc582"},{"d":"6b63384c6c6c43502b2b6f4a33356e7639777a756637365653484d696d666442376c6b766363384535355847324a797430556b32563173646c696b78547a382b58387264465278446c6e515273483230344f487755413d3d","s":"30440220714a18d8a2caed47c46167507b7f617eaadf4c21577ce15c0a05239776ff35e90220686628c1bb8fccf8df302a8c7732794c7384c13400b00f4e581274a42611f1a4"},{"d":"785643706f495a6e364e69725977586165622b74394444384434663866757458567a59486971435041623648696e674777486575396e3379786353346c474a32656d427a3438572b70687968392b7939652b703230413d3d","s":"3044022053ee6c67febf147517756d212fa471ef9f5dc8adbb5d65465c68e3754bb62bff022030ba984b314f637f44fb1b444ae4f81413bcc85ae3e650e15f01b9250f337fa1"},{"d":"434d503364586869756536347a4d585941553947624d52653630626d5051546434787a77554d73584f5555714b744569616a61683278474a5261557768763958756b5531624a426e6f425638392f55765031617352413d3d","s":"3045022100a221d69381dd6e847ecc5e790c018487240a0851557d523baee7296abf9b70b002203a85e43a672093c2564191ff041dc62c5aabad0b334788ccf34323e279836304"},{"d":"5533716f7875736e6b417363556776467367377a5562526b33493561717256376a38787a596e79694e31464d2f635838316b48685844674a4c745238542f3530684b4365433830436f366f386a54454b3056494f5a513d3d","s":"304502205a78ab474a22738527442785ae9d44a4178cd8440043419e872eec399860f145022100feb517e3973502aeee6ae6858ef291a05eac1c606f7cb80b44cabcb6b8da3fd5"},{"d":"5631416b4a52692b55307571546b684c54474a55354c5374346f41343951444c4c7a4b6d3858426c504d466478594f4c586a346f733954765557776357624f69457a742b76433967435374474e4352726945455674673d3d","s":"3046022100b86bea57f41b458b0a25caf56409d22b5fe54e4f840a95f2d8db164150b4917d022100f1798202e2ace6921ea29856adcdfdb668009eef56bf33ed97d16a4bf42e3f09"},{"d":"45654e30312b57764b45464d486a77453151524d774a6a74544863426636566c7672746a387754454b68594e5a483433374e6f49645847317976464853323241386934644c55704a7352326f6d386a62447272386a773d3d","s":"30440220453c10c6cbac7f6f3155bb8830f86357ad1cad780fc33161364d04373742680a02206267d826c85df6a6fa041653adfc8e11c1e0504e7963e35f488f6e9eae4e9d21"},{"d":"6f7359636c6348544c487071654e397a41694d6e6530514154665168367a765547574b325a4e422f6763487030554453334c434e58494c71666673695674595978373657337457666e4c7168714b7170595644586e513d3d","s":"304402200bf6c550fdf3071e0a2936584a347bc8b9214c6119b76ad76042c70b0d0f58d302203e18c02f7b5fd5cb41bfc755c41e301f3f352d82e96867c435ef0b31c1a1577a"},{"d":"796542356d4938595637582b2f69427839504e6c74563241387532704330342b6c52366e696f7562556452325863564a353866736d75336a50424b366a41717143514f44454937666f475279616b54706134704645773d3d","s":"3046022100f621d177b045f0a67e8bb8b8cc3aa41a0f5b8c3b43c26f1087a9ec1cf40f90c4022100f9bc53d76cb77f0e0b807d77fba676f7e9124aa5507cf2fe133997a3f84899fc"},{"d":"616f6b4b5875444147654f6f62564f7a686639774e6b486f30694c4e796f4e43423878456335722f334435437a584130743464464e6a5277797353794a7472364a75646a3734416574594945506259335a486e556a773d3d","s":"30460221009ede2e72643dcee0cbf2154a2b50538d02d49c046cda379e0d6a2009176c6f52022100d4a5f53279e02e815956cfd29f47e08f58338b09c76b04600333e4b00fe2a333"},{"d":"4b465975443057384f59627065637947673651752b4d6c4477584e6d726357654869673557645250376b4d474f6475586a305a6575654c6a5137364e6d2f782b79787637714c7a52636f4b5370546d54767a574c51413d3d","s":"3046022100c8ba503d1adff4f23e2696443c6a9b00dbcdfc64a1782ac10d6fcd9ec9d97356022100a1e9647fc00ff7f548d6625869c4a41ead8f01be57b84400aa3a7c427a3d2b48"},{"d":"4b4d3071307a6b4d6d344a48615a584c5a2f30536e6c4d4745356f41435067745572594b4b6167635851482f417869426e3735536f4e787234575667414735437472335073576e2f506a6d5449316347784d684f69673d3d","s":"3045022100c197e9a3297c197473a3e578e5f540368739169004a4882b2dd9a76332c3fe670220137c05b01e2ad103de70386baef14f008b1507fc08dbc1b17710d7668c09bac2"},{"d":"705961634d324a4d39555a697454534a4a72667a345678734e474841584b6278584575346242306465765579314a4c2f374a424e6a57754e6e4b365235554f3946334d6d466843723535305a306f574e5136532f66413d3d","s":"3046022100cca3cad874c764c8b3d6310139fdd0c457fc0e20e6975ab7a632aacdbe6c1b7b022100cb171bab0c03b93d520ab805fea483caf389cf34cc98561d0f65fb097f374ed0"}],"publicKey":"03745656494ed84d599c0d3a62ced6b1e9d8e7c2240e8dd1e07c0387ae108b4791"}' --compressed
```

Error Response

```
[
   {
      "error":"Error message"
   }
]
```

Success Response
```
[
  [
    {
      "hash":"3e6642bbc616e09f90cbb1cc85ab4749bb79d1fa017baa0513f80b66289e9a31",
      "configHash":"19924913f08605e99f0a3aeb361b9a00",
      "bankHash":"b5121c3944a205cdefb0a28788bb2ef09d6a296eef51e33e2108a9a8a7e5f574",
      "from":"0x5967a4016501465CD951a1e3984F772AfDeB5207",
 "transfers":"W3sidG8iOiIweDE2RDZjYWUyNWYzNkExNkFCQTM0ODI1MjZkRmFiQTFENjlhN2FCNWYiLCJhbW91bnQiOjEwMDAwMDAwMDAwMCwibm9uY2UiOiIzMjMwMzIzMzMxMzEzNTM2MzkzMjM3MzUzNjM1MzQzNDM4MzgzNDM1MzUzNDM5Iiwid2FsbGV0SWQiOiIiLCJoYXNoIjoiOTdhMDE4ZGJlN2Y2OTlmMTViNGE0M2JhMjhjM2Y5YmI5Nzk5NzQ1YzQ2OTQ3NTFkODljNjE2MDY0NWFmNDk4YyJ9XQ==",
      "amount":100000000000,
      "amountWithFee":100001000000,
      "fee":1000000,
      "coinbase":false,
      "createdAt":1569275654,
      "publicKey":"03745656494ed84d599c0d3a62ced6b1e9d8e7c2240e8dd1e07c0387ae108b4791",
      "signature":"30460221008ba0322d8df6cf56d117ecd583e1dcbda275735d7a050d87bcd70a2cd8feca04022100b28a01fc2bd00548132a49d18f3673b4e5952e2c7f0abe79dfef11752edcce94"
    }
  ]
]
```

**[Back to top](#Get-started)**

## Get block template

| #   | URI                             | Method    | Description                                                |
|-----|---------------------------------|-----------|------------------------------------------------------------|
| 15. | `/getBlockTemplate`             | POST      | Get block template with transactions pool                  |

Request

```
    curl -X POST \
      https://node.inescoin.org/getBlockTemplate \
      -H 'Content-Type: application/json' \
      -d '{
      "walletAddress" : "0x5967a4016501465CD951a1e3984F772AfDeB5207"
    }'
```

Response

```
{
    "id": "0x5967a4016501465CD951a1e3984F772AfDeB5207",
    "data": "W3siaGFzaCI6Ijg3MjNhZWUyY2U5NjY5MDJhYWI3YmNjNWE2OTQwMWVmMjhlZjE3MWU2ZTRiYTc0ZjY1ZTY3ODAwYzVhMWFhNWYiLCJjb25maWdIYXNoIjoiMTk5MjQ5MTNmMDg2MDVlOTlmMGEzYWViMzYxYjlhMDAiLCJiYW5rSGFzaCI6IjA4MGVhOTYwZDUwMmJiYTgyZjc3MTU0MTcyMTM2N2NkMTg1MmQ1OTU4NmViNTVmM2U5MmIwNDFmNDVlMDAxYTgiLCJmcm9tIjoiaW5lc2NvaW4iLCJ0cmFuc2ZlcnMiOiJXM3NpZEc4aU9pSXdlRFU1TmpkaE5EQXhOalV3TVRRMk5VTkVPVFV4WVRGbE16azRORVkzTnpKQlprUmxRalV5TURjaUxDSmhiVzkxYm5RaU9qRXdNREF3TURBd01EQXdNQ3dpYm05dVkyVWlPaUl6T1RNeE16SXpNVE01TXpFek5UTTJNemt6TWpNM016UXpORE0wTXpNeVpUTTFNekF6TVRNeU56QTBPRFVpTENKb1lYTm9Jam9pWVdNNFlUazVOREl6WVRsalpXWTFPVE5qWWprd1pUZGhOV1poT1RVME0yVTVPV1F4WWpnNE1tTTJZbVpqTTJFMk1XWTJNMlUzTmpabE1ETmxORFpsWkNJc0luZGhiR3hsZEVsa0lqb2lJbjFkIiwiYW1vdW50IjoxMDAwMDAwMDAwMDAsImFtb3VudFdpdGhGZWUiOjEwMDAwMDAwMDAwMCwiZmVlIjowLCJjb2luYmFzZSI6dHJ1ZSwiY3JlYXRlZEF0IjoxNTY5Mjc0NDQzLCJwdWJsaWNLZXkiOiIiLCJzaWduYXR1cmUiOiIifV0=",
    "nonce": 0,
    "configHash": "19924913f08605e99f0a3aeb361b9a00",
    "difficulty": 21,
    "height": 102986,
    "createdAt": 1569274443,
    "previousHash": "0000032a0cfa3147526ca5081501d68383ca227a150c8b9c9f58472a3f92b424",
    "previousCumulativeDifficulty": 138401546182,
    "merkleRoot": "0838e69388adc42ba6ec495dbbbe096bce833750d420931d903cb2a8fd010ccd"
}
```

**[Back to top](#Get-started)**

## Submit block hash

| #   | URI                             | Method    | Description                                                |
|-----|---------------------------------|-----------|------------------------------------------------------------|
| 16. | `/submitBlockHash`              | POST      | Submit block hash                                          |

Request

```
    curl -X POST \
      https://node.inescoin.org/submitBlockHash \
      -H 'Content-Type: application/json' \
      -d '{
      "nonce": "090980998980980980980980809809809",
      "hash" : "0x9eea83b0969226c3c499df6486c171ea8e14e13e",
      "walletAddress": "0x9eea83b0969226c3c499df6486c171ea8e14e13e"
    }'
```

Error response

```
[
    {
        "error": "error message"
    }
]
```
Success response

```
[
    'done' => 'ok'
]
```

**[Back to top](#Get-started)**

## Get domain url

| #   | URI                             | Method    | Description                                                |
|-----|---------------------------------|-----------|------------------------------------------------------------|
| 17. | `/get-domain-url`               | POST      | Get domain details                                         |

Request

```
    curl -X POST \
      https://node.inescoin.org/get-domain-url \
      -H 'Content-Type: application/json' \
      -d '{
      "url": "inescoin"
    }'
```

Error response

```
[]
```

Success response

```
{ 
   "hash": "13c7d05beb152f1944ac3eac1bf0b1deda700cdd3df02375bef3927a635a6cf9",
   "url": "inescoin",
   "body": "eyJodG1sIjp7ImVuIjp7ImxhYmVsIjoiRW5nbGlzaCIsIndlYnNpdGUiOnsidGl0bGUiOiJJbmVzY29pbiIsImljb24iOiIiLCJ0aW1lem9uZSI6IiIsImFjdGl2ZSI6dHJ1ZSwiYW5hbHl0aWNzIjp7ImFjdGl2ZSI6ZmFsc2UsImNvZGUiOiIifSwibWV0YSI6W3sidHlwZSI6Im5hbWUiLCJuYW1lIjoiZGVzY3JpcHRpb24iLCJjb250ZW50IjoiSW5lc2NvaW4sIERvbWFpbiwgV2Vic2l0ZSBhbmQgTWVzc2VuZ2VyIGludG8gQmxvY2tjaGFpbiJ9LHsidHlwZSI6Im5hbWUiLCJuYW1lIjoia2V5d29yZHMiLCJjb250ZW50IjoiSW5lc2NvaW4sIGJsb2NrY2hhaW4sIGRvbWFpbiwgY3J5cHRvLCB3ZWJzaXRlLCBtZXNzZW5nZXIifSx7InR5cGUiOiJuYW1lIiwibmFtZSI6ImF1dGhvciIsImNvbnRlbnQiOiJJbmVzY29pbiBOZXR3b3JrIn1dfSwiY29tcGFueSI6eyJuYW1lIjoiSW5lc2NvaW4iLCJzbG9nYW4iOiIiLCJkZXNjcmlwdGlvbiI6ImZkc2ZzZGZzZGYiLCJsb2dvIjoiIiwieWVhciI6MjAxOSwidGVybXNPZlNlcnZpY2UiOiIiLCJ0ZXJtc09mU2FsZXMiOiIiLCJwcml2YWN5UG9saWN5IjoiIiwiZmFxIjoiIn0sImxvY2F0aW9uIjpbeyJhZGRyZXNzIjoiIiwicmVnaW9uIjoiIiwiemlwY29kZSI6IiIsImNpdHkiOiIiLCJjb3VudHJ5IjoiIiwibG9uZ2l0dWRlIjoiIiwibGF0aXR1ZGUiOiIiLCJwaG9uZSI6IiIsImVtYWlsIjoiIn1dLCJuZXR3b3JrIjp7ImdpdGh1YiI6IiIsImZhY2Vib29rIjoiIiwidHdpdHRlciI6IiIsImxpbmtlZGluIjoiIiwieW91dHViZSI6IiIsImluc3RhZ3JhbSI6IiIsIndlY2hhdCI6IiIsIndlaWJvIjoiIiwiZG91eWluIjoiIiwidmtvbnRha3RlIjoiIiwib2Rub0tsYXNzbmlraSI6IiIsInRlbGVncmFtIjoiIiwid2hhdHNhcHAiOiIifSwicGFnZXMiOlt7Im1lbnVUaXRsZSI6IiIsInNob3duSW5NZW51Ijp0cnVlLCJpc0xpbmsiOmZhbHNlLCJsaW5rVXJsIjoiIiwiZGl2SWQiOiIiLCJsYWJlbCI6IiIsImJvZHkiOiI8ZGl2IGNsYXNzPVwiY29udGFpbmVyIGgtMTAwXCI+XG48ZGl2IGNsYXNzPVwicm93IGgtMTAwIGFsaWduLWl0ZW1zLWNlbnRlciBqdXN0aWZ5LWNvbnRlbnQtY2VudGVyIHRleHQtY2VudGVyXCI+XG48ZGl2IGNsYXNzPVwiY29sLWxnLTEwIGFsaWduLXNlbGYtZW5kXCI+XG48aDEgY2xhc3M9XCJ0ZXh0LXVwcGVyY2FzZSB0ZXh0LXdoaXRlIGZvbnQtd2VpZ2h0LWJvbGRcIj5DcmVhdGUgeW91ciBkb21haW4gbmFtZSBhbmQgd2Vic2l0ZSBpbnRvIGJsb2NrY2hhaW4sIHdpdGggZW5jcnlwdGVkIG1lc3NlbmdlcjxcL2gxPlxuPGhyIGNsYXNzPVwiZGl2aWRlciBteS00XCIgXC8+PFwvZGl2PlxuPGRpdiBjbGFzcz1cImNvbC1sZy04IGFsaWduLXNlbGYtYmFzZWxpbmVcIj5cbjxwIGNsYXNzPVwidGV4dC13aGl0ZS03NSBmb250LXdlaWdodC1saWdodCBtYi01XCI+RGVjZW50cmFsaXplZCBCbG9ja2NoYWluIFRlY2hub2xvZ3k8XC9wPlxuPGEgY2xhc3M9XCJidG4gYnRuLWxpZ2h0IGJ0bi14bCBqcy1zY3JvbGwtdHJpZ2dlclwiIGhyZWY9XCJodHRwczpcL1wvZXhwbG9yZXIuaW5lc2NvaW4ub3JnXCI+VHJhbnNhY3Rpb24gJmFtcDsgRG9tYWluIGV4cGxvcmVyPFwvYT4gPGEgY2xhc3M9XCJidG4gYnRuLWxpZ2h0IGJ0bi14bCBqcy1zY3JvbGwtdHJpZ2dlclwiIHRpdGxlPVwiT2ZmbGluZSBXYWxsZXQsIFdlYnNpdGUgQ01TIGFuZCBNZXNzZW5nZXJcIiBocmVmPVwiaHR0cHM6XC9cL3dhbGxldC5pbmVzY29pbi5vcmdcIj5PZmZsaW5lIFdhbGxldCwgV2Vic2l0ZSBDTVMgYW5kIE1lc3NlbmdlcjxcL2E+PFwvZGl2PlxuPFwvZGl2PlxuPFwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6Imh0dHBzOlwvXC9yZXMuY2xvdWRpbmFyeS5jb21cL2R6ZmJ4bHR6eFwvaW1hZ2VcL3VwbG9hZFwvdjE1NzE2NTkyMzFcL0luZXNjb2luXC9iZy1tYXN0aGVhZF94Y2ptMXIuanBnIn0seyJtZW51VGl0bGUiOiJUZWNobm9sb2dpZXMiLCJzaG93bkluTWVudSI6dHJ1ZSwiaXNMaW5rIjpmYWxzZSwibGlua1VybCI6IiIsImRpdklkIjoidGVjaG5vbG9naWVzIiwibGFiZWwiOiIiLCJib2R5IjoiPGRpdiBjbGFzcz1cImNvbnRhaW5lclwiPlxuPGgyIGNsYXNzPVwidGV4dC1jZW50ZXIgbXQtMFwiPk91ciBUZWNobm9sb2dpZXM8XC9oMj5cbjxociBjbGFzcz1cImRpdmlkZXIgbXktNFwiIFwvPlxuPGRpdiBjbGFzcz1cInJvd1wiPlxuPGRpdiBjbGFzcz1cImNvbC1sZy0zIGNvbC1tZC02IHRleHQtY2VudGVyXCI+XG48ZGl2IGNsYXNzPVwibXQtNVwiPjxlbSBjbGFzcz1cImZhcyBmYS00eCBmYS1oZWFydCB0ZXh0LXByaW1hcnkgbWItNFwiPiZuYnNwOzxcL2VtPlxuPGgzIGNsYXNzPVwiaDQgbWItMlwiPlJlYWN0UEhQPFwvaDM+XG48cCBjbGFzcz1cInRleHQtbXV0ZWQgbWItMFwiPlJlYWN0UEhQIGlzIGEgbG93LWxldmVsIGxpYnJhcnkgZm9yIGV2ZW50LWRyaXZlbiBwcm9ncmFtbWluZyBpbiBQSFAuIEF0IGl0cyBjb3JlIGlzIGFuIGV2ZW50IGxvb3AsIG9uIHRvcCBvZiB3aGljaCBpdCBwcm92aWRlcyBsb3ctbGV2ZWwgdXRpbGl0aWVzLjxcL3A+XG48XC9kaXY+XG48XC9kaXY+XG48ZGl2IGNsYXNzPVwiY29sLWxnLTMgY29sLW1kLTYgdGV4dC1jZW50ZXJcIj5cbjxkaXYgY2xhc3M9XCJtdC01XCI+PGVtIGNsYXNzPVwiZmFzIGZhLTR4IGZhLWRhdGFiYXNlIHRleHQtcHJpbWFyeSBtYi00XCI+Jm5ic3A7PFwvZW0+XG48aDMgY2xhc3M9XCJoNCBtYi0yXCI+RWxhc3RpY3NlYXJjaCBEYXRhYmFzZTxcL2gzPlxuPHAgY2xhc3M9XCJ0ZXh0LW11dGVkIG1iLTBcIj5FbGFzdGljc2VhcmNoIGlzIGEgZGF0YWJhc2UgdGhhdCBzdG9yZXMsIHJldHJpZXZlcywgYW5kIG1hbmFnZXMgZG9jdW1lbnQtb3JpZW50ZWQgYW5kIHNpaS1zdHJ1Y3R1cmVkIGRhdGEuPFwvcD5cbjxcL2Rpdj5cbjxcL2Rpdj5cbjxkaXYgY2xhc3M9XCJjb2wtbGctMyBjb2wtbWQtNiB0ZXh0LWNlbnRlclwiPlxuPGRpdiBjbGFzcz1cIm10LTVcIj48ZW0gY2xhc3M9XCJmYXMgZmEtNHggZmEtbG9jayB0ZXh0LXByaW1hcnkgbWItNFwiPiZuYnNwOzxcL2VtPlxuPGgzIGNsYXNzPVwiaDQgbWItMlwiPkJsb2NrY2hhaW48XC9oMz5cbjxwIGNsYXNzPVwidGV4dC1tdXRlZCBtYi0wXCI+QSBibG9ja2NoYWluLCBvcmlnaW5hbGx5IGJsb2NrIGNoYWluLCBpcyBhIGdyb3dpbmcgbGlzdCBvZiByZWNvcmRzLCBjYWxsZWQgYmxvY2tzLCB0aGF0IGFyZSBsaW5rZWQgdXNpbmcgY3J5cHRvZ3JhcGh5LjxcL3A+XG48XC9kaXY+XG48XC9kaXY+XG48ZGl2IGNsYXNzPVwiY29sLWxnLTMgY29sLW1kLTYgdGV4dC1jZW50ZXJcIj5cbjxkaXYgY2xhc3M9XCJtdC01XCI+PGVtIGNsYXNzPVwiZmFzIGZhLTR4IGZhLWdsb2JlIHRleHQtcHJpbWFyeSBtYi00XCI+Jm5ic3A7PFwvZW0+XG48aDMgY2xhc3M9XCJoNCBtYi0yXCI+UDJQIE5ldHdvdGs8XC9oMz5cbjxwIGNsYXNzPVwidGV4dC1tdXRlZCBtYi0wXCI+U3RhbmRzIGZvciBcIlBlZXIgdG8gUGVlci5cIiBJbiBhIFAyUCBuZXR3b3JrLCB0aGUgXCJwZWVyc1wiIGFyZSBjb21wdXRlciBzeXN0ZW1zIHdoaWNoIGFyZSBjb25uZWN0ZWQgdG8gZWFjaCBvdGhlciB2aWEgdGhlIEludGVybmV0LjxcL3A+XG48XC9kaXY+XG48XC9kaXY+XG48XC9kaXY+XG48XC9kaXY+IiwiYmFja2dyb3VuZE9wYWNpdHkiOjEwMCwiaGVpZ2h0IjoiIiwiYmFja2dyb3VuZEltYWdlIjoiIn0seyJtZW51VGl0bGUiOiIiLCJzaG93bkluTWVudSI6ZmFsc2UsImlzTGluayI6ZmFsc2UsImxpbmtVcmwiOiIiLCJkaXZJZCI6IiIsImxhYmVsIjoiIiwiYm9keSI6IjxkaXYgY2xhc3M9XCJjb250YWluZXIgdGV4dC1jZW50ZXJcIj48aW1nIGNsYXNzPVwiaW1nLWZsdWlkXCIgc3JjPVwiaHR0cHM6XC9cL3Jlcy5jbG91ZGluYXJ5LmNvbVwvZHpmYnhsdHp4XC9pbWFnZVwvdXBsb2FkXC92MTU3MTY1OTIzMVwvSW5lc2NvaW5cL2luZXNjb2luLWJsb2NrY2hhaW4tbmV0d29ya19ianFmbTYuanBnXCIgXC8+PFwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiIiwic2hvd25Jbk1lbnUiOmZhbHNlLCJpc0xpbmsiOmZhbHNlLCJsaW5rVXJsIjoiIiwiZGl2SWQiOiIiLCJsYWJlbCI6IiIsImJvZHkiOiI8ZGl2IGNsYXNzPVwiY29udGFpbmVyIHRleHQtY2VudGVyXCI+PGltZyBjbGFzcz1cImltZy1mbHVpZFwiIHNyYz1cImh0dHBzOlwvXC9yZXMuY2xvdWRpbmFyeS5jb21cL2R6ZmJ4bHR6eFwvaW1hZ2VcL3VwbG9hZFwvdjE1NzE2NTkyMzFcL0luZXNjb2luXC9pbmVzY29pbi1ibG9ja2NoYWluLWJsb2NrX2dqc3ZyZi5qcGdcIiBcLz48XC9kaXY+IiwiYmFja2dyb3VuZE9wYWNpdHkiOjEwMCwiaGVpZ2h0IjoiIiwiYmFja2dyb3VuZEltYWdlIjoiIn0seyJtZW51VGl0bGUiOiIiLCJzaG93bkluTWVudSI6ZmFsc2UsImlzTGluayI6ZmFsc2UsImxpbmtVcmwiOiIiLCJkaXZJZCI6IiIsImxhYmVsIjoiIiwiYm9keSI6IjxkaXYgY2xhc3M9XCJjb250YWluZXIgdGV4dC1jZW50ZXJcIj48aW1nIGNsYXNzPVwiaW1nLWZsdWlkXCIgc3JjPVwiaHR0cHM6XC9cL3Jlcy5jbG91ZGluYXJ5LmNvbVwvZHpmYnhsdHp4XC9pbWFnZVwvdXBsb2FkXC92MTU3MTY1OTIzMVwvSW5lc2NvaW5cL2luZXNjb2luLWJsb2NrY2hhaW4tdHJhbnNhY3Rpb24tY29uc2Vuc3VzX3l5ZnltOC5qcGdcIiBcLz48XC9kaXY+IiwiYmFja2dyb3VuZE9wYWNpdHkiOjEwMCwiaGVpZ2h0IjoiIiwiYmFja2dyb3VuZEltYWdlIjoiIn0seyJtZW51VGl0bGUiOiIiLCJzaG93bkluTWVudSI6ZmFsc2UsImlzTGluayI6ZmFsc2UsImxpbmtVcmwiOiIiLCJkaXZJZCI6IiIsImxhYmVsIjoiIiwiYm9keSI6IjxkaXYgY2xhc3M9XCJjb250YWluZXIgdGV4dC1jZW50ZXJcIj48aW1nIGNsYXNzPVwiaW1nLWZsdWlkXCIgc3JjPVwiaHR0cHM6XC9cL3Jlcy5jbG91ZGluYXJ5LmNvbVwvZHpmYnhsdHp4XC9pbWFnZVwvdXBsb2FkXC92MTU3MTY1OTIzMFwvSW5lc2NvaW5cL2luZXNjb2luLWJsb2NrY2hhaW4tYmFuay1jb25zZW5zdXNfYnR5OXVkLmpwZ1wiIFwvPjxcL2Rpdj4iLCJiYWNrZ3JvdW5kT3BhY2l0eSI6MTAwLCJoZWlnaHQiOiIiLCJiYWNrZ3JvdW5kSW1hZ2UiOiIifSx7Im1lbnVUaXRsZSI6IiIsInNob3duSW5NZW51IjpmYWxzZSwiaXNMaW5rIjpmYWxzZSwibGlua1VybCI6IiIsImRpdklkIjoiIiwibGFiZWwiOiIiLCJib2R5IjoiPGRpdiBjbGFzcz1cImNvbnRhaW5lciB0ZXh0LWNlbnRlclwiPjxpbWcgY2xhc3M9XCJpbWctZmx1aWRcIiBzcmM9XCJodHRwczpcL1wvcmVzLmNsb3VkaW5hcnkuY29tXC9kemZieGx0enhcL2ltYWdlXC91cGxvYWRcL3YxNTcxNjU5MjMwXC9JbmVzY29pblwvaW5lc2NvaW4tYmxvY2tjaGFpbi1wZWVycy1jb25zZW5zdXNfY2R5NG5uLmpwZ1wiIFwvPjxcL2Rpdj4iLCJiYWNrZ3JvdW5kT3BhY2l0eSI6MTAwLCJoZWlnaHQiOiIiLCJiYWNrZ3JvdW5kSW1hZ2UiOiIifSx7Im1lbnVUaXRsZSI6IlRlYW0iLCJzaG93bkluTWVudSI6dHJ1ZSwiaXNMaW5rIjpmYWxzZSwibGlua1VybCI6IiIsImRpdklkIjoidGVhbSIsImxhYmVsIjoiIiwiYm9keSI6IjxkaXYgY2xhc3M9XCJjb250YWluZXIgbXQtNFwiPlxuPGgxIGNsYXNzPVwibWItNSB0ZXh0LWNlbnRlclwiPlRlYW08XC9oMT5cbjxkaXYgY2xhc3M9XCJyb3cganVzdGlmeS1jb250ZW50LW1kLWNlbnRlclwiPlxuPGRpdiBjbGFzcz1cImNvbC14bC0zIGNvbC1tZC02IG1iLTRcIj5cbjxkaXYgY2xhc3M9XCJjYXJkIGJvcmRlci0wIHNoYWRvd1wiPjxpbWcgY2xhc3M9XCJjYXJkLWltZy10b3BcIiBzcmM9XCJodHRwczpcL1wvcmVzLmNsb3VkaW5hcnkuY29tXC9kemZieGx0enhcL2ltYWdlXC91cGxvYWRcL3YxNTcxNjU5MjMxXC9JbmVzY29pblwvaW5lc2NvaW4tbW9vbl9oMHE4eWguanBnXCIgYWx0PVwiTW91bmlyIFInUXVpYmFcIiBcLz5cbjxkaXYgY2xhc3M9XCJjYXJkLWJvZHkgdGV4dC1jZW50ZXJcIj5cbjxoNSBjbGFzcz1cImNhcmQtdGl0bGUgbWItMFwiPk1vdW5pciBSJ1F1aWJhPFwvaDU+XG48ZGl2IGNsYXNzPVwiY2FyZC10ZXh0IHRleHQtYmxhY2stNTBcIj5DcmVhdG9yPFwvZGl2PlxuPGRpdiBjbGFzcz1cImNhcmQtdGV4dCB0ZXh0LWJsYWNrLTUwXCI+PGEgaHJlZj1cImh0dHBzOlwvXC9saW5rZWRpbi5jb21cL2luXC9tb3VuaXItci1xdWliYS0xNGFhODRiYVwvXCI+PGVtIGNsYXNzPVwiZmFiIGZhLTJ4IGZhLWxpbmtlZGluIG1iLTRcIj4mbmJzcDs8XC9lbT48XC9hPjxcL2Rpdj5cbjxcL2Rpdj5cbjxcL2Rpdj5cbjxcL2Rpdj5cbjxcL2Rpdj5cbjxcL2Rpdj4iLCJiYWNrZ3JvdW5kT3BhY2l0eSI6MTAwLCJoZWlnaHQiOiIiLCJiYWNrZ3JvdW5kSW1hZ2UiOiIifSx7Im1lbnVUaXRsZSI6IkNvbnRhY3QiLCJzaG93bkluTWVudSI6dHJ1ZSwiaXNMaW5rIjpmYWxzZSwibGlua1VybCI6IiIsImRpdklkIjoiY29udGFjdCIsImxhYmVsIjoiIiwiYm9keSI6IjxkaXYgY2xhc3M9XCJjb250YWluZXJcIj5cbjxkaXYgY2xhc3M9XCJyb3cganVzdGlmeS1jb250ZW50LWNlbnRlclwiPlxuPGRpdiBjbGFzcz1cImNvbC1sZy04IHRleHQtY2VudGVyXCI+XG48aDIgY2xhc3M9XCJtdC0wXCI+TGV0J3MgR2V0IEluIFRvdWNoITxcL2gyPlxuPGhyIGNsYXNzPVwiZGl2aWRlciBteS00XCIgXC8+XG48cCBjbGFzcz1cInRleHQtbXV0ZWQgbWItNVwiPllvdSBjYW4gc3VwcG9ydCB0aGlzIHByb2plY3Q8XC9wPlxuPFwvZGl2PlxuPFwvZGl2PlxuPGRpdiBjbGFzcz1cInJvd1wiPlxuPGRpdiBjbGFzcz1cImNvbC1sZy0xMiBtci1hdXRvIHRleHQtY2VudGVyXCI+PGEgY2xhc3M9XCJibG9ja1wiIGhyZWY9XCJodHRwczpcL1wvZ2l0aHViLmNvbVwvaW5lc2NvaW5cIj48ZW0gY2xhc3M9XCJmYWIgZmEtZ2l0aHViIGZhLTN4IG1iLTMgdGV4dC1tdXRlZFwiPiZuYnNwOzxcL2VtPjxcL2E+IDxhIGNsYXNzPVwiYmxvY2tcIiBocmVmPVwiaHR0cHM6XC9cL3QubWVcL2pvaW5jaGF0XC9JVEQwRUJNY1NiYlNBTGdXZ1JSbFd3XCI+PGVtIGNsYXNzPVwiZmFiIGZhLXRlbGVncmFtIGZhLTN4IG1iLTMgdGV4dC1tdXRlZFwiPiZuYnNwOzxcL2VtPjxcL2E+PFwvZGl2PlxuPFwvZGl2PlxuPFwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiRXhwbG9yZXIiLCJzaG93bkluTWVudSI6dHJ1ZSwiaXNMaW5rIjp0cnVlLCJsaW5rVXJsIjoiaHR0cHM6XC9cL2V4cGxvcmVyLmluZXNjb2luLm9yZyIsImRpdklkIjoiIiwibGFiZWwiOiIiLCJib2R5IjoiIiwiYmFja2dyb3VuZE9wYWNpdHkiOjEwMCwiaGVpZ2h0IjoiIiwiYmFja2dyb3VuZEltYWdlIjoiIn0seyJtZW51VGl0bGUiOiJXYWxsZXQiLCJzaG93bkluTWVudSI6dHJ1ZSwiaXNMaW5rIjp0cnVlLCJsaW5rVXJsIjoiaHR0cHM6XC9cL3dhbGxldC5pbmVzY29pbi5vcmciLCJkaXZJZCI6IiIsImxhYmVsIjoiIiwiYm9keSI6IiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9XSwidGhlbWUiOnsianMiOnsidmFsdWUiOiIoZnVuY3Rpb24oJCkge1xuICBcInVzZSBzdHJpY3RcIjsgXC9cLyBTdGFydCBvZiB1c2Ugc3RyaWN0XG5cbiAgXC9cLyBTbW9vdGggc2Nyb2xsaW5nIHVzaW5nIGpRdWVyeSBlYXNpbmdcbiAgJCgnYS5qcy1zY3JvbGwtdHJpZ2dlcltocmVmKj1cIiNcIl06bm90KFtocmVmPVwiI1wiXSknKS5jbGljayhmdW5jdGlvbigpIHtcbiAgICBpZiAobG9jYXRpb24ucGF0aG5hbWUucmVwbGFjZShcL15cXFwvXC8sICcnKSA9PSB0aGlzLnBhdGhuYW1lLnJlcGxhY2UoXC9eXFxcL1wvLCAnJykgJiYgbG9jYXRpb24uaG9zdG5hbWUgPT0gdGhpcy5ob3N0bmFtZSkge1xuICAgICAgdmFyIHRhcmdldCA9ICQodGhpcy5oYXNoKTtcbiAgICAgIHRhcmdldCA9IHRhcmdldC5sZW5ndGggPyB0YXJnZXQgOiAkKCdbbmFtZT0nICsgdGhpcy5oYXNoLnNsaWNlKDEpICsgJ10nKTtcbiAgICAgIGlmICh0YXJnZXQubGVuZ3RoKSB7XG4gICAgICAgICQoJ2h0bWwsIGJvZHknKS5hbmltYXRlKHtcbiAgICAgICAgICBzY3JvbGxUb3A6ICh0YXJnZXQub2Zmc2V0KCkudG9wIC0gNzIpXG4gICAgICAgIH0sIDEwMDAsIFwiZWFzZUluT3V0RXhwb1wiKTtcbiAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgfVxuICAgIH1cbiAgfSk7XG5cbiAgXC9cLyBDbG9zZXMgcmVzcG9uc2l2ZSBtZW51IHdoZW4gYSBzY3JvbGwgdHJpZ2dlciBsaW5rIGlzIGNsaWNrZWRcbiAgJCgnLmpzLXNjcm9sbC10cmlnZ2VyJykuY2xpY2soZnVuY3Rpb24oKSB7XG4gICAgJCgnLm5hdmJhci1jb2xsYXBzZScpLmNvbGxhcHNlKCdoaWRlJyk7XG4gIH0pO1xuXG4gIFwvXC8gQWN0aXZhdGUgc2Nyb2xsc3B5IHRvIGFkZCBhY3RpdmUgY2xhc3MgdG8gbmF2YmFyIGl0ZW1zIG9uIHNjcm9sbFxuICAkKCdib2R5Jykuc2Nyb2xsc3B5KHtcbiAgICB0YXJnZXQ6ICcjbWFpbk5hdicsXG4gICAgb2Zmc2V0OiA3NVxuICB9KTtcblxuICBcL1wvIENvbGxhcHNlIE5hdmJhclxuICB2YXIgbmF2YmFyQ29sbGFwc2UgPSBmdW5jdGlvbigpIHtcbiAgICBpZiAoJChcIiNtYWluTmF2XCIpLm9mZnNldCgpLnRvcCA+IDEwMCkge1xuICAgICAgJChcIiNtYWluTmF2XCIpLmFkZENsYXNzKFwibmF2YmFyLXNjcm9sbGVkXCIpO1xuICAgIH0gZWxzZSB7XG4gICAgICAkKFwiI21haW5OYXZcIikucmVtb3ZlQ2xhc3MoXCJuYXZiYXItc2Nyb2xsZWRcIik7XG4gICAgfVxuICB9O1xuICBcL1wvIENvbGxhcHNlIG5vdyBpZiBwYWdlIGlzIG5vdCBhdCB0b3BcbiAgbmF2YmFyQ29sbGFwc2UoKTtcbiAgXC9cLyBDb2xsYXBzZSB0aGUgbmF2YmFyIHdoZW4gcGFnZSBpcyBzY3JvbGxlZFxuICAkKHdpbmRvdykuc2Nyb2xsKG5hdmJhckNvbGxhcHNlKTtcbn0pKGpRdWVyeSk7XG4iLCJsaW5rcyI6W3sibGluayI6IlwvXC9jb2RlLmpxdWVyeS5jb21cL2pxdWVyeS0zLjMuMS5taW4uanMifSx7ImxpbmsiOiJcL1wvc3RhY2twYXRoLmJvb3RzdHJhcGNkbi5jb21cL2Jvb3RzdHJhcFwvNC4zLjFcL2pzXC9ib290c3RyYXAubWluLmpzIn0seyJsaW5rIjoiXC9cL2NkbmpzLmNsb3VkZmxhcmUuY29tXC9hamF4XC9saWJzXC9wb3BwZXIuanNcLzEuMTQuN1wvdW1kXC9wb3BwZXIubWluLmpzIn0seyJsaW5rIjoiXC9cL2NkbmpzLmNsb3VkZmxhcmUuY29tXC9hamF4XC9saWJzXC9qcXVlcnktZWFzaW5nXC8xLjQuMVwvanF1ZXJ5LmVhc2luZy5taW4uanMifSx7ImxpbmsiOiJcL1wvY2RuanMuY2xvdWRmbGFyZS5jb21cL2FqYXhcL2xpYnNcL21hZ25pZmljLXBvcHVwLmpzXC8xLjEuMFwvanF1ZXJ5Lm1hZ25pZmljLXBvcHVwLm1pbi5qcyJ9XX0sImNzcyI6eyJ2YWx1ZSI6ImJvZHksXG5odG1sIHtcbiAgd2lkdGg6IDEwMCU7XG4gIGhlaWdodDogMTAwJTtcbn1cblxuLnRleHQtd2hpdGUtNzUge1xuICBjb2xvcjogcmdiYSgyNTUsIDI1NSwgMjU1LCAwLjc1KTtcbn1cblxuaHIuZGl2aWRlciB7XG4gIG1heC13aWR0aDogMy4yNXJlbTtcbiAgYm9yZGVyLXdpZHRoOiAwLjJyZW07XG4gIGJvcmRlci1jb2xvcjogIzk5MDBDQztcbn1cblxuaHIubGlnaHQge1xuICBib3JkZXItY29sb3I6ICNmZmY7XG59XG5cbi5idG4ge1xuICBmb250LWZhbWlseTogXCJNZXJyaXdlYXRoZXIgU2Fuc1wiLCAtYXBwbGUtc3lzdGVtLCBCbGlua01hY1N5c3RlbUZvbnQsIFwiU2Vnb2UgVUlcIiwgUm9ib3RvLCBcIkhlbHZldGljYSBOZXVlXCIsIEFyaWFsLCBcIk5vdG8gU2Fuc1wiLCBzYW5zLXNlcmlmLCBcIkFwcGxlIENvbG9yIEVtb2ppXCIsIFwiU2Vnb2UgVUkgRW1vamlcIiwgXCJTZWdvZSBVSSBTeW1ib2xcIiwgXCJOb3RvIENvbG9yIEVtb2ppXCI7XG59XG5cbi5idG4teGwge1xuICBwYWRkaW5nOiAxLjI1cmVtIDIuMjVyZW07XG4gIGZvbnQtc2l6ZTogMC44NXJlbTtcbiAgZm9udC13ZWlnaHQ6IDcwMDtcbiAgdGV4dC10cmFuc2Zvcm06IHVwcGVyY2FzZTtcbiAgYm9yZGVyOiBub25lO1xuICBib3JkZXItcmFkaXVzOiAxMHJlbTtcbn1cblxuLnBhZ2Utc2VjdGlvbiB7XG4gIHBhZGRpbmc6IDNyZW0gMDtcbn1cblxuI21haW5OYXYge1xuICAtd2Via2l0LWJveC1zaGFkb3c6IDAgMC41cmVtIDFyZW0gcmdiYSgwLCAwLCAwLCAwLjE1KTtcbiAgYm94LXNoYWRvdzogMCAwLjVyZW0gMXJlbSByZ2JhKDAsIDAsIDAsIDAuMTUpO1xuICBiYWNrZ3JvdW5kLWNvbG9yOiAjZmZmO1xuICAtd2Via2l0LXRyYW5zaXRpb246IGJhY2tncm91bmQtY29sb3IgMC4ycyBlYXNlO1xuICB0cmFuc2l0aW9uOiBiYWNrZ3JvdW5kLWNvbG9yIDAuMnMgZWFzZTtcbn1cblxuI21haW5OYXYgLm5hdmJhci1icmFuZCB7XG4gIGZvbnQtZmFtaWx5OiBcIk1lcnJpd2VhdGhlciBTYW5zXCIsIC1hcHBsZS1zeXN0ZW0sIEJsaW5rTWFjU3lzdGVtRm9udCwgXCJTZWdvZSBVSVwiLCBSb2JvdG8sIFwiSGVsdmV0aWNhIE5ldWVcIiwgQXJpYWwsIFwiTm90byBTYW5zXCIsIHNhbnMtc2VyaWYsIFwiQXBwbGUgQ29sb3IgRW1vamlcIiwgXCJTZWdvZSBVSSBFbW9qaVwiLCBcIlNlZ29lIFVJIFN5bWJvbFwiLCBcIk5vdG8gQ29sb3IgRW1vamlcIjtcbiAgZm9udC13ZWlnaHQ6IDcwMDtcbiAgY29sb3I6ICMyMTI1Mjk7XG59XG5cbiNtYWluTmF2IC5uYXZiYXItbmF2IC5uYXYtaXRlbSAubmF2LWxpbmsge1xuICBjb2xvcjogIzZjNzU3ZDtcbiAgZm9udC1mYW1pbHk6IFwiTWVycml3ZWF0aGVyIFNhbnNcIiwgLWFwcGxlLXN5c3RlbSwgQmxpbmtNYWNTeXN0ZW1Gb250LCBcIlNlZ29lIFVJXCIsIFJvYm90bywgXCJIZWx2ZXRpY2EgTmV1ZVwiLCBBcmlhbCwgXCJOb3RvIFNhbnNcIiwgc2Fucy1zZXJpZiwgXCJBcHBsZSBDb2xvciBFbW9qaVwiLCBcIlNlZ29lIFVJIEVtb2ppXCIsIFwiU2Vnb2UgVUkgU3ltYm9sXCIsIFwiTm90byBDb2xvciBFbW9qaVwiO1xuICBmb250LXdlaWdodDogNzAwO1xuICBmb250LXNpemU6IDAuOXJlbTtcbiAgcGFkZGluZzogMC43NXJlbSAwO1xufVxuXG4jbWFpbk5hdiAubmF2YmFyLW5hdiAubmF2LWl0ZW0gLm5hdi1saW5rOmhvdmVyLCAjbWFpbk5hdiAubmF2YmFyLW5hdiAubmF2LWl0ZW0gLm5hdi1saW5rOmFjdGl2ZSB7XG4gIGNvbG9yOiAjOTkwMENDO1xufVxuXG4jbWFpbk5hdiAubmF2YmFyLW5hdiAubmF2LWl0ZW0gLm5hdi1saW5rLmFjdGl2ZSB7XG4gIGNvbG9yOiAjOTkwMENDICFpbXBvcnRhbnQ7XG59XG5cbkBtZWRpYSAobWluLXdpZHRoOiA5OTJweCkge1xuICAjbWFpbk5hdiB7XG4gICAgLXdlYmtpdC1ib3gtc2hhZG93OiBub25lO1xuICAgIGJveC1zaGFkb3c6IG5vbmU7XG4gICAgYmFja2dyb3VuZC1jb2xvcjogdHJhbnNwYXJlbnQ7XG4gIH1cbiAgI21haW5OYXYgLm5hdmJhci1icmFuZCB7XG4gICAgY29sb3I6IHJnYmEoMjU1LCAyNTUsIDI1NSwgMC43KTtcbiAgfVxuICAjbWFpbk5hdiAubmF2YmFyLWJyYW5kOmhvdmVyIHtcbiAgICBjb2xvcjogI2ZmZjtcbiAgfVxuICAjbWFpbk5hdiAubmF2YmFyLW5hdiAubmF2LWl0ZW0gLm5hdi1saW5rIHtcbiAgICBjb2xvcjogcmdiYSgyNTUsIDI1NSwgMjU1LCAwLjcpO1xuICAgIHBhZGRpbmc6IDAgMXJlbTtcbiAgfVxuICAjbWFpbk5hdiAubmF2YmFyLW5hdiAubmF2LWl0ZW0gLm5hdi1saW5rOmhvdmVyIHtcbiAgICBjb2xvcjogI2ZmZjtcbiAgfVxuICAjbWFpbk5hdiAubmF2YmFyLW5hdiAubmF2LWl0ZW06bGFzdC1jaGlsZCAubmF2LWxpbmsge1xuICAgIHBhZGRpbmctcmlnaHQ6IDA7XG4gIH1cbiAgI21haW5OYXYubmF2YmFyLXNjcm9sbGVkIHtcbiAgICAtd2Via2l0LWJveC1zaGFkb3c6IDAgMC41cmVtIDFyZW0gcmdiYSgwLCAwLCAwLCAwLjE1KTtcbiAgICBib3gtc2hhZG93OiAwIDAuNXJlbSAxcmVtIHJnYmEoMCwgMCwgMCwgMC4xNSk7XG4gICAgYmFja2dyb3VuZC1jb2xvcjogI2ZmZjtcbiAgfVxuICAjbWFpbk5hdi5uYXZiYXItc2Nyb2xsZWQgLm5hdmJhci1icmFuZCB7XG4gICAgY29sb3I6ICMyMTI1Mjk7XG4gIH1cbiAgI21haW5OYXYubmF2YmFyLXNjcm9sbGVkIC5uYXZiYXItYnJhbmQ6aG92ZXIge1xuICAgIGNvbG9yOiAjOTkwMENDO1xuICB9XG4gICNtYWluTmF2Lm5hdmJhci1zY3JvbGxlZCAubmF2YmFyLW5hdiAubmF2LWl0ZW0gLm5hdi1saW5rIHtcbiAgICBjb2xvcjogIzIxMjUyOTtcbiAgfVxuICAjbWFpbk5hdi5uYXZiYXItc2Nyb2xsZWQgLm5hdmJhci1uYXYgLm5hdi1pdGVtIC5uYXYtbGluazpob3ZlciB7XG4gICAgY29sb3I6ICM5OTAwQ0M7XG4gIH1cbn1cblxuaGVhZGVyLm1hc3RoZWFkIHtcbiAgcGFkZGluZy10b3A6IDEwcmVtO1xuICBwYWRkaW5nLWJvdHRvbTogY2FsYygxMHJlbSAtIDcycHgpO1xuICBiYWNrZ3JvdW5kOiAtd2Via2l0LWdyYWRpZW50KGxpbmVhciwgbGVmdCB0b3AsIGxlZnQgYm90dG9tLCBmcm9tKHJnYmEoOTIsIDc3LCA2NiwgMC44KSksIHRvKHJnYmEoOTIsIDc3LCA2NiwgMC44KSkpLCB1cmwoXCIuLlwvaW1nXC9iZy1tYXN0aGVhZC5qcGdcIik7XG4gIGJhY2tncm91bmQ6IGxpbmVhci1ncmFkaWVudCh0byBib3R0b20sIHJnYmEoOTIsIDc3LCA2NiwgMC44KSAwJSwgcmdiYSg5MiwgNzcsIDY2LCAwLjgpIDEwMCUpLCB1cmwoXCIuLlwvaW1nXC9iZy1tYXN0aGVhZC5qcGdcIik7XG4gIGJhY2tncm91bmQtcG9zaXRpb246IGNlbnRlcjtcbiAgYmFja2dyb3VuZC1yZXBlYXQ6IG5vLXJlcGVhdDtcbiAgYmFja2dyb3VuZC1hdHRhY2htZW50OiBzY3JvbGw7XG4gIGJhY2tncm91bmQtc2l6ZTogY292ZXI7XG59XG5cbmhlYWRlci5tYXN0aGVhZCBoMSB7XG4gIGZvbnQtc2l6ZTogMi4yNXJlbTtcbn1cblxuQG1lZGlhIChtaW4td2lkdGg6IDk5MnB4KSB7XG4gIGhlYWRlci5tYXN0aGVhZCB7XG4gICAgaGVpZ2h0OiAxMDB2aDtcbiAgICBtaW4taGVpZ2h0OiA0MHJlbTtcbiAgICBwYWRkaW5nLXRvcDogNzJweDtcbiAgICBwYWRkaW5nLWJvdHRvbTogMDtcbiAgfVxuICBoZWFkZXIubWFzdGhlYWQgcCB7XG4gICAgZm9udC1zaXplOiAxLjE1cmVtO1xuICB9XG4gIGhlYWRlci5tYXN0aGVhZCBoMSB7XG4gICAgZm9udC1zaXplOiAzcmVtO1xuICB9XG59XG5cbkBtZWRpYSAobWluLXdpZHRoOiAxMjAwcHgpIHtcbiAgaGVhZGVyLm1hc3RoZWFkIGgxIHtcbiAgICBmb250LXNpemU6IDMuNXJlbTtcbiAgfVxufVxuXG4jcG9ydGZvbGlvIC5jb250YWluZXItZmx1aWQge1xuICBtYXgtd2lkdGg6IDE5MjBweDtcbn1cblxuI3BvcnRmb2xpbyAuY29udGFpbmVyLWZsdWlkIC5wb3J0Zm9saW8tYm94IHtcbiAgcG9zaXRpb246IHJlbGF0aXZlO1xuICBkaXNwbGF5OiBibG9jaztcbn1cblxuI3BvcnRmb2xpbyAuY29udGFpbmVyLWZsdWlkIC5wb3J0Zm9saW8tYm94IC5wb3J0Zm9saW8tYm94LWNhcHRpb24ge1xuICBkaXNwbGF5OiAtd2Via2l0LWJveDtcbiAgZGlzcGxheTogLW1zLWZsZXhib3g7XG4gIGRpc3BsYXk6IGZsZXg7XG4gIC13ZWJraXQtYm94LWFsaWduOiBjZW50ZXI7XG4gIC1tcy1mbGV4LWFsaWduOiBjZW50ZXI7XG4gIGFsaWduLWl0ZW1zOiBjZW50ZXI7XG4gIC13ZWJraXQtYm94LXBhY2s6IGNlbnRlcjtcbiAgLW1zLWZsZXgtcGFjazogY2VudGVyO1xuICBqdXN0aWZ5LWNvbnRlbnQ6IGNlbnRlcjtcbiAgLXdlYmtpdC1ib3gtb3JpZW50OiB2ZXJ0aWNhbDtcbiAgLXdlYmtpdC1ib3gtZGlyZWN0aW9uOiBub3JtYWw7XG4gIC1tcy1mbGV4LWRpcmVjdGlvbjogY29sdW1uO1xuICBmbGV4LWRpcmVjdGlvbjogY29sdW1uO1xuICB3aWR0aDogMTAwJTtcbiAgaGVpZ2h0OiAxMDAlO1xuICBwb3NpdGlvbjogYWJzb2x1dGU7XG4gIGJvdHRvbTogMDtcbiAgdGV4dC1hbGlnbjogY2VudGVyO1xuICBvcGFjaXR5OiAwO1xuICBjb2xvcjogI2ZmZjtcbiAgYmFja2dyb3VuZDogcmdiYSgxNTMsIDAsIDIwNCwgMC45KTtcbiAgLXdlYmtpdC10cmFuc2l0aW9uOiBvcGFjaXR5IDAuMjVzIGVhc2U7XG4gIHRyYW5zaXRpb246IG9wYWNpdHkgMC4yNXMgZWFzZTtcbiAgdGV4dC1hbGlnbjogY2VudGVyO1xufVxuXG4jcG9ydGZvbGlvIC5jb250YWluZXItZmx1aWQgLnBvcnRmb2xpby1ib3ggLnBvcnRmb2xpby1ib3gtY2FwdGlvbiAucHJvamVjdC1jYXRlZ29yeSB7XG4gIGZvbnQtZmFtaWx5OiBcIk1lcnJpd2VhdGhlciBTYW5zXCIsIC1hcHBsZS1zeXN0ZW0sIEJsaW5rTWFjU3lzdGVtRm9udCwgXCJTZWdvZSBVSVwiLCBSb2JvdG8sIFwiSGVsdmV0aWNhIE5ldWVcIiwgQXJpYWwsIFwiTm90byBTYW5zXCIsIHNhbnMtc2VyaWYsIFwiQXBwbGUgQ29sb3IgRW1vamlcIiwgXCJTZWdvZSBVSSBFbW9qaVwiLCBcIlNlZ29lIFVJIFN5bWJvbFwiLCBcIk5vdG8gQ29sb3IgRW1vamlcIjtcbiAgZm9udC1zaXplOiAwLjg1cmVtO1xuICBmb250LXdlaWdodDogNjAwO1xuICB0ZXh0LXRyYW5zZm9ybTogdXBwZXJjYXNlO1xufVxuXG4jcG9ydGZvbGlvIC5jb250YWluZXItZmx1aWQgLnBvcnRmb2xpby1ib3ggLnBvcnRmb2xpby1ib3gtY2FwdGlvbiAucHJvamVjdC1uYW1lIHtcbiAgZm9udC1zaXplOiAxLjJyZW07XG59XG5cbiNwb3J0Zm9saW8gLmNvbnRhaW5lci1mbHVpZCAucG9ydGZvbGlvLWJveDpob3ZlciAucG9ydGZvbGlvLWJveC1jYXB0aW9uIHtcbiAgb3BhY2l0eTogMTtcbn0iLCJsaW5rcyI6W3sibGluayI6IlwvXC9jZG5qcy5jbG91ZGZsYXJlLmNvbVwvYWpheFwvbGlic1wvZm9udC1hd2Vzb21lXC81LjEwLjJcL2Nzc1wvYWxsLm1pbi5jc3MifSx7ImxpbmsiOiJcL1wvZm9udHMuZ29vZ2xlYXBpcy5jb21cL2Nzcz9mYW1pbHk9TWVycml3ZWF0aGVyK1NhbnM6NDAwLDcwMCJ9LHsibGluayI6IlwvXC9mb250cy5nb29nbGVhcGlzLmNvbVwvY3NzP2ZhbWlseT1NZXJyaXdlYXRoZXI6NDAwLDMwMCwzMDBpdGFsaWMsNDAwaXRhbGljLDcwMCw3MDBpdGFsaWMifSx7ImxpbmsiOiJcL1wvY2RuanMuY2xvdWRmbGFyZS5jb21cL2FqYXhcL2xpYnNcL21hZ25pZmljLXBvcHVwLmpzXC8xLjEuMFwvbWFnbmlmaWMtcG9wdXAubWluLmNzcyJ9LHsibGluayI6IlwvXC9zdGFja3BhdGguYm9vdHN0cmFwY2RuLmNvbVwvYm9vdHN0cmFwXC80LjMuMVwvY3NzXC9ib290c3RyYXAubWluLmNzcyJ9XX19LCJwcm9kdWN0cyI6W3siYWN0aXZlIjoiZmFsc2UiLCJza3UiOiI2ZTNkNWQxYy02OTZiLTQyMDYtODEzZi1hMjdmZGY4YmI1ZTIiLCJ0aXRsZSI6Ikx1ZWlsd2l0ei1IYWhuIiwiZGVzY3JpcHRpb24iOiJ1dCB0ZWxsdXMgbnVsbGEgdXQgZXJhdCBpZCBtYXVyaXMgdnVscHV0YXRlIGVsZW1lbnR1bSBudWxsYW0gdmFyaXVzIG51bGxhIiwiYW1vdW50IjoiNjUiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDpcL1wvZHVtbXlpbWFnZS5jb21cLzMwMHg0MDAuYm1wXC9mZjQ0NDRcL2ZmZmZmZiJ9LHsiYWN0aXZlIjoiIiwic2t1IjoiNDA5ZmJiNjMtNThkNS00NjcxLWEzMTMtMDc3ZTExNDU0NjU0IiwidGl0bGUiOiJPc2luc2tpLCBIZXJtYW5uIGFuZCBXaWxsIiwiZGVzY3JpcHRpb24iOiJ1dCBzdXNjaXBpdCBhIGZldWdpYXQgZXQgZXJvcyB2ZXN0aWJ1bHVtIGFjIGVzdCBsYWNpbmlhIG5pc2kgdmVuZW5hdGlzIHRyaXN0aXF1ZSBmdXNjZSBjb25ndWUgZGlhbSIsImFtb3VudCI6Ijc5IiwiY3VycmVuY3kiOiJ1c2QiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmpwZ1wvZGRkZGRkXC8wMDAwMDAifSx7ImFjdGl2ZSI6InRydWUiLCJza3UiOiIxODFlM2U5Ni0zMTdiLTQwZTgtYWNjNi0zZDgzYTU2MWEzNGIiLCJ0aXRsZSI6IlR1cmNvdHRlLURpZXRyaWNoIiwiZGVzY3JpcHRpb24iOiJxdWlzIGxpYmVybyBudWxsYW0gc2l0IGFtZXQgdHVycGlzIGVsZW1lbnR1bSBsaWd1bGEgdmVoaWN1bGEgY29uc2VxdWF0IG1vcmJpIGEgaXBzdW0gaW50ZWdlciBhIG5pYmgiLCJhbW91bnQiOiI1NSIsImN1cnJlbmN5IjoidXNkIiwiaW1hZ2UiOiJodHRwOlwvXC9kdW1teWltYWdlLmNvbVwvMzAweDQwMC5wbmdcL2NjMDAwMFwvZmZmZmZmIn0seyJhY3RpdmUiOiJmYWxzZSIsInNrdSI6IjQxZjU1YTlhLTZjNDktNDE0Zi1hZDM1LTZmZjgzMWE2YzY1ZCIsInRpdGxlIjoiQ3J1aWNrc2hhbmstV2Vpc3NuYXQiLCJkZXNjcmlwdGlvbiI6ImVyYXQgdmVzdGlidWx1bSBzZWQgbWFnbmEgYXQgbnVuYyBjb21tb2RvIHBsYWNlcmF0IHByYWVzZW50IGJsYW5kaXQgbmFtIiwiYW1vdW50IjoiMzMiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDpcL1wvZHVtbXlpbWFnZS5jb21cLzMwMHg0MDAucG5nXC9jYzAwMDBcL2ZmZmZmZiJ9LHsiYWN0aXZlIjoiIiwic2t1IjoiZmVmMTg3OTYtZjFkNi00MzU5LTg1MDAtMTIyOGNjOTkxM2EwIiwidGl0bGUiOiJTY2hhZGVuLUZlZW5leSIsImRlc2NyaXB0aW9uIjoicXVpc3F1ZSBlcmF0IGVyb3Mgdml2ZXJyYSBlZ2V0IGNvbmd1ZSBlZ2V0IHNlbXBlciBydXRydW0gbnVsbGEgbnVuYyBwdXJ1cyBwaGFzZWxsdXMgaW4iLCJhbW91bnQiOiIyOSIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOlwvXC9kdW1teWltYWdlLmNvbVwvMzAweDQwMC5wbmdcLzVmYTJkZFwvZmZmZmZmIn0seyJhY3RpdmUiOiJ0cnVlIiwic2t1IjoiOTM1ZTQ4MDYtOWNhNy00MWQ1LWFmNmItMWMxZjE3NWU3MWNlIiwidGl0bGUiOiJIYW1pbGwgR3JvdXAiLCJkZXNjcmlwdGlvbiI6InV0IG9kaW8gY3JhcyBtaSBwZWRlIG1hbGVzdWFkYSBpbiBpbXBlcmRpZXQgZXQgY29tbW9kbyB2dWxwdXRhdGUiLCJhbW91bnQiOiIyIiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmJtcFwvY2MwMDAwXC9mZmZmZmYifSx7ImFjdGl2ZSI6IiIsInNrdSI6ImJiM2IzOTMwLWQ4OGEtNDYxOC1iOTVjLTYzYzRhNzY0ZmQ1MiIsInRpdGxlIjoiRmFkZWwsIExlZmZsZXIgYW5kIE1pbGxlciIsImRlc2NyaXB0aW9uIjoibWFnbmEgYmliZW5kdW0gaW1wZXJkaWV0IG51bGxhbSBvcmNpIHBlZGUgdmVuZW5hdGlzIG5vbiBzb2RhbGVzIHNlZCB0aW5jaWR1bnQgZXUgZmVsaXMiLCJhbW91bnQiOiIyNyIsImN1cnJlbmN5IjoidXNkIiwiaW1hZ2UiOiJodHRwOlwvXC9kdW1teWltYWdlLmNvbVwvMzAweDQwMC5qcGdcL2NjMDAwMFwvZmZmZmZmIn0seyJhY3RpdmUiOiIiLCJza3UiOiI3OWMwZWU0Ni01ZTRkLTQ2YzgtODFiMS0yMGVlYjJkYjA1MDciLCJ0aXRsZSI6Ik1hbm4gR3JvdXAiLCJkZXNjcmlwdGlvbiI6ImVsZW1lbnR1bSBldSBpbnRlcmR1bSBldSB0aW5jaWR1bnQgaW4gbGVvIG1hZWNlbmFzIHB1bHZpbmFyIGxvYm9ydGlzIGVzdCBwaGFzZWxsdXMgc2l0IGFtZXQgZXJhdCIsImFtb3VudCI6IjkxIiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmpwZ1wvZGRkZGRkXC8wMDAwMDAifSx7ImFjdGl2ZSI6InRydWUiLCJza3UiOiIxY2M3YzE4Mi04ZDFmLTQwNGQtODUzMi05Zjg3NGZjZTFjMGUiLCJ0aXRsZSI6Ik1hZ2dpby1Bbmt1bmRpbmciLCJkZXNjcmlwdGlvbiI6Im9kaW8gcG9ydHRpdG9yIGlkIGNvbnNlcXVhdCBpbiBjb25zZXF1YXQgdXQgbnVsbGEgc2VkIGFjY3Vtc2FuIiwiYW1vdW50IjoiNDIiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDpcL1wvZHVtbXlpbWFnZS5jb21cLzMwMHg0MDAuanBnXC9kZGRkZGRcLzAwMDAwMCJ9LHsiYWN0aXZlIjoidHJ1ZSIsInNrdSI6IjQ0YTZiM2RmLTVlMjUtNDAyNC05YTJiLWY3MDRmYjQ1MzZjZCIsInRpdGxlIjoiS2lobiwgTWNLZW56aWUgYW5kIEhlYW5leSIsImRlc2NyaXB0aW9uIjoiYWNjdW1zYW4gdG9ydG9yIHF1aXMgdHVycGlzIHNlZCBhbnRlIHZpdmFtdXMgdG9ydG9yIGR1aXMgbWF0dGlzIGVnZXN0YXMgbWV0dXMgYWVuZWFuIiwiYW1vdW50IjoiODciLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDpcL1wvZHVtbXlpbWFnZS5jb21cLzMwMHg0MDAuanBnXC81ZmEyZGRcL2ZmZmZmZiJ9LHsiYWN0aXZlIjoiIiwic2t1IjoiMThiMTRiYTEtY2VlNy00Y2RhLTg5NzctMWU4MWE0NzgyYzBiIiwidGl0bGUiOiJIYXJ2ZXksIENvbnJveSBhbmQgQmVja2VyIiwiZGVzY3JpcHRpb24iOiJlZ2V0IHJ1dHJ1bSBhdCBsb3JlbSBpbnRlZ2VyIHRpbmNpZHVudCBhbnRlIHZlbCBpcHN1bSBwcmFlc2VudCBibGFuZGl0IGxhY2luaWEgZXJhdCIsImFtb3VudCI6IjQ0IiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmpwZ1wvNWZhMmRkXC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiZTUyMDc3MTQtYzRmNy00ZjY0LWJhYmMtZjM2NTJmY2U1NzNhIiwidGl0bGUiOiJHcmltZXMgSW5jIiwiZGVzY3JpcHRpb24iOiJhbnRlIGlwc3VtIHByaW1pcyBpbiBmYXVjaWJ1cyBvcmNpIGx1Y3R1cyBldCB1bHRyaWNlcyBwb3N1ZXJlIGN1YmlsaWEgY3VyYWUgbnVsbGEgZGFwaWJ1cyBkb2xvciIsImFtb3VudCI6IjQ1IiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmJtcFwvNWZhMmRkXC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiNmZiNjkzYzQtM2FhYi00YjdkLWE5ZDctYWU3ZTEzYzBmMjA2IiwidGl0bGUiOiJWb24tQ3VtbWVyYXRhIiwiZGVzY3JpcHRpb24iOiJhIGxpYmVybyBuYW0gZHVpIHByb2luIGxlbyBvZGlvIHBvcnR0aXRvciBpZCBjb25zZXF1YXQgaW4gY29uc2VxdWF0IHV0IG51bGxhIHNlZCBhY2N1bXNhbiBmZWxpcyB1dCBhdCBkb2xvciIsImFtb3VudCI6IjYzIiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmJtcFwvNWZhMmRkXC9mZmZmZmYifSx7ImFjdGl2ZSI6InRydWUiLCJza3UiOiJmNjliYjk0NS01OGFhLTQ0MjUtYTBlZS1mODQzMzI5NDNiNGYiLCJ0aXRsZSI6IlBmYW5uZXJzdGlsbCwgU2NoaW1tZWwgYW5kIENhcnRlciIsImRlc2NyaXB0aW9uIjoicGVkZSB2ZW5lbmF0aXMgbm9uIHNvZGFsZXMgc2VkIHRpbmNpZHVudCBldSBmZWxpcyBmdXNjZSBwb3N1ZXJlIGZlbGlzIHNlZCBsYWN1cyIsImFtb3VudCI6Ijc1IiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLnBuZ1wvZmY0NDQ0XC9mZmZmZmYifSx7ImFjdGl2ZSI6IiIsInNrdSI6IjNiZjQyMDEyLTMwZDctNDg5NS1iODg0LTBjZDZhM2NlZWZhMCIsInRpdGxlIjoiTWVydHosIFN0ZWhyIGFuZCBDb25uZWxseSIsImRlc2NyaXB0aW9uIjoiZXJhdCB2ZXN0aWJ1bHVtIHNlZCBtYWduYSBhdCBudW5jIGNvbW1vZG8gcGxhY2VyYXQgcHJhZXNlbnQgYmxhbmRpdCBuYW0gbnVsbGEgaW50ZWdlciBwZWRlIiwiYW1vdW50IjoiMTgiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDpcL1wvZHVtbXlpbWFnZS5jb21cLzMwMHg0MDAuanBnXC9jYzAwMDBcL2ZmZmZmZiJ9LHsiYWN0aXZlIjoidHJ1ZSIsInNrdSI6ImE4NzIxZDNmLWU4MDktNGZiZi1iNTdmLTM2OWVlYmRhYjFmMyIsInRpdGxlIjoiRGF2aXMtTGVmZmxlciIsImRlc2NyaXB0aW9uIjoibGVjdHVzIGluIHF1YW0gZnJpbmdpbGxhIHJob25jdXMgbWF1cmlzIGVuaW0gbGVvIHJob25jdXMgc2VkIHZlc3RpYnVsdW0gc2l0IGFtZXQgY3Vyc3VzIGlkIHR1cnBpcyBpbnRlZ2VyIGFsaXF1ZXQiLCJhbW91bnQiOiI3MSIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOlwvXC9kdW1teWltYWdlLmNvbVwvMzAweDQwMC5wbmdcL2ZmNDQ0NFwvZmZmZmZmIn0seyJhY3RpdmUiOiJ0cnVlIiwic2t1IjoiOTEzMTVlOGEtODU5MC00ZTdhLThmYzQtYTljNWYwMTMwYzEzIiwidGl0bGUiOiJHbGVhc29uLUJyYWR0a2UiLCJkZXNjcmlwdGlvbiI6Im1pIGluIHBvcnR0aXRvciBwZWRlIGp1c3RvIGV1IG1hc3NhIGRvbmVjIGRhcGlidXMgZHVpcyIsImFtb3VudCI6Ijk2IiwiY3VycmVuY3kiOiJ1c2QiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmJtcFwvZGRkZGRkXC8wMDAwMDAifSx7ImFjdGl2ZSI6IiIsInNrdSI6IjY2ZTM5OTA1LWQzMjctNGVjZC04NDU1LWRmNmE4OWMwOGIyNCIsInRpdGxlIjoiS2lybGluLUt1aGljIiwiZGVzY3JpcHRpb24iOiJpbiBsaWJlcm8gdXQgbWFzc2Egdm9sdXRwYXQgY29udmFsbGlzIG1vcmJpIG9kaW8gb2RpbyBlbGVtZW50dW0gZXUgaW50ZXJkdW0iLCJhbW91bnQiOiI4NSIsImN1cnJlbmN5IjoidXNkIiwiaW1hZ2UiOiJodHRwOlwvXC9kdW1teWltYWdlLmNvbVwvMzAweDQwMC5ibXBcL2NjMDAwMFwvZmZmZmZmIn0seyJhY3RpdmUiOiJmYWxzZSIsInNrdSI6Ijc4MDRiNTY5LWI1ZjYtNDlkMy1hZTM4LWI2ZGRmZmFlNTJlNCIsInRpdGxlIjoiTydDb25uZXItS29zcyIsImRlc2NyaXB0aW9uIjoic2l0IGFtZXQgc2FwaWVuIGRpZ25pc3NpbSB2ZXN0aWJ1bHVtIHZlc3RpYnVsdW0gYW50ZSBpcHN1bSBwcmltaXMgaW4gZmF1Y2lidXMgb3JjaSBsdWN0dXMgZXQgdWx0cmljZXMgcG9zdWVyZSBjdWJpbGlhIGN1cmFlIiwiYW1vdW50IjoiMzQiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDpcL1wvZHVtbXlpbWFnZS5jb21cLzMwMHg0MDAuanBnXC9kZGRkZGRcLzAwMDAwMCJ9LHsiYWN0aXZlIjoiZmFsc2UiLCJza3UiOiI1ZDI5ZmQyNy0yYjU0LTRhYTAtOWMwNS0zZjc1ODQ2ZmE2MzIiLCJ0aXRsZSI6IlNjaHVsdHogYW5kIFNvbnMiLCJkZXNjcmlwdGlvbiI6InByYWVzZW50IGlkIG1hc3NhIGlkIG5pc2wgdmVuZW5hdGlzIGxhY2luaWEgYWVuZWFuIHNpdCBhbWV0IGp1c3RvIG1vcmJpIHV0IG9kaW8gY3JhcyBtaSBwZWRlIG1hbGVzdWFkYSIsImFtb3VudCI6IjkxIiwiY3VycmVuY3kiOiJ1c2QiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLnBuZ1wvZmY0NDQ0XC9mZmZmZmYifSx7ImFjdGl2ZSI6IiIsInNrdSI6ImE5YmQ0MGMzLTA4YjAtNGEzMS1hMGMwLWZiOGI4ZGMyYTA0OCIsInRpdGxlIjoiU3RyZWljaC1IYW1pbGwiLCJkZXNjcmlwdGlvbiI6ImhlbmRyZXJpdCBhdCB2dWxwdXRhdGUgdml0YWUgbmlzbCBhZW5lYW4gbGVjdHVzIHBlbGxlbnRlc3F1ZSBlZ2V0IG51bmMgZG9uZWMgcXVpcyIsImFtb3VudCI6IjE4IiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmJtcFwvZmY0NDQ0XC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiNjY3NDIwOGYtMzUyYi00MzJkLTlkMDAtNzVjNzQyNmVmMDI5IiwidGl0bGUiOiJQYXVjZWstU2NobWlkdCIsImRlc2NyaXB0aW9uIjoiY29uc2VxdWF0IHV0IG51bGxhIHNlZCBhY2N1bXNhbiBmZWxpcyB1dCBhdCBkb2xvciBxdWlzIG9kaW8gY29uc2VxdWF0IHZhcml1cyBpbnRlZ2VyIiwiYW1vdW50IjoiNDUiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDpcL1wvZHVtbXlpbWFnZS5jb21cLzMwMHg0MDAuYm1wXC81ZmEyZGRcL2ZmZmZmZiJ9LHsiYWN0aXZlIjoiIiwic2t1IjoiNTA5MzI3MzItYjVmMi00ODFmLWJjNzItOTI1N2VjMjU4MjMxIiwidGl0bGUiOiJTdG9rZXMtUGZlZmZlciIsImRlc2NyaXB0aW9uIjoibG9yZW0gaW50ZWdlciB0aW5jaWR1bnQgYW50ZSB2ZWwgaXBzdW0gcHJhZXNlbnQgYmxhbmRpdCBsYWNpbmlhIGVyYXQgdmVzdGlidWx1bSBzZWQgbWFnbmEgYXQiLCJhbW91bnQiOiIzNiIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOlwvXC9kdW1teWltYWdlLmNvbVwvMzAweDQwMC5ibXBcL2NjMDAwMFwvZmZmZmZmIn0seyJhY3RpdmUiOiIiLCJza3UiOiJmZWRiMjcyYS0zMWI2LTQ5MGUtYmVhOS1kMmExZWZjMGRhNWEiLCJ0aXRsZSI6IkNyZW1pbiwgS3NobGVyaW4gYW5kIEt1dGNoIiwiZGVzY3JpcHRpb24iOiJsaWJlcm8gdXQgbWFzc2Egdm9sdXRwYXQgY29udmFsbGlzIG1vcmJpIG9kaW8gb2RpbyBlbGVtZW50dW0gZXUgaW50ZXJkdW0gZXUgdGluY2lkdW50IGluIGxlbyBtYWVjZW5hcyBwdWx2aW5hciBsb2JvcnRpcyBlc3QiLCJhbW91bnQiOiIyMiIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOlwvXC9kdW1teWltYWdlLmNvbVwvMzAweDQwMC5qcGdcLzVmYTJkZFwvZmZmZmZmIn0seyJhY3RpdmUiOiIiLCJza3UiOiI4NzdhNDEyMi0xOTY0LTQ2NzItYmRmOC1jZWVhNTdiMTBkOGYiLCJ0aXRsZSI6IkhhZ2VuZXMgSW5jIiwiZGVzY3JpcHRpb24iOiJjb25ndWUgZWxlbWVudHVtIGluIGhhYyBoYWJpdGFzc2UgcGxhdGVhIGRpY3R1bXN0IG1vcmJpIHZlc3RpYnVsdW0gdmVsaXQgaWQgcHJldGl1bSBpYWN1bGlzIGRpYW0gZXJhdCBmZXJtZW50dW0ganVzdG8iLCJhbW91bnQiOiI5NSIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOlwvXC9kdW1teWltYWdlLmNvbVwvMzAweDQwMC5ibXBcL2RkZGRkZFwvMDAwMDAwIn0seyJhY3RpdmUiOiJ0cnVlIiwic2t1IjoiYzdiZjQxM2QtMjE1MC00MGVkLTgwNWEtNmQyYjc2NWZiYWM0IiwidGl0bGUiOiJGYXkgYW5kIFNvbnMiLCJkZXNjcmlwdGlvbiI6ImF1Y3RvciBzZWQgdHJpc3RpcXVlIGluIHRlbXB1cyBzaXQgYW1ldCBzZW0gZnVzY2UgY29uc2VxdWF0IG51bGxhIiwiYW1vdW50IjoiMzQiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDpcL1wvZHVtbXlpbWFnZS5jb21cLzMwMHg0MDAuanBnXC9jYzAwMDBcL2ZmZmZmZiJ9LHsiYWN0aXZlIjoiZmFsc2UiLCJza3UiOiI1NGVkN2JkNC1hNjg2LTQ5MTItOWUwOC1kZDIzZGVjNzc5OTMiLCJ0aXRsZSI6IkhhbWlsbCwgUG9sbGljaCBhbmQgTW9lbiIsImRlc2NyaXB0aW9uIjoiZWdldCBydXRydW0gYXQgbG9yZW0gaW50ZWdlciB0aW5jaWR1bnQgYW50ZSB2ZWwgaXBzdW0gcHJhZXNlbnQgYmxhbmRpdCBsYWNpbmlhIGVyYXQgdmVzdGlidWx1bSIsImFtb3VudCI6IjE4IiwiY3VycmVuY3kiOiJ1c2QiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmJtcFwvNWZhMmRkXC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiNTVkNTkxNjYtZjczMy00ZTBhLTg4OWUtYjVjODM4ZThiMTk1IiwidGl0bGUiOiJTY2h1bGlzdC1MYW5nIiwiZGVzY3JpcHRpb24iOiJzb2RhbGVzIHNjZWxlcmlzcXVlIG1hdXJpcyBzaXQgYW1ldCBlcm9zIHN1c3BlbmRpc3NlIGFjY3Vtc2FuIHRvcnRvciBxdWlzIHR1cnBpcyBzZWQgYW50ZSB2aXZhbXVzIHRvcnRvciBkdWlzIG1hdHRpcyBlZ2VzdGFzIiwiYW1vdW50IjoiMjEiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDpcL1wvZHVtbXlpbWFnZS5jb21cLzMwMHg0MDAuanBnXC9mZjQ0NDRcL2ZmZmZmZiJ9LHsiYWN0aXZlIjoiIiwic2t1IjoiMzk3ZWFlZmItMGI0Yy00YjFlLWExOGMtYWE4YTg2OTNmNzQ4IiwidGl0bGUiOiJDb25uZWxseSBJbmMiLCJkZXNjcmlwdGlvbiI6Im1hZ25pcyBkaXMgcGFydHVyaWVudCBtb250ZXMgbmFzY2V0dXIgcmlkaWN1bHVzIG11cyB2aXZhbXVzIHZlc3RpYnVsdW0gc2FnaXR0aXMgc2FwaWVuIGN1bSIsImFtb3VudCI6IjQ2IiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmJtcFwvZmY0NDQ0XC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiMDU4ZjBhNzItOTVkZi00NDNkLWFkMGItMDZhNmMyZGM1MjE5IiwidGl0bGUiOiJTdHJhY2tlLCBGZWVzdCBhbmQgQWx0ZW53ZXJ0aCIsImRlc2NyaXB0aW9uIjoidGluY2lkdW50IGluIGxlbyBtYWVjZW5hcyBwdWx2aW5hciBsb2JvcnRpcyBlc3QgcGhhc2VsbHVzIHNpdCBhbWV0IGVyYXQgbnVsbGEgdGVtcHVzIHZpdmFtdXMgaW4gZmVsaXMgZXUgc2FwaWVuIGN1cnN1cyB2ZXN0aWJ1bHVtIiwiYW1vdW50IjoiNjgiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDpcL1wvZHVtbXlpbWFnZS5jb21cLzMwMHg0MDAuanBnXC9jYzAwMDBcL2ZmZmZmZiJ9LHsiYWN0aXZlIjoidHJ1ZSIsInNrdSI6ImVmYzZjZjgwLTFkYTgtNDI1OS05MzJlLTBiZTk1NDVhNWYzMyIsInRpdGxlIjoiVGhpZWwtSGlja2xlIiwiZGVzY3JpcHRpb24iOiJudW5jIHZlc3RpYnVsdW0gYW50ZSBpcHN1bSBwcmltaXMgaW4gZmF1Y2lidXMgb3JjaSBsdWN0dXMgZXQgdWx0cmljZXMgcG9zdWVyZSBjdWJpbGlhIGN1cmFlIG1hdXJpcyB2aXZlcnJhIiwiYW1vdW50IjoiNzUiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDpcL1wvZHVtbXlpbWFnZS5jb21cLzMwMHg0MDAucG5nXC9jYzAwMDBcL2ZmZmZmZiJ9LHsiYWN0aXZlIjoiZmFsc2UiLCJza3UiOiIxNjM3YWVjMS0yZDM2LTRjMWYtYmViOC1hZjNiMTk3ODU5NTkiLCJ0aXRsZSI6IkdvbGRuZXItUm93ZSIsImRlc2NyaXB0aW9uIjoiYXVndWUgdmVzdGlidWx1bSBydXRydW0gcnV0cnVtIG5lcXVlIGFlbmVhbiBhdWN0b3IgZ3JhdmlkYSBzZW0gcHJhZXNlbnQgaWQgbWFzc2EgaWQgbmlzbCB2ZW5lbmF0aXMgbGFjaW5pYSBhZW5lYW4gc2l0IiwiYW1vdW50IjoiMTMiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDpcL1wvZHVtbXlpbWFnZS5jb21cLzMwMHg0MDAuanBnXC81ZmEyZGRcL2ZmZmZmZiJ9LHsiYWN0aXZlIjoidHJ1ZSIsInNrdSI6IjFkNGRmZDBkLTQ4MjktNDFhMi04NThjLTU2NDAzNzlhMWQ1OCIsInRpdGxlIjoiQ3VtbWVyYXRhLCBLb2hsZXIgYW5kIE1pbGxlciIsImRlc2NyaXB0aW9uIjoicGVsbGVudGVzcXVlIHF1aXNxdWUgcG9ydGEgdm9sdXRwYXQgZXJhdCBxdWlzcXVlIGVyYXQgZXJvcyB2aXZlcnJhIGVnZXQgY29uZ3VlIGVnZXQgc2VtcGVyIHJ1dHJ1bSIsImFtb3VudCI6IjcyIiwiY3VycmVuY3kiOiJ1c2QiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmpwZ1wvZGRkZGRkXC8wMDAwMDAifSx7ImFjdGl2ZSI6IiIsInNrdSI6Ijg3ZmNjMmI3LWYwY2UtNDYyNS1iNTM1LTIwNDg1MjljMDkzOCIsInRpdGxlIjoiSGVyem9nLVdpc29reSIsImRlc2NyaXB0aW9uIjoiYXVndWUgcXVhbSBzb2xsaWNpdHVkaW4gdml0YWUgY29uc2VjdGV0dWVyIGVnZXQgcnV0cnVtIGF0IGxvcmVtIGludGVnZXIgdGluY2lkdW50IGFudGUgdmVsIGlwc3VtIHByYWVzZW50IGJsYW5kaXQgbGFjaW5pYSBlcmF0IHZlc3RpYnVsdW0gc2VkIiwiYW1vdW50IjoiMzUiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDpcL1wvZHVtbXlpbWFnZS5jb21cLzMwMHg0MDAucG5nXC9kZGRkZGRcLzAwMDAwMCJ9LHsiYWN0aXZlIjoiIiwic2t1IjoiNWIzMGRkODctOTA4Ny00OWUzLWE4YjUtNzNlMWVhNDVmZDk0IiwidGl0bGUiOiJPa3VuZXZhLUhhdWNrIiwiZGVzY3JpcHRpb24iOiJldGlhbSBqdXN0byBldGlhbSBwcmV0aXVtIGlhY3VsaXMganVzdG8gaW4gaGFjIGhhYml0YXNzZSBwbGF0ZWEgZGljdHVtc3QgZXRpYW0gZmF1Y2lidXMgY3Vyc3VzIHVybmEgdXQiLCJhbW91bnQiOiI2NyIsImN1cnJlbmN5IjoidXNkIiwiaW1hZ2UiOiJodHRwOlwvXC9kdW1teWltYWdlLmNvbVwvMzAweDQwMC5ibXBcL2NjMDAwMFwvZmZmZmZmIn0seyJhY3RpdmUiOiIiLCJza3UiOiIyZmQ4OWVhYi04OGY5LTRkZGYtOWJiYS0zN2FlZDcyMzE1OWUiLCJ0aXRsZSI6IkZheS1XdWNrZXJ0IiwiZGVzY3JpcHRpb24iOiJzdXNjaXBpdCBudWxsYSBlbGl0IGFjIG51bGxhIHNlZCB2ZWwgZW5pbSBzaXQgYW1ldCBudW5jIHZpdmVycmEgZGFwaWJ1cyBudWxsYSIsImFtb3VudCI6IjU3IiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmJtcFwvY2MwMDAwXC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiMDE4Y2FmNjQtYTAzOC00NWI5LTg1OGQtZTBmNTMzY2I4NjE2IiwidGl0bGUiOiJUcm9tcC1SZWljaGVsIiwiZGVzY3JpcHRpb24iOiJxdWlzIHR1cnBpcyBzZWQgYW50ZSB2aXZhbXVzIHRvcnRvciBkdWlzIG1hdHRpcyBlZ2VzdGFzIG1ldHVzIGFlbmVhbiBmZXJtZW50dW0gZG9uZWMiLCJhbW91bnQiOiI0NCIsImN1cnJlbmN5IjoidXNkIiwiaW1hZ2UiOiJodHRwOlwvXC9kdW1teWltYWdlLmNvbVwvMzAweDQwMC5ibXBcL2ZmNDQ0NFwvZmZmZmZmIn0seyJhY3RpdmUiOiIiLCJza3UiOiI3OWRiY2E2MC01MmIwLTQyN2QtODZmNy00ZTIzMGJlMzE4NDEiLCJ0aXRsZSI6IkZyaWVzZW4tV2hpdGUiLCJkZXNjcmlwdGlvbiI6Im1vcmJpIHZlc3RpYnVsdW0gdmVsaXQgaWQgcHJldGl1bSBpYWN1bGlzIGRpYW0gZXJhdCBmZXJtZW50dW0ganVzdG8gbmVjIGNvbmRpbWVudHVtIG5lcXVlIHNhcGllbiBwbGFjZXJhdCBhbnRlIG51bGxhIGp1c3RvIGFsaXF1YW0iLCJhbW91bnQiOiI1MCIsImN1cnJlbmN5IjoidXNkIiwiaW1hZ2UiOiJodHRwOlwvXC9kdW1teWltYWdlLmNvbVwvMzAweDQwMC5ibXBcL2RkZGRkZFwvMDAwMDAwIn0seyJhY3RpdmUiOiJ0cnVlIiwic2t1IjoiYmUyYjUwYmMtZGFmNS00ZmJjLWEyMzQtMWY5MzE4OWRhNWFmIiwidGl0bGUiOiJPbHNvbiwgQmFycm93cyBhbmQgUXVpZ2xleSIsImRlc2NyaXB0aW9uIjoibnVsbGEgdXQgZXJhdCBpZCBtYXVyaXMgdnVscHV0YXRlIGVsZW1lbnR1bSBudWxsYW0gdmFyaXVzIG51bGxhIGZhY2lsaXNpIGNyYXMgbm9uIHZlbGl0IG5lYyBuaXNpIHZ1bHB1dGF0ZSBub251bW15IG1hZWNlbmFzIHRpbmNpZHVudCIsImFtb3VudCI6IjgwIiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmpwZ1wvY2MwMDAwXC9mZmZmZmYifSx7ImFjdGl2ZSI6IiIsInNrdSI6ImVhOThjZWNlLWViMTMtNDNiMy1hZDFkLWEyOTY3MWMzZDhiNSIsInRpdGxlIjoiTGVncm9zLUdsb3ZlciIsImRlc2NyaXB0aW9uIjoiY3VtIHNvY2lpcyBuYXRvcXVlIHBlbmF0aWJ1cyBldCBtYWduaXMgZGlzIHBhcnR1cmllbnQgbW9udGVzIG5hc2NldHVyIHJpZGljdWx1cyIsImFtb3VudCI6IjkwIiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLnBuZ1wvZmY0NDQ0XC9mZmZmZmYifSx7ImFjdGl2ZSI6InRydWUiLCJza3UiOiJmYjg4YTAwOC0zNWQ4LTQyZDUtOGMyNy1iYjUyZTEzNmViMjEiLCJ0aXRsZSI6IldhdHNpY2EsIENvcmtlcnkgYW5kIEZheSIsImRlc2NyaXB0aW9uIjoiaXBzdW0gcHJpbWlzIGluIGZhdWNpYnVzIG9yY2kgbHVjdHVzIGV0IHVsdHJpY2VzIHBvc3VlcmUgY3ViaWxpYSBjdXJhZSBkdWlzIGZhdWNpYnVzIGFjY3Vtc2FuIG9kaW8iLCJhbW91bnQiOiIyOSIsImN1cnJlbmN5IjoidXNkIiwiaW1hZ2UiOiJodHRwOlwvXC9kdW1teWltYWdlLmNvbVwvMzAweDQwMC5qcGdcLzVmYTJkZFwvZmZmZmZmIn0seyJhY3RpdmUiOiJmYWxzZSIsInNrdSI6ImM0OTI1YmI1LTBmOTEtNDllMC1iZjk3LTE1MzZmMTc5NjQyYSIsInRpdGxlIjoiUHVyZHksIEtpcmxpbiBhbmQgSm9obnN0b24iLCJkZXNjcmlwdGlvbiI6Im5vbiBwcmV0aXVtIHF1aXMgbGVjdHVzIHN1c3BlbmRpc3NlIHBvdGVudGkgaW4gZWxlaWZlbmQgcXVhbSBhIG9kaW8iLCJhbW91bnQiOiIyOSIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOlwvXC9kdW1teWltYWdlLmNvbVwvMzAweDQwMC5qcGdcL2ZmNDQ0NFwvZmZmZmZmIn0seyJhY3RpdmUiOiIiLCJza3UiOiJkZWY1ZWRhMC0zODk3LTRkOGItOTVhMi0xMjUyODI4MGJmNzMiLCJ0aXRsZSI6IlRpbGxtYW4gR3JvdXAiLCJkZXNjcmlwdGlvbiI6InZlbGl0IGV1IGVzdCBjb25ndWUgZWxlbWVudHVtIGluIGhhYyBoYWJpdGFzc2UgcGxhdGVhIGRpY3R1bXN0IG1vcmJpIHZlc3RpYnVsdW0gdmVsaXQiLCJhbW91bnQiOiIxIiwiY3VycmVuY3kiOiJ1c2QiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmJtcFwvY2MwMDAwXC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiNzcxNzBiMTgtMmM1MS00OTA4LWE2ZjAtNmM0YmNkZmIzZmQyIiwidGl0bGUiOiJLZXJsdWtlIExMQyIsImRlc2NyaXB0aW9uIjoiY3VyYWUgZHVpcyBmYXVjaWJ1cyBhY2N1bXNhbiBvZGlvIGN1cmFiaXR1ciBjb252YWxsaXMgZHVpcyBjb25zZXF1YXQgZHVpIG5lYyBuaXNpIHZvbHV0cGF0IGVsZWlmZW5kIGRvbmVjIHV0IGRvbG9yIG1vcmJpIHZlbCBsZWN0dXMiLCJhbW91bnQiOiIyMiIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOlwvXC9kdW1teWltYWdlLmNvbVwvMzAweDQwMC5wbmdcL2RkZGRkZFwvMDAwMDAwIn0seyJhY3RpdmUiOiJ0cnVlIiwic2t1IjoiNjIzZGU1ODUtYmIxYi00NDkwLTk0ZGItNDUzMWY1MTA4Mzk3IiwidGl0bGUiOiJXb2xmZi1CZWNodGVsYXIiLCJkZXNjcmlwdGlvbiI6ImV0IHRlbXB1cyBzZW1wZXIgZXN0IHF1YW0gcGhhcmV0cmEgbWFnbmEgYWMgY29uc2VxdWF0IG1ldHVzIHNhcGllbiIsImFtb3VudCI6IjQyIiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLnBuZ1wvY2MwMDAwXC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiNDRhMGJiZjEtOTAxMC00ZjdmLWE4OTctY2VjYjcwYzMwNTlkIiwidGl0bGUiOiJIaWxscyBhbmQgU29ucyIsImRlc2NyaXB0aW9uIjoiYW1ldCBlcmF0IG51bGxhIHRlbXB1cyB2aXZhbXVzIGluIGZlbGlzIGV1IHNhcGllbiBjdXJzdXMgdmVzdGlidWx1bSIsImFtb3VudCI6IjQyIiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLmJtcFwvNWZhMmRkXC9mZmZmZmYifSx7ImFjdGl2ZSI6IiIsInNrdSI6IjFlNDc0MGY1LWQwZmQtNGZkNy05YzkxLTgzMzlhZGU3NTgzNSIsInRpdGxlIjoiR2xvdmVyIGFuZCBTb25zIiwiZGVzY3JpcHRpb24iOiJ2ZWhpY3VsYSBjb25kaW1lbnR1bSBjdXJhYml0dXIgaW4gbGliZXJvIHV0IG1hc3NhIHZvbHV0cGF0IGNvbnZhbGxpcyBtb3JiaSBvZGlvIiwiYW1vdW50IjoiOTIiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDpcL1wvZHVtbXlpbWFnZS5jb21cLzMwMHg0MDAuanBnXC9mZjQ0NDRcL2ZmZmZmZiJ9LHsiYWN0aXZlIjoidHJ1ZSIsInNrdSI6IjNhM2M1Yzk1LWM5NjEtNDE4MS1iZTk3LWQ5MGMxZjVlNTUwZiIsInRpdGxlIjoiV3ltYW4tRGlldHJpY2giLCJkZXNjcmlwdGlvbiI6ImlkIGNvbnNlcXVhdCBpbiBjb25zZXF1YXQgdXQgbnVsbGEgc2VkIGFjY3Vtc2FuIGZlbGlzIHV0IGF0IGRvbG9yIHF1aXMgb2RpbyBjb25zZXF1YXQgdmFyaXVzIGludGVnZXIgYWMgbGVvIHBlbGxlbnRlc3F1ZSIsImFtb3VudCI6Ijg2IiwiY3VycmVuY3kiOiJ1c2QiLCJpbWFnZSI6Imh0dHA6XC9cL2R1bW15aW1hZ2UuY29tXC8zMDB4NDAwLnBuZ1wvNWZhMmRkXC9mZmZmZmYifV0sImNhdGVnb3JpZXMiOlt7InNrdSI6IjhJTVpOQlM3V1NLMVVEIiwidGl0bGUiOiJsZWd1bWVzIn0seyJza3UiOiJUWlpBSzVRUzlUVkdXQyIsInRpdGxlIjoiZ2F0ZWF1IiwiY2hpbGRyZW4iOlt7InNrdSI6IkE2UzQ5WjFHSUtBOTI4IiwidGl0bGUiOiJjb29raWVzcyIsInBhcmVudCI6IlRaWkFLNVFTOVRWR1dDIn1dfSx7InNrdSI6IkMxNkJDVjkxTFlWNkpOIiwidGl0bGUiOiJib25ib24iLCJjaGlsZHJlbiI6W3sic2t1IjoiMVZRRlRFUFk5RjJIN0oiLCJ0aXRsZSI6InNtYXJ0aWVzIiwicGFyZW50IjoiQzE2QkNWOTFMWVY2Sk4ifV19XSwidGFncyI6W119LCJmciI6eyJsYWJlbCI6IkZyYW5jYWlzIiwid2Vic2l0ZSI6eyJ0aXRsZSI6IkluZXNjb2luIiwiaWNvbiI6IiIsInRpbWV6b25lIjoiIiwiYWN0aXZlIjp0cnVlLCJhbmFseXRpY3MiOnsiYWN0aXZlIjpmYWxzZSwiY29kZSI6IiJ9LCJtZXRhIjpbeyJ0eXBlIjoibmFtZSIsIm5hbWUiOiJkZXNjcmlwdGlvbiIsImNvbnRlbnQiOiJJbmVzY29pbiwgRG9tYWluLCBXZWJzaXRlIGFuZCBNZXNzZW5nZXIgaW50byBCbG9ja2NoYWluIn0seyJ0eXBlIjoibmFtZSIsIm5hbWUiOiJrZXl3b3JkcyIsImNvbnRlbnQiOiJJbmVzY29pbiwgYmxvY2tjaGFpbiwgZG9tYWluLCBjcnlwdG8sIHdlYnNpdGUsIG1lc3NlbmdlciJ9LHsidHlwZSI6Im5hbWUiLCJuYW1lIjoiYXV0aG9yIiwiY29udGVudCI6IkluZXNjb2luIE5ldHdvcmsifV19LCJjb21wYW55Ijp7Im5hbWUiOiJJbmVzY29pbiIsInNsb2dhbiI6IiIsImRlc2NyaXB0aW9uIjoiIiwibG9nbyI6IiIsInllYXIiOjIwMTksInRlcm1zT2ZTZXJ2aWNlIjoiIiwidGVybXNPZlNhbGVzIjoiIiwicHJpdmFjeVBvbGljeSI6IiIsImZhcSI6IiJ9LCJsb2NhdGlvbiI6W3siYWRkcmVzcyI6IiIsInJlZ2lvbiI6IiIsInppcGNvZGUiOiIiLCJjaXR5IjoiIiwiY291bnRyeSI6IiIsImxvbmdpdHVkZSI6IiIsImxhdGl0dWRlIjoiIiwicGhvbmUiOiIiLCJlbWFpbCI6IiJ9XSwibmV0d29yayI6eyJnaXRodWIiOiIiLCJmYWNlYm9vayI6IiIsInR3aXR0ZXIiOiIiLCJsaW5rZWRpbiI6IiIsInlvdXR1YmUiOiIiLCJpbnN0YWdyYW0iOiIiLCJ3ZWNoYXQiOiIiLCJ3ZWlibyI6IiIsImRvdXlpbiI6IiIsInZrb250YWt0ZSI6IiIsIm9kbm9LbGFzc25pa2kiOiIiLCJ0ZWxlZ3JhbSI6IiIsIndoYXRzYXBwIjoiIn0sInBhZ2VzIjpbeyJtZW51VGl0bGUiOiIiLCJzaG93bkluTWVudSI6dHJ1ZSwiaXNMaW5rIjpmYWxzZSwibGlua1VybCI6IiIsImRpdklkIjoiIiwibGFiZWwiOiIiLCJib2R5IjoiPGRpdiBjbGFzcz1cImNvbnRhaW5lciBoLTEwMFwiPlxuPGRpdiBjbGFzcz1cInJvdyBoLTEwMCBhbGlnbi1pdGVtcy1jZW50ZXIganVzdGlmeS1jb250ZW50LWNlbnRlciB0ZXh0LWNlbnRlclwiPlxuPGRpdiBjbGFzcz1cImNvbC1sZy0xMCBhbGlnbi1zZWxmLWVuZFwiPlxuPGgxIGNsYXNzPVwidGV4dC11cHBlcmNhc2UgdGV4dC13aGl0ZSBmb250LXdlaWdodC1ib2xkXCI+Q3ImZWFjdXRlO2V6IHZvdHJlIG5vbSBkZSBkb21haW5lIGV0IHZvdHJlIHNpdGUgd2ViIGRhbnMgdW5lIGJsb2NrY2hhaW4sIGF2ZWMgdW4gbWVzc2FnZXIgY3J5cHQmZWFjdXRlOzxcL2gxPlxuPGhyIGNsYXNzPVwiZGl2aWRlciBteS00XCIgXC8+PFwvZGl2PlxuPGRpdiBjbGFzcz1cImNvbC1sZy04IGFsaWduLXNlbGYtYmFzZWxpbmVcIj5cbjxwIGNsYXNzPVwidGV4dC13aGl0ZS03NSBmb250LXdlaWdodC1saWdodCBtYi01XCI+VGVjaG5vbG9naWUgZGUgYmxvY2tjaGFpbiBkJmVhY3V0ZTtjZW50cmFsaXMmZWFjdXRlO2U8XC9wPlxuPGEgY2xhc3M9XCJidG4gYnRuLWxpZ2h0IGJ0bi14bCBqcy1zY3JvbGwtdHJpZ2dlclwiIHRpdGxlPVwiVHJhbnNhY3Rpb24gZXQgZXhwbG9yYXRldXIgZGUgZG9tYWluZVwiIGhyZWY9XCJodHRwczpcL1wvZXhwbG9yZXIuaW5lc2NvaW4ub3JnXCI+VHJhbnNhY3Rpb24gZXQgZXhwbG9yYXRldXIgZGUgZG9tYWluZTxcL2E+IDxhIGNsYXNzPVwiYnRuIGJ0bi1saWdodCBidG4teGwganMtc2Nyb2xsLXRyaWdnZXJcIiB0aXRsZT1cIlBvcnRlZmV1aWxsZSBob3JzIGxpZ25lLCBzaXRlIFdlYiBDTVMgZXQgTWVzc2VuZ2VyXCIgaHJlZj1cImh0dHBzOlwvXC93YWxsZXQuaW5lc2NvaW4ub3JnXCI+UG9ydGVmZXVpbGxlIGhvcnMgbGlnbmUsIHNpdGUgV2ViIENNUyBldCBNZXNzZW5nZXI8XC9hPjxcL2Rpdj5cbjxcL2Rpdj5cbjxcL2Rpdj4iLCJiYWNrZ3JvdW5kT3BhY2l0eSI6MTAwLCJoZWlnaHQiOiIiLCJiYWNrZ3JvdW5kSW1hZ2UiOiJodHRwczpcL1wvcmVzLmNsb3VkaW5hcnkuY29tXC9kemZieGx0enhcL2ltYWdlXC91cGxvYWRcL3YxNTcxNjU5MjMxXC9JbmVzY29pblwvYmctbWFzdGhlYWRfeGNqbTFyLmpwZyJ9LHsibWVudVRpdGxlIjoiVGVjaG5vbG9naWVzIiwic2hvd25Jbk1lbnUiOnRydWUsImlzTGluayI6ZmFsc2UsImxpbmtVcmwiOiIiLCJkaXZJZCI6InRlY2hub2xvZ2llcyIsImxhYmVsIjoiIiwiYm9keSI6IjxkaXYgY2xhc3M9XCJjb250YWluZXJcIj5cbjxoMiBjbGFzcz1cInRleHQtY2VudGVyIG10LTBcIj5Ob3MgdGVjaG5vbG9naWVzPFwvaDI+XG48aHIgY2xhc3M9XCJkaXZpZGVyIG15LTRcIiBcLz5cbjxkaXYgY2xhc3M9XCJyb3dcIj5cbjxkaXYgY2xhc3M9XCJjb2wtbGctMyBjb2wtbWQtNiB0ZXh0LWNlbnRlclwiPlxuPGRpdiBjbGFzcz1cIm10LTVcIj48ZW0gY2xhc3M9XCJmYXMgZmEtNHggZmEtaGVhcnQgdGV4dC1wcmltYXJ5IG1iLTRcIj4mbmJzcDs8XC9lbT5cbjxoMyBjbGFzcz1cImg0IG1iLTJcIj5SZWFjdFBIUDxcL2gzPlxuPHAgY2xhc3M9XCJ0ZXh0LW11dGVkIG1iLTBcIj5SZWFjdFBIUCBlc3QgdW5lIGJpYmxpb3RoJmVncmF2ZTtxdWUgZGUgYmFzIG5pdmVhdSBwb3VyIGxhIHByb2dyYW1tYXRpb24gJmVhY3V0ZTt2JmVhY3V0ZTtuZW1lbnRpZWxsZSBlbiBQSFAuICZBZ3JhdmU7IHNhIGJhc2UsIGlsIHkgYSB1bmUgYm91Y2xlIGQnJmVhY3V0ZTt2JmVhY3V0ZTtuZW1lbnQsIGF1LWRlc3N1cyBkZSBsYXF1ZWxsZSBpbCBmb3Vybml0IGRlcyB1dGlsaXRhaXJlcyBkZSBiYXMgbml2ZWF1LjxcL3A+XG48XC9kaXY+XG48XC9kaXY+XG48ZGl2IGNsYXNzPVwiY29sLWxnLTMgY29sLW1kLTYgdGV4dC1jZW50ZXJcIj5cbjxkaXYgY2xhc3M9XCJtdC01XCI+PGVtIGNsYXNzPVwiZmFzIGZhLTR4IGZhLWRhdGFiYXNlIHRleHQtcHJpbWFyeSBtYi00XCI+Jm5ic3A7PFwvZW0+XG48aDMgY2xhc3M9XCJoNCBtYi0yXCI+RWxhc3RpY3NlYXJjaCBEYXRhYmFzZTxcL2gzPlxuPHAgY2xhc3M9XCJ0ZXh0LW11dGVkIG1iLTBcIj5FbGFzdGljc2VhcmNoIGVzdCB1bmUgYmFzZSBkZSBkb25uJmVhY3V0ZTtlcyBxdWkgc3RvY2tlLCByJmVhY3V0ZTtjdXAmZWdyYXZlO3JlIGV0IGcmZWdyYXZlO3JlIGRlcyBkb25uJmVhY3V0ZTtlcyBvcmllbnQmZWFjdXRlO2VzIGRvY3VtZW50IGV0IHN0cnVjdHVyJmVhY3V0ZTtlcyBwYXIgc2lpLjxcL3A+XG48XC9kaXY+XG48XC9kaXY+XG48ZGl2IGNsYXNzPVwiY29sLWxnLTMgY29sLW1kLTYgdGV4dC1jZW50ZXJcIj5cbjxkaXYgY2xhc3M9XCJtdC01XCI+PGVtIGNsYXNzPVwiZmFzIGZhLTR4IGZhLWxvY2sgdGV4dC1wcmltYXJ5IG1iLTRcIj4mbmJzcDs8XC9lbT5cbjxoMyBjbGFzcz1cImg0IG1iLTJcIj5CbG9ja2NoYWluPFwvaDM+XG48cCBjbGFzcz1cInRleHQtbXV0ZWQgbWItMFwiPlVuZSBibG9ja2NoYWluLCAmYWdyYXZlOyBsJ29yaWdpbmUgdW5lIGNoYSZpY2lyYztuZSBkZSBibG9jcywgZXN0IHVuZSBsaXN0ZSBjcm9pc3NhbnRlIGQnZW5yZWdpc3RyZW1lbnRzLCBhcHBlbCZlYWN1dGU7cyBibG9jcywgbGkmZWFjdXRlO3MgJmFncmF2ZTsgbCdhaWRlIGRlIGxhIGNyeXB0b2dyYXBoaWUuPFwvcD5cbjxcL2Rpdj5cbjxcL2Rpdj5cbjxkaXYgY2xhc3M9XCJjb2wtbGctMyBjb2wtbWQtNiB0ZXh0LWNlbnRlclwiPlxuPGRpdiBjbGFzcz1cIm10LTVcIj48ZW0gY2xhc3M9XCJmYXMgZmEtNHggZmEtZ2xvYmUgdGV4dC1wcmltYXJ5IG1iLTRcIj4mbmJzcDs8XC9lbT5cbjxoMyBjbGFzcz1cImg0IG1iLTJcIj5QMlAgTmV0d290azxcL2gzPlxuPHAgY2xhc3M9XCJ0ZXh0LW11dGVkIG1iLTBcIj5TaWduaWZpZSAmbGFxdW87UGVlciB0byBQZWVyJnJhcXVvOy4gRGFucyB1biByJmVhY3V0ZTtzZWF1IFAyUCwgbGVzIFwiaG9tb2xvZ3Vlc1wiIHNvbnQgZGVzIHN5c3QmZWdyYXZlO21lcyBpbmZvcm1hdGlxdWVzIHF1aSBzb250IGNvbm5lY3QmZWFjdXRlO3MgbGVzIHVucyBhdXggYXV0cmVzIHZpYSBJbnRlcm5ldC48XC9wPlxuPFwvZGl2PlxuPFwvZGl2PlxuPFwvZGl2PlxuPFwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiIiwic2hvd25Jbk1lbnUiOmZhbHNlLCJpc0xpbmsiOmZhbHNlLCJsaW5rVXJsIjoiIiwiZGl2SWQiOiIiLCJsYWJlbCI6IiIsImJvZHkiOiI8ZGl2IGNsYXNzPVwiY29udGFpbmVyIHRleHQtY2VudGVyXCI+PGltZyBjbGFzcz1cImltZy1mbHVpZFwiIHNyYz1cImh0dHBzOlwvXC9yZXMuY2xvdWRpbmFyeS5jb21cL2R6ZmJ4bHR6eFwvaW1hZ2VcL3VwbG9hZFwvdjE1NzE2NTkyMzFcL0luZXNjb2luXC9pbmVzY29pbi1ibG9ja2NoYWluLW5ldHdvcmtfYmpxZm02LmpwZ1wiIFwvPjxcL2Rpdj4iLCJiYWNrZ3JvdW5kT3BhY2l0eSI6MTAwLCJoZWlnaHQiOiIiLCJiYWNrZ3JvdW5kSW1hZ2UiOiIifSx7Im1lbnVUaXRsZSI6IiIsInNob3duSW5NZW51IjpmYWxzZSwiaXNMaW5rIjpmYWxzZSwibGlua1VybCI6IiIsImRpdklkIjoiIiwibGFiZWwiOiIiLCJib2R5IjoiPGRpdiBjbGFzcz1cImNvbnRhaW5lciB0ZXh0LWNlbnRlclwiPjxpbWcgY2xhc3M9XCJpbWctZmx1aWRcIiBzcmM9XCJodHRwczpcL1wvcmVzLmNsb3VkaW5hcnkuY29tXC9kemZieGx0enhcL2ltYWdlXC91cGxvYWRcL3YxNTcxNjU5MjMxXC9JbmVzY29pblwvaW5lc2NvaW4tYmxvY2tjaGFpbi1ibG9ja19nanN2cmYuanBnXCIgXC8+PFwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiIiwic2hvd25Jbk1lbnUiOmZhbHNlLCJpc0xpbmsiOmZhbHNlLCJsaW5rVXJsIjoiIiwiZGl2SWQiOiIiLCJsYWJlbCI6IiIsImJvZHkiOiI8ZGl2IGNsYXNzPVwiY29udGFpbmVyIHRleHQtY2VudGVyXCI+PGltZyBjbGFzcz1cImltZy1mbHVpZFwiIHNyYz1cImh0dHBzOlwvXC9yZXMuY2xvdWRpbmFyeS5jb21cL2R6ZmJ4bHR6eFwvaW1hZ2VcL3VwbG9hZFwvdjE1NzE2NTkyMzFcL0luZXNjb2luXC9pbmVzY29pbi1ibG9ja2NoYWluLXRyYW5zYWN0aW9uLWNvbnNlbnN1c195eWZ5bTguanBnXCIgXC8+PFwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiIiwic2hvd25Jbk1lbnUiOmZhbHNlLCJpc0xpbmsiOmZhbHNlLCJsaW5rVXJsIjoiIiwiZGl2SWQiOiIiLCJsYWJlbCI6IiIsImJvZHkiOiI8ZGl2IGNsYXNzPVwiY29udGFpbmVyIHRleHQtY2VudGVyXCI+PGltZyBjbGFzcz1cImltZy1mbHVpZFwiIHNyYz1cImh0dHBzOlwvXC9yZXMuY2xvdWRpbmFyeS5jb21cL2R6ZmJ4bHR6eFwvaW1hZ2VcL3VwbG9hZFwvdjE1NzE2NTkyMzBcL0luZXNjb2luXC9pbmVzY29pbi1ibG9ja2NoYWluLWJhbmstY29uc2Vuc3VzX2J0eTl1ZC5qcGdcIiBcLz48XC9kaXY+IiwiYmFja2dyb3VuZE9wYWNpdHkiOjEwMCwiaGVpZ2h0IjoiIiwiYmFja2dyb3VuZEltYWdlIjoiIn0seyJtZW51VGl0bGUiOiIiLCJzaG93bkluTWVudSI6ZmFsc2UsImlzTGluayI6ZmFsc2UsImxpbmtVcmwiOiIiLCJkaXZJZCI6IiIsImxhYmVsIjoiIiwiYm9keSI6IjxkaXYgY2xhc3M9XCJjb250YWluZXIgdGV4dC1jZW50ZXJcIj48aW1nIGNsYXNzPVwiaW1nLWZsdWlkXCIgc3JjPVwiaHR0cHM6XC9cL3Jlcy5jbG91ZGluYXJ5LmNvbVwvZHpmYnhsdHp4XC9pbWFnZVwvdXBsb2FkXC92MTU3MTY1OTIzMFwvSW5lc2NvaW5cL2luZXNjb2luLWJsb2NrY2hhaW4tcGVlcnMtY29uc2Vuc3VzX2NkeTRubi5qcGdcIiBcLz48XC9kaXY+IiwiYmFja2dyb3VuZE9wYWNpdHkiOjEwMCwiaGVpZ2h0IjoiIiwiYmFja2dyb3VuZEltYWdlIjoiIn0seyJtZW51VGl0bGUiOiJFcXVpcGUiLCJzaG93bkluTWVudSI6dHJ1ZSwiaXNMaW5rIjpmYWxzZSwibGlua1VybCI6IiIsImRpdklkIjoiZXF1aXBlIiwibGFiZWwiOiIiLCJib2R5IjoiPGRpdiBjbGFzcz1cImNvbnRhaW5lciBtdC00XCI+XG48aDEgY2xhc3M9XCJtYi01IHRleHQtY2VudGVyXCI+VGVhbTxcL2gxPlxuPGRpdiBjbGFzcz1cInJvdyBqdXN0aWZ5LWNvbnRlbnQtbWQtY2VudGVyXCI+XG48ZGl2IGNsYXNzPVwiY29sLXhsLTMgY29sLW1kLTYgbWItNFwiPlxuPGRpdiBjbGFzcz1cImNhcmQgYm9yZGVyLTAgc2hhZG93XCI+PGltZyBjbGFzcz1cImNhcmQtaW1nLXRvcFwiIHNyYz1cImh0dHBzOlwvXC9yZXMuY2xvdWRpbmFyeS5jb21cL2R6ZmJ4bHR6eFwvaW1hZ2VcL3VwbG9hZFwvdjE1NzE2NTkyMzFcL0luZXNjb2luXC9pbmVzY29pbi1tb29uX2gwcTh5aC5qcGdcIiBhbHQ9XCJNb3VuaXIgUidRdWliYVwiIFwvPlxuPGRpdiBjbGFzcz1cImNhcmQtYm9keSB0ZXh0LWNlbnRlclwiPlxuPGg1IGNsYXNzPVwiY2FyZC10aXRsZSBtYi0wXCI+TW91bmlyIFInUXVpYmE8XC9oNT5cbjxkaXYgY2xhc3M9XCJjYXJkLXRleHQgdGV4dC1ibGFjay01MFwiPkNyZWF0b3I8XC9kaXY+XG48ZGl2IGNsYXNzPVwiY2FyZC10ZXh0IHRleHQtYmxhY2stNTBcIj48YSBocmVmPVwiaHR0cHM6XC9cL2xpbmtlZGluLmNvbVwvaW5cL21vdW5pci1yLXF1aWJhLTE0YWE4NGJhXC9cIj48ZW0gY2xhc3M9XCJmYWIgZmEtMnggZmEtbGlua2VkaW4gbWItNFwiPiZuYnNwOzxcL2VtPjxcL2E+PFwvZGl2PlxuPFwvZGl2PlxuPFwvZGl2PlxuPFwvZGl2PlxuPGRpdiBjbGFzcz1cImNvbC14bC0zIGNvbC1tZC02IG1iLTRcIj5cbjxkaXYgY2xhc3M9XCJjYXJkIGJvcmRlci0wIHNoYWRvd1wiPjxpbWcgY2xhc3M9XCJjYXJkLWltZy10b3BcIiBzcmM9XCJodHRwczpcL1wvcmVzLmNsb3VkaW5hcnkuY29tXC9kemZieGx0enhcL2ltYWdlXC91cGxvYWRcL3YxNTcyNTM5NjY0XC9JbmVzY29pblwvZmxvcmVudC41YTQ3MDljNV9tbXN3d3YuanBnXCIgYWx0PVwiRmxvcmVudCBEYXF1ZXRcIiBcLz5cbjxkaXYgY2xhc3M9XCJjYXJkLWJvZHkgdGV4dC1jZW50ZXJcIj5cbjxoNSBjbGFzcz1cImNhcmQtdGl0bGUgbWItMFwiPkZsb3JlbnQgRGFxdWV0PFwvaDU+XG48ZGl2IGNsYXNzPVwiY2FyZC10ZXh0IHRleHQtYmxhY2stNTBcIj5FeHBlcnRzIGVuIEN5YmVycyZlYWN1dGU7Y3VyaXQmZWFjdXRlOyBldCBCbG9ja2NoYWluLCBDb2ZvbmRhdGV1ciBjaGV6IDxhIGhyZWY9XCJodHRwczpcL1wvZGlzY29pbi5pb1wvXCI+aHR0cHM6XC9cL2Rpc2NvaW4uaW9cLzxcL2E+PFwvZGl2PlxuPGRpdiBjbGFzcz1cImNhcmQtdGV4dCB0ZXh0LWJsYWNrLTUwXCI+PGEgaHJlZj1cImh0dHBzOlwvXC93d3cubGlua2VkaW4uY29tXC9pblwvZmxvcmVudGRhcXVldFwvXCI+PGVtIGNsYXNzPVwiZmFiIGZhLTJ4IGZhLWxpbmtlZGluIG1iLTRcIj4mbmJzcDs8XC9lbT48XC9hPjxcL2Rpdj5cbjxcL2Rpdj5cbjxcL2Rpdj5cbjxcL2Rpdj5cbjxkaXYgY2xhc3M9XCJjb2wteGwtMyBjb2wtbWQtNiBtYi00XCI+XG48ZGl2IGNsYXNzPVwiY2FyZCBib3JkZXItMCBzaGFkb3dcIj48aW1nIGNsYXNzPVwiY2FyZC1pbWctdG9wXCIgc3JjPVwiaHR0cHM6XC9cL3Jlcy5jbG91ZGluYXJ5LmNvbVwvZHpmYnhsdHp4XC9pbWFnZVwvdXBsb2FkXC92MTU3MjU1NjY2NFwvSW5lc2NvaW5cL2luZXNjb2luX2ZyYW5ja19zYWxoaS5qcGdcIiBhbHQ9XCJGcmFuY2sgU2FsaGlcIiBcLz5cbjxkaXYgY2xhc3M9XCJjYXJkLWJvZHkgdGV4dC1jZW50ZXJcIj5cbjxoNSBjbGFzcz1cImNhcmQtdGl0bGUgbWItMFwiPkZyYW5jayBTYWxoaTxcL2g1PlxuPGRpdiBjbGFzcz1cImNhcmQtdGV4dCB0ZXh0LWJsYWNrLTUwXCI+SUNPIE1hbmFnZXI8XC9kaXY+XG48ZGl2IGNsYXNzPVwiY2FyZC10ZXh0IHRleHQtYmxhY2stNTBcIj48YSBocmVmPVwiaHR0cHM6XC9cL3d3dy5saW5rZWRpbi5jb21cL2luXC9mcmFuY2stc2FsaGktNTA4NzFiMTYzXC9cIj48ZW0gY2xhc3M9XCJmYWIgZmEtMnggZmEtbGlua2VkaW4gbWItNFwiPiZuYnNwOzxcL2VtPjxcL2E+PFwvZGl2PlxuPFwvZGl2PlxuPFwvZGl2PlxuPFwvZGl2PlxuPFwvZGl2PlxuPFwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiQ29udGFjdCIsInNob3duSW5NZW51Ijp0cnVlLCJpc0xpbmsiOmZhbHNlLCJsaW5rVXJsIjoiIiwiZGl2SWQiOiJjb250YWN0IiwibGFiZWwiOiIiLCJib2R5IjoiPGRpdiBjbGFzcz1cImNvbnRhaW5lclwiPlxuPGRpdiBjbGFzcz1cInJvdyBqdXN0aWZ5LWNvbnRlbnQtY2VudGVyXCI+XG48ZGl2IGNsYXNzPVwiY29sLWxnLTggdGV4dC1jZW50ZXJcIj5cbjxoMiBjbGFzcz1cIm10LTBcIj5FbnRyb25zIGVuIGNvbnRhY3QhPFwvaDI+XG48aHIgY2xhc3M9XCJkaXZpZGVyIG15LTRcIiBcLz5cbjxwIGNsYXNzPVwidGV4dC1tdXRlZCBtYi01XCI+Vm91cyBwb3V2ZXogc291dGVuaXIgY2UgcHJvamV0PFwvcD5cbjxcL2Rpdj5cbjxcL2Rpdj5cbjxkaXYgY2xhc3M9XCJyb3dcIj5cbjxkaXYgY2xhc3M9XCJjb2wtbGctMTIgbXItYXV0byB0ZXh0LWNlbnRlclwiPjxhIGNsYXNzPVwiYmxvY2tcIiBocmVmPVwiaHR0cHM6XC9cL2dpdGh1Yi5jb21cL2luZXNjb2luXCI+PGVtIGNsYXNzPVwiZmFiIGZhLWdpdGh1YiBmYS0zeCBtYi0zIHRleHQtbXV0ZWRcIj4mbmJzcDs8XC9lbT48XC9hPiA8YSBjbGFzcz1cImJsb2NrXCIgaHJlZj1cImh0dHBzOlwvXC90Lm1lXC9qb2luY2hhdFwvSVREMEVCTWNTYmJTQUxnV2dSUmxXd1wiPjxlbSBjbGFzcz1cImZhYiBmYS10ZWxlZ3JhbSBmYS0zeCBtYi0zIHRleHQtbXV0ZWRcIj4mbmJzcDs8XC9lbT48XC9hPjxcL2Rpdj5cbjxcL2Rpdj5cbjxcL2Rpdj4iLCJiYWNrZ3JvdW5kT3BhY2l0eSI6MTAwLCJoZWlnaHQiOiIiLCJiYWNrZ3JvdW5kSW1hZ2UiOiIifSx7Im1lbnVUaXRsZSI6IkV4cGxvcmF0ZXVyIiwic2hvd25Jbk1lbnUiOnRydWUsImlzTGluayI6dHJ1ZSwibGlua1VybCI6Imh0dHBzOlwvXC9leHBsb3Jlci5pbmVzY29pbi5vcmciLCJkaXZJZCI6IiIsImxhYmVsIjoiIiwiYm9keSI6IiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiUG9ydGVmZXVpbGxlIiwic2hvd25Jbk1lbnUiOnRydWUsImlzTGluayI6dHJ1ZSwibGlua1VybCI6Imh0dHBzOlwvXC93YWxsZXQuaW5lc2NvaW4ub3JnIiwiZGl2SWQiOiIiLCJsYWJlbCI6IiIsImJvZHkiOiIiLCJiYWNrZ3JvdW5kT3BhY2l0eSI6MTAwLCJoZWlnaHQiOiIiLCJiYWNrZ3JvdW5kSW1hZ2UiOiIifV0sInRoZW1lIjp7ImpzIjp7InZhbHVlIjoiKGZ1bmN0aW9uKCQpIHtcbiAgXCJ1c2Ugc3RyaWN0XCI7IFwvXC8gU3RhcnQgb2YgdXNlIHN0cmljdFxuXG4gIFwvXC8gU21vb3RoIHNjcm9sbGluZyB1c2luZyBqUXVlcnkgZWFzaW5nXG4gICQoJ2EuanMtc2Nyb2xsLXRyaWdnZXJbaHJlZio9XCIjXCJdOm5vdChbaHJlZj1cIiNcIl0pJykuY2xpY2soZnVuY3Rpb24oKSB7XG4gICAgaWYgKGxvY2F0aW9uLnBhdGhuYW1lLnJlcGxhY2UoXC9eXFxcL1wvLCAnJykgPT0gdGhpcy5wYXRobmFtZS5yZXBsYWNlKFwvXlxcXC9cLywgJycpICYmIGxvY2F0aW9uLmhvc3RuYW1lID09IHRoaXMuaG9zdG5hbWUpIHtcbiAgICAgIHZhciB0YXJnZXQgPSAkKHRoaXMuaGFzaCk7XG4gICAgICB0YXJnZXQgPSB0YXJnZXQubGVuZ3RoID8gdGFyZ2V0IDogJCgnW25hbWU9JyArIHRoaXMuaGFzaC5zbGljZSgxKSArICddJyk7XG4gICAgICBpZiAodGFyZ2V0Lmxlbmd0aCkge1xuICAgICAgICAkKCdodG1sLCBib2R5JykuYW5pbWF0ZSh7XG4gICAgICAgICAgc2Nyb2xsVG9wOiAodGFyZ2V0Lm9mZnNldCgpLnRvcCAtIDcyKVxuICAgICAgICB9LCAxMDAwLCBcImVhc2VJbk91dEV4cG9cIik7XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgIH1cbiAgICB9XG4gIH0pO1xuXG4gIFwvXC8gQ2xvc2VzIHJlc3BvbnNpdmUgbWVudSB3aGVuIGEgc2Nyb2xsIHRyaWdnZXIgbGluayBpcyBjbGlja2VkXG4gICQoJy5qcy1zY3JvbGwtdHJpZ2dlcicpLmNsaWNrKGZ1bmN0aW9uKCkge1xuICAgICQoJy5uYXZiYXItY29sbGFwc2UnKS5jb2xsYXBzZSgnaGlkZScpO1xuICB9KTtcblxuICBcL1wvIEFjdGl2YXRlIHNjcm9sbHNweSB0byBhZGQgYWN0aXZlIGNsYXNzIHRvIG5hdmJhciBpdGVtcyBvbiBzY3JvbGxcbiAgJCgnYm9keScpLnNjcm9sbHNweSh7XG4gICAgdGFyZ2V0OiAnI21haW5OYXYnLFxuICAgIG9mZnNldDogNzVcbiAgfSk7XG5cbiAgXC9cLyBDb2xsYXBzZSBOYXZiYXJcbiAgdmFyIG5hdmJhckNvbGxhcHNlID0gZnVuY3Rpb24oKSB7XG4gICAgaWYgKCQoXCIjbWFpbk5hdlwiKS5vZmZzZXQoKS50b3AgPiAxMDApIHtcbiAgICAgICQoXCIjbWFpbk5hdlwiKS5hZGRDbGFzcyhcIm5hdmJhci1zY3JvbGxlZFwiKTtcbiAgICB9IGVsc2Uge1xuICAgICAgJChcIiNtYWluTmF2XCIpLnJlbW92ZUNsYXNzKFwibmF2YmFyLXNjcm9sbGVkXCIpO1xuICAgIH1cbiAgfTtcbiAgXC9cLyBDb2xsYXBzZSBub3cgaWYgcGFnZSBpcyBub3QgYXQgdG9wXG4gIG5hdmJhckNvbGxhcHNlKCk7XG4gIFwvXC8gQ29sbGFwc2UgdGhlIG5hdmJhciB3aGVuIHBhZ2UgaXMgc2Nyb2xsZWRcbiAgJCh3aW5kb3cpLnNjcm9sbChuYXZiYXJDb2xsYXBzZSk7XG59KShqUXVlcnkpO1xuIiwibGlua3MiOlt7ImxpbmsiOiJcL1wvY29kZS5qcXVlcnkuY29tXC9qcXVlcnktMy4zLjEubWluLmpzIn0seyJsaW5rIjoiXC9cL3N0YWNrcGF0aC5ib290c3RyYXBjZG4uY29tXC9ib290c3RyYXBcLzQuMy4xXC9qc1wvYm9vdHN0cmFwLm1pbi5qcyJ9LHsibGluayI6IlwvXC9jZG5qcy5jbG91ZGZsYXJlLmNvbVwvYWpheFwvbGlic1wvcG9wcGVyLmpzXC8xLjE0LjdcL3VtZFwvcG9wcGVyLm1pbi5qcyJ9LHsibGluayI6IlwvXC9jZG5qcy5jbG91ZGZsYXJlLmNvbVwvYWpheFwvbGlic1wvanF1ZXJ5LWVhc2luZ1wvMS40LjFcL2pxdWVyeS5lYXNpbmcubWluLmpzIn0seyJsaW5rIjoiXC9cL2NkbmpzLmNsb3VkZmxhcmUuY29tXC9hamF4XC9saWJzXC9tYWduaWZpYy1wb3B1cC5qc1wvMS4xLjBcL2pxdWVyeS5tYWduaWZpYy1wb3B1cC5taW4uanMifV19LCJjc3MiOnsidmFsdWUiOiJib2R5LFxuaHRtbCB7XG4gIHdpZHRoOiAxMDAlO1xuICBoZWlnaHQ6IDEwMCU7XG59XG5cbi50ZXh0LXdoaXRlLTc1IHtcbiAgY29sb3I6IHJnYmEoMjU1LCAyNTUsIDI1NSwgMC43NSk7XG59XG5cbmhyLmRpdmlkZXIge1xuICBtYXgtd2lkdGg6IDMuMjVyZW07XG4gIGJvcmRlci13aWR0aDogMC4ycmVtO1xuICBib3JkZXItY29sb3I6ICM5OTAwQ0M7XG59XG5cbmhyLmxpZ2h0IHtcbiAgYm9yZGVyLWNvbG9yOiAjZmZmO1xufVxuXG4uYnRuIHtcbiAgZm9udC1mYW1pbHk6IFwiTWVycml3ZWF0aGVyIFNhbnNcIiwgLWFwcGxlLXN5c3RlbSwgQmxpbmtNYWNTeXN0ZW1Gb250LCBcIlNlZ29lIFVJXCIsIFJvYm90bywgXCJIZWx2ZXRpY2EgTmV1ZVwiLCBBcmlhbCwgXCJOb3RvIFNhbnNcIiwgc2Fucy1zZXJpZiwgXCJBcHBsZSBDb2xvciBFbW9qaVwiLCBcIlNlZ29lIFVJIEVtb2ppXCIsIFwiU2Vnb2UgVUkgU3ltYm9sXCIsIFwiTm90byBDb2xvciBFbW9qaVwiO1xufVxuXG4uYnRuLXhsIHtcbiAgcGFkZGluZzogMS4yNXJlbSAyLjI1cmVtO1xuICBmb250LXNpemU6IDAuODVyZW07XG4gIGZvbnQtd2VpZ2h0OiA3MDA7XG4gIHRleHQtdHJhbnNmb3JtOiB1cHBlcmNhc2U7XG4gIGJvcmRlcjogbm9uZTtcbiAgYm9yZGVyLXJhZGl1czogMTByZW07XG59XG5cbi5wYWdlLXNlY3Rpb24ge1xuICBwYWRkaW5nOiAzcmVtIDA7XG59XG5cbiNtYWluTmF2IHtcbiAgLXdlYmtpdC1ib3gtc2hhZG93OiAwIDAuNXJlbSAxcmVtIHJnYmEoMCwgMCwgMCwgMC4xNSk7XG4gIGJveC1zaGFkb3c6IDAgMC41cmVtIDFyZW0gcmdiYSgwLCAwLCAwLCAwLjE1KTtcbiAgYmFja2dyb3VuZC1jb2xvcjogI2ZmZjtcbiAgLXdlYmtpdC10cmFuc2l0aW9uOiBiYWNrZ3JvdW5kLWNvbG9yIDAuMnMgZWFzZTtcbiAgdHJhbnNpdGlvbjogYmFja2dyb3VuZC1jb2xvciAwLjJzIGVhc2U7XG59XG5cbiNtYWluTmF2IC5uYXZiYXItYnJhbmQge1xuICBmb250LWZhbWlseTogXCJNZXJyaXdlYXRoZXIgU2Fuc1wiLCAtYXBwbGUtc3lzdGVtLCBCbGlua01hY1N5c3RlbUZvbnQsIFwiU2Vnb2UgVUlcIiwgUm9ib3RvLCBcIkhlbHZldGljYSBOZXVlXCIsIEFyaWFsLCBcIk5vdG8gU2Fuc1wiLCBzYW5zLXNlcmlmLCBcIkFwcGxlIENvbG9yIEVtb2ppXCIsIFwiU2Vnb2UgVUkgRW1vamlcIiwgXCJTZWdvZSBVSSBTeW1ib2xcIiwgXCJOb3RvIENvbG9yIEVtb2ppXCI7XG4gIGZvbnQtd2VpZ2h0OiA3MDA7XG4gIGNvbG9yOiAjMjEyNTI5O1xufVxuXG4jbWFpbk5hdiAubmF2YmFyLW5hdiAubmF2LWl0ZW0gLm5hdi1saW5rIHtcbiAgY29sb3I6ICM2Yzc1N2Q7XG4gIGZvbnQtZmFtaWx5OiBcIk1lcnJpd2VhdGhlciBTYW5zXCIsIC1hcHBsZS1zeXN0ZW0sIEJsaW5rTWFjU3lzdGVtRm9udCwgXCJTZWdvZSBVSVwiLCBSb2JvdG8sIFwiSGVsdmV0aWNhIE5ldWVcIiwgQXJpYWwsIFwiTm90byBTYW5zXCIsIHNhbnMtc2VyaWYsIFwiQXBwbGUgQ29sb3IgRW1vamlcIiwgXCJTZWdvZSBVSSBFbW9qaVwiLCBcIlNlZ29lIFVJIFN5bWJvbFwiLCBcIk5vdG8gQ29sb3IgRW1vamlcIjtcbiAgZm9udC13ZWlnaHQ6IDcwMDtcbiAgZm9udC1zaXplOiAwLjlyZW07XG4gIHBhZGRpbmc6IDAuNzVyZW0gMDtcbn1cblxuI21haW5OYXYgLm5hdmJhci1uYXYgLm5hdi1pdGVtIC5uYXYtbGluazpob3ZlciwgI21haW5OYXYgLm5hdmJhci1uYXYgLm5hdi1pdGVtIC5uYXYtbGluazphY3RpdmUge1xuICBjb2xvcjogIzk5MDBDQztcbn1cblxuI21haW5OYXYgLm5hdmJhci1uYXYgLm5hdi1pdGVtIC5uYXYtbGluay5hY3RpdmUge1xuICBjb2xvcjogIzk5MDBDQyAhaW1wb3J0YW50O1xufVxuXG5AbWVkaWEgKG1pbi13aWR0aDogOTkycHgpIHtcbiAgI21haW5OYXYge1xuICAgIC13ZWJraXQtYm94LXNoYWRvdzogbm9uZTtcbiAgICBib3gtc2hhZG93OiBub25lO1xuICAgIGJhY2tncm91bmQtY29sb3I6IHRyYW5zcGFyZW50O1xuICB9XG4gICNtYWluTmF2IC5uYXZiYXItYnJhbmQge1xuICAgIGNvbG9yOiByZ2JhKDI1NSwgMjU1LCAyNTUsIDAuNyk7XG4gIH1cbiAgI21haW5OYXYgLm5hdmJhci1icmFuZDpob3ZlciB7XG4gICAgY29sb3I6ICNmZmY7XG4gIH1cbiAgI21haW5OYXYgLm5hdmJhci1uYXYgLm5hdi1pdGVtIC5uYXYtbGluayB7XG4gICAgY29sb3I6IHJnYmEoMjU1LCAyNTUsIDI1NSwgMC43KTtcbiAgICBwYWRkaW5nOiAwIDFyZW07XG4gIH1cbiAgI21haW5OYXYgLm5hdmJhci1uYXYgLm5hdi1pdGVtIC5uYXYtbGluazpob3ZlciB7XG4gICAgY29sb3I6ICNmZmY7XG4gIH1cbiAgI21haW5OYXYgLm5hdmJhci1uYXYgLm5hdi1pdGVtOmxhc3QtY2hpbGQgLm5hdi1saW5rIHtcbiAgICBwYWRkaW5nLXJpZ2h0OiAwO1xuICB9XG4gICNtYWluTmF2Lm5hdmJhci1zY3JvbGxlZCB7XG4gICAgLXdlYmtpdC1ib3gtc2hhZG93OiAwIDAuNXJlbSAxcmVtIHJnYmEoMCwgMCwgMCwgMC4xNSk7XG4gICAgYm94LXNoYWRvdzogMCAwLjVyZW0gMXJlbSByZ2JhKDAsIDAsIDAsIDAuMTUpO1xuICAgIGJhY2tncm91bmQtY29sb3I6ICNmZmY7XG4gIH1cbiAgI21haW5OYXYubmF2YmFyLXNjcm9sbGVkIC5uYXZiYXItYnJhbmQge1xuICAgIGNvbG9yOiAjMjEyNTI5O1xuICB9XG4gICNtYWluTmF2Lm5hdmJhci1zY3JvbGxlZCAubmF2YmFyLWJyYW5kOmhvdmVyIHtcbiAgICBjb2xvcjogIzk5MDBDQztcbiAgfVxuICAjbWFpbk5hdi5uYXZiYXItc2Nyb2xsZWQgLm5hdmJhci1uYXYgLm5hdi1pdGVtIC5uYXYtbGluayB7XG4gICAgY29sb3I6ICMyMTI1Mjk7XG4gIH1cbiAgI21haW5OYXYubmF2YmFyLXNjcm9sbGVkIC5uYXZiYXItbmF2IC5uYXYtaXRlbSAubmF2LWxpbms6aG92ZXIge1xuICAgIGNvbG9yOiAjOTkwMENDO1xuICB9XG59XG5cbmhlYWRlci5tYXN0aGVhZCB7XG4gIHBhZGRpbmctdG9wOiAxMHJlbTtcbiAgcGFkZGluZy1ib3R0b206IGNhbGMoMTByZW0gLSA3MnB4KTtcbiAgYmFja2dyb3VuZDogLXdlYmtpdC1ncmFkaWVudChsaW5lYXIsIGxlZnQgdG9wLCBsZWZ0IGJvdHRvbSwgZnJvbShyZ2JhKDkyLCA3NywgNjYsIDAuOCkpLCB0byhyZ2JhKDkyLCA3NywgNjYsIDAuOCkpKSwgdXJsKFwiLi5cL2ltZ1wvYmctbWFzdGhlYWQuanBnXCIpO1xuICBiYWNrZ3JvdW5kOiBsaW5lYXItZ3JhZGllbnQodG8gYm90dG9tLCByZ2JhKDkyLCA3NywgNjYsIDAuOCkgMCUsIHJnYmEoOTIsIDc3LCA2NiwgMC44KSAxMDAlKSwgdXJsKFwiLi5cL2ltZ1wvYmctbWFzdGhlYWQuanBnXCIpO1xuICBiYWNrZ3JvdW5kLXBvc2l0aW9uOiBjZW50ZXI7XG4gIGJhY2tncm91bmQtcmVwZWF0OiBuby1yZXBlYXQ7XG4gIGJhY2tncm91bmQtYXR0YWNobWVudDogc2Nyb2xsO1xuICBiYWNrZ3JvdW5kLXNpemU6IGNvdmVyO1xufVxuXG5oZWFkZXIubWFzdGhlYWQgaDEge1xuICBmb250LXNpemU6IDIuMjVyZW07XG59XG5cbkBtZWRpYSAobWluLXdpZHRoOiA5OTJweCkge1xuICBoZWFkZXIubWFzdGhlYWQge1xuICAgIGhlaWdodDogMTAwdmg7XG4gICAgbWluLWhlaWdodDogNDByZW07XG4gICAgcGFkZGluZy10b3A6IDcycHg7XG4gICAgcGFkZGluZy1ib3R0b206IDA7XG4gIH1cbiAgaGVhZGVyLm1hc3RoZWFkIHAge1xuICAgIGZvbnQtc2l6ZTogMS4xNXJlbTtcbiAgfVxuICBoZWFkZXIubWFzdGhlYWQgaDEge1xuICAgIGZvbnQtc2l6ZTogM3JlbTtcbiAgfVxufVxuXG5AbWVkaWEgKG1pbi13aWR0aDogMTIwMHB4KSB7XG4gIGhlYWRlci5tYXN0aGVhZCBoMSB7XG4gICAgZm9udC1zaXplOiAzLjVyZW07XG4gIH1cbn1cblxuI3BvcnRmb2xpbyAuY29udGFpbmVyLWZsdWlkIHtcbiAgbWF4LXdpZHRoOiAxOTIwcHg7XG59XG5cbiNwb3J0Zm9saW8gLmNvbnRhaW5lci1mbHVpZCAucG9ydGZvbGlvLWJveCB7XG4gIHBvc2l0aW9uOiByZWxhdGl2ZTtcbiAgZGlzcGxheTogYmxvY2s7XG59XG5cbiNwb3J0Zm9saW8gLmNvbnRhaW5lci1mbHVpZCAucG9ydGZvbGlvLWJveCAucG9ydGZvbGlvLWJveC1jYXB0aW9uIHtcbiAgZGlzcGxheTogLXdlYmtpdC1ib3g7XG4gIGRpc3BsYXk6IC1tcy1mbGV4Ym94O1xuICBkaXNwbGF5OiBmbGV4O1xuICAtd2Via2l0LWJveC1hbGlnbjogY2VudGVyO1xuICAtbXMtZmxleC1hbGlnbjogY2VudGVyO1xuICBhbGlnbi1pdGVtczogY2VudGVyO1xuICAtd2Via2l0LWJveC1wYWNrOiBjZW50ZXI7XG4gIC1tcy1mbGV4LXBhY2s6IGNlbnRlcjtcbiAganVzdGlmeS1jb250ZW50OiBjZW50ZXI7XG4gIC13ZWJraXQtYm94LW9yaWVudDogdmVydGljYWw7XG4gIC13ZWJraXQtYm94LWRpcmVjdGlvbjogbm9ybWFsO1xuICAtbXMtZmxleC1kaXJlY3Rpb246IGNvbHVtbjtcbiAgZmxleC1kaXJlY3Rpb246IGNvbHVtbjtcbiAgd2lkdGg6IDEwMCU7XG4gIGhlaWdodDogMTAwJTtcbiAgcG9zaXRpb246IGFic29sdXRlO1xuICBib3R0b206IDA7XG4gIHRleHQtYWxpZ246IGNlbnRlcjtcbiAgb3BhY2l0eTogMDtcbiAgY29sb3I6ICNmZmY7XG4gIGJhY2tncm91bmQ6IHJnYmEoMTUzLCAwLCAyMDQsIDAuOSk7XG4gIC13ZWJraXQtdHJhbnNpdGlvbjogb3BhY2l0eSAwLjI1cyBlYXNlO1xuICB0cmFuc2l0aW9uOiBvcGFjaXR5IDAuMjVzIGVhc2U7XG4gIHRleHQtYWxpZ246IGNlbnRlcjtcbn1cblxuI3BvcnRmb2xpbyAuY29udGFpbmVyLWZsdWlkIC5wb3J0Zm9saW8tYm94IC5wb3J0Zm9saW8tYm94LWNhcHRpb24gLnByb2plY3QtY2F0ZWdvcnkge1xuICBmb250LWZhbWlseTogXCJNZXJyaXdlYXRoZXIgU2Fuc1wiLCAtYXBwbGUtc3lzdGVtLCBCbGlua01hY1N5c3RlbUZvbnQsIFwiU2Vnb2UgVUlcIiwgUm9ib3RvLCBcIkhlbHZldGljYSBOZXVlXCIsIEFyaWFsLCBcIk5vdG8gU2Fuc1wiLCBzYW5zLXNlcmlmLCBcIkFwcGxlIENvbG9yIEVtb2ppXCIsIFwiU2Vnb2UgVUkgRW1vamlcIiwgXCJTZWdvZSBVSSBTeW1ib2xcIiwgXCJOb3RvIENvbG9yIEVtb2ppXCI7XG4gIGZvbnQtc2l6ZTogMC44NXJlbTtcbiAgZm9udC13ZWlnaHQ6IDYwMDtcbiAgdGV4dC10cmFuc2Zvcm06IHVwcGVyY2FzZTtcbn1cblxuI3BvcnRmb2xpbyAuY29udGFpbmVyLWZsdWlkIC5wb3J0Zm9saW8tYm94IC5wb3J0Zm9saW8tYm94LWNhcHRpb24gLnByb2plY3QtbmFtZSB7XG4gIGZvbnQtc2l6ZTogMS4ycmVtO1xufVxuXG4jcG9ydGZvbGlvIC5jb250YWluZXItZmx1aWQgLnBvcnRmb2xpby1ib3g6aG92ZXIgLnBvcnRmb2xpby1ib3gtY2FwdGlvbiB7XG4gIG9wYWNpdHk6IDE7XG59IiwibGlua3MiOlt7ImxpbmsiOiJcL1wvY2RuanMuY2xvdWRmbGFyZS5jb21cL2FqYXhcL2xpYnNcL2ZvbnQtYXdlc29tZVwvNS4xMC4yXC9jc3NcL2FsbC5taW4uY3NzIn0seyJsaW5rIjoiXC9cL2ZvbnRzLmdvb2dsZWFwaXMuY29tXC9jc3M\/ZmFtaWx5PU1lcnJpd2VhdGhlcitTYW5zOjQwMCw3MDAifSx7ImxpbmsiOiJcL1wvZm9udHMuZ29vZ2xlYXBpcy5jb21cL2Nzcz9mYW1pbHk9TWVycml3ZWF0aGVyOjQwMCwzMDAsMzAwaXRhbGljLDQwMGl0YWxpYyw3MDAsNzAwaXRhbGljIn0seyJsaW5rIjoiXC9cL2NkbmpzLmNsb3VkZmxhcmUuY29tXC9hamF4XC9saWJzXC9tYWduaWZpYy1wb3B1cC5qc1wvMS4xLjBcL21hZ25pZmljLXBvcHVwLm1pbi5jc3MifSx7ImxpbmsiOiJcL1wvc3RhY2twYXRoLmJvb3RzdHJhcGNkbi5jb21cL2Jvb3RzdHJhcFwvNC4zLjFcL2Nzc1wvYm9vdHN0cmFwLm1pbi5jc3MifV19fX19LCJ1cmwiOiJpbmVzY29pbiIsInNpZ25hdHVyZSI6IjMwNDQwMjIwNDdkNjBjMjNmZDhlY2Q1NGM0Mzk0NmY3NzE1MzVmNDg2NzEzOWMxMjc2MmM0OWIzMjEyNWE1ZGM2YzE5NDM2MDAyMjA3MDJmNTk5YjhjZTUzNTViOTFhYWFkYjQ5Njg2ZDA3M2IxMDg1M2E1OTFhYjhhYTFiNjkyMjc3NmZkNDllMmNhIiwib3duZXJBZGRyZXNzIjoiMHg5Yzc5ODNhZTc2QTAzNzFmRmNlNTBEZjMzODNlRjUzRGVhMDY0N2I4Iiwib3duZXJQdWJsaWNLZXkiOiIwM2JkYjQzYmMwNWMwMzA1MDdjZmYyNGY1MzQ0N2IxMGM0YjQzYmNiMmVmM2NlMThiZjY0YTJjMjNkMmZhMWRiMjIiLCJoYXNoIjoiMTNjN2QwNWJlYjE1MmYxOTQ0YWMzZWFjMWJmMGIxZGVkYTcwMGNkZDNkZjAyMzc1YmVmMzkyN2E2MzVhNmNmOSIsImJsb2NrSGVpZ2h0IjoyLCJ0cmFuc2FjdGlvbkhhc2giOiIxMWNlNTI5OTQ4ZGFjYzdkMDBkYjlkNWQzN2JiMmY2NGEyZjgwMDJjMTk1ZjljYTczYWNkZjY0MzBlMjU2ZGNiIiwiYmxvY2tIZWlnaHRFbmQiOjE1MDAwMDJ9",
   "ownerAddress": "0x9c7983ae76A0371fFce50Df3383eF53Dea0647b8",
   "ownerPublicKey": "03bdb43bc05c030507cff24f53447b10c4b43bcb2ef3ce18bf64a2c23d2fa1db22",
   "signature": "3044022047d60c23fd8ecd54c43946f771535f4867139c12762c49b32125a5dc6c1943600220702f599b8ce5355b91aaadb49686d073b10853a591ab8aa1b6922776fd49e2ca",
   "blockHeight": 3,
   "transactionHash": "4dc0e65abfa9e7e6f0a512ed005e50799caecd73e97576e54a726990460cae56"
}
```

**[Back to top](#Get-started)**

## Get website info

| #   | URI                             | Method    | Description                                                |
|-----|---------------------------------|-----------|------------------------------------------------------------|
| 18. | `/get-website-info`             | POST      | Get website details                                         |

Request

```
    curl -X POST \
      https://node.inescoin.org/get-website-info \
      -H 'Content-Type: application/json' \
      -d '{
      "url": "inescoin"
    }'
```

Error response

```
[]
```

Success response

```
{ 
   "hash": "13c7d05beb152f1944ac3eac1bf0b1deda700cdd3df02375bef3927a635a6cf9",
   "url": "inescoin",
   "ownerAddress": "0x9c7983ae76A0371fFce50Df3383eF53Dea0647b8",
   "ownerPublicKey": "03bdb43bc05c030507cff24f53447b10c4b43bcb2ef3ce18bf64a2c23d2fa1db22",
   "signature": "3044022047d60c23fd8ecd54c43946f771535f4867139c12762c49b32125a5dc6c1943600220702f599b8ce5355b91aaadb49686d073b10853a591ab8aa1b6922776fd49e2ca",
   "blockHeight": 2,
   "transactionHash": "11ce529948dacc7d00db9d5d37bb2f64a2f8002c195f9ca73acdf6430e256dcb",
   "blockHeightEnd": 1500002,
   "transactions": [ 
      { 
         "hash": "4dc0e65abfa9e7e6f0a512ed005e50799caecd73e97576e54a726990460cae56",
         "configHash": "fd6e25c5cfc7974db849f95b19973465",
         "bankHash": "d3a8ed60a13e60eec88f96c21251ff345b2741b0b0126a4c5fae24e4bbee7794",
         "blockHeight": 3,
         "from": "0x9c7983ae76A0371fFce50Df3383eF53Dea0647b8",
         "toDo": "W3siYWN0aW9uIjoidXBkYXRlIiwibmFtZSI6ImluZXNjb2luIiwiZGF0YSI6eyJodG1sIjp7ImVuIjp7ImxhYmVsIjoiRW5nbGlzaCIsIndlYnNpdGUiOnsidGl0bGUiOiJJbmVzY29pbiIsImljb24iOiIiLCJ0aW1lem9uZSI6IiIsImFjdGl2ZSI6dHJ1ZSwiYW5hbHl0aWNzIjp7ImFjdGl2ZSI6ZmFsc2UsImNvZGUiOiIifSwibWV0YSI6W3sidHlwZSI6Im5hbWUiLCJuYW1lIjoiZGVzY3JpcHRpb24iLCJjb250ZW50IjoiSW5lc2NvaW4sIERvbWFpbiwgV2Vic2l0ZSBhbmQgTWVzc2VuZ2VyIGludG8gQmxvY2tjaGFpbiJ9LHsidHlwZSI6Im5hbWUiLCJuYW1lIjoia2V5d29yZHMiLCJjb250ZW50IjoiSW5lc2NvaW4sIGJsb2NrY2hhaW4sIGRvbWFpbiwgY3J5cHRvLCB3ZWJzaXRlLCBtZXNzZW5nZXIifSx7InR5cGUiOiJuYW1lIiwibmFtZSI6ImF1dGhvciIsImNvbnRlbnQiOiJJbmVzY29pbiBOZXR3b3JrIn1dfSwiY29tcGFueSI6eyJuYW1lIjoiSW5lc2NvaW4iLCJzbG9nYW4iOiIiLCJkZXNjcmlwdGlvbiI6ImZkc2ZzZGZzZGYiLCJsb2dvIjoiIiwieWVhciI6MjAxOSwidGVybXNPZlNlcnZpY2UiOiIiLCJ0ZXJtc09mU2FsZXMiOiIiLCJwcml2YWN5UG9saWN5IjoiIiwiZmFxIjoiIn0sImxvY2F0aW9uIjpbeyJhZGRyZXNzIjoiIiwicmVnaW9uIjoiIiwiemlwY29kZSI6IiIsImNpdHkiOiIiLCJjb3VudHJ5IjoiIiwibG9uZ2l0dWRlIjoiIiwibGF0aXR1ZGUiOiIiLCJwaG9uZSI6IiIsImVtYWlsIjoiIn1dLCJuZXR3b3JrIjp7ImdpdGh1YiI6IiIsImZhY2Vib29rIjoiIiwidHdpdHRlciI6IiIsImxpbmtlZGluIjoiIiwieW91dHViZSI6IiIsImluc3RhZ3JhbSI6IiIsIndlY2hhdCI6IiIsIndlaWJvIjoiIiwiZG91eWluIjoiIiwidmtvbnRha3RlIjoiIiwib2Rub0tsYXNzbmlraSI6IiIsInRlbGVncmFtIjoiIiwid2hhdHNhcHAiOiIifSwicGFnZXMiOlt7Im1lbnVUaXRsZSI6IiIsInNob3duSW5NZW51Ijp0cnVlLCJpc0xpbmsiOmZhbHNlLCJsaW5rVXJsIjoiIiwiZGl2SWQiOiIiLCJsYWJlbCI6IiIsImJvZHkiOiI8ZGl2IGNsYXNzPVwiY29udGFpbmVyIGgtMTAwXCI+XG48ZGl2IGNsYXNzPVwicm93IGgtMTAwIGFsaWduLWl0ZW1zLWNlbnRlciBqdXN0aWZ5LWNvbnRlbnQtY2VudGVyIHRleHQtY2VudGVyXCI+XG48ZGl2IGNsYXNzPVwiY29sLWxnLTEwIGFsaWduLXNlbGYtZW5kXCI+XG48aDEgY2xhc3M9XCJ0ZXh0LXVwcGVyY2FzZSB0ZXh0LXdoaXRlIGZvbnQtd2VpZ2h0LWJvbGRcIj5DcmVhdGUgeW91ciBkb21haW4gbmFtZSBhbmQgd2Vic2l0ZSBpbnRvIGJsb2NrY2hhaW4sIHdpdGggZW5jcnlwdGVkIG1lc3NlbmdlcjwvaDE+XG48aHIgY2xhc3M9XCJkaXZpZGVyIG15LTRcIiAvPjwvZGl2PlxuPGRpdiBjbGFzcz1cImNvbC1sZy04IGFsaWduLXNlbGYtYmFzZWxpbmVcIj5cbjxwIGNsYXNzPVwidGV4dC13aGl0ZS03NSBmb250LXdlaWdodC1saWdodCBtYi01XCI+RGVjZW50cmFsaXplZCBCbG9ja2NoYWluIFRlY2hub2xvZ3k8L3A+XG48YSBjbGFzcz1cImJ0biBidG4tbGlnaHQgYnRuLXhsIGpzLXNjcm9sbC10cmlnZ2VyXCIgaHJlZj1cImh0dHBzOi8vZXhwbG9yZXIuaW5lc2NvaW4ub3JnXCI+VHJhbnNhY3Rpb24gJmFtcDsgRG9tYWluIGV4cGxvcmVyPC9hPiA8YSBjbGFzcz1cImJ0biBidG4tbGlnaHQgYnRuLXhsIGpzLXNjcm9sbC10cmlnZ2VyXCIgdGl0bGU9XCJPZmZsaW5lIFdhbGxldCwgV2Vic2l0ZSBDTVMgYW5kIE1lc3NlbmdlclwiIGhyZWY9XCJodHRwczovL3dhbGxldC5pbmVzY29pbi5vcmdcIj5PZmZsaW5lIFdhbGxldCwgV2Vic2l0ZSBDTVMgYW5kIE1lc3NlbmdlcjwvYT48L2Rpdj5cbjwvZGl2PlxuPC9kaXY+IiwiYmFja2dyb3VuZE9wYWNpdHkiOjEwMCwiaGVpZ2h0IjoiIiwiYmFja2dyb3VuZEltYWdlIjoiaHR0cHM6Ly9yZXMuY2xvdWRpbmFyeS5jb20vZHpmYnhsdHp4L2ltYWdlL3VwbG9hZC92MTU3MTY1OTIzMS9JbmVzY29pbi9iZy1tYXN0aGVhZF94Y2ptMXIuanBnIn0seyJtZW51VGl0bGUiOiJUZWNobm9sb2dpZXMiLCJzaG93bkluTWVudSI6dHJ1ZSwiaXNMaW5rIjpmYWxzZSwibGlua1VybCI6IiIsImRpdklkIjoidGVjaG5vbG9naWVzIiwibGFiZWwiOiIiLCJib2R5IjoiPGRpdiBjbGFzcz1cImNvbnRhaW5lclwiPlxuPGgyIGNsYXNzPVwidGV4dC1jZW50ZXIgbXQtMFwiPk91ciBUZWNobm9sb2dpZXM8L2gyPlxuPGhyIGNsYXNzPVwiZGl2aWRlciBteS00XCIgLz5cbjxkaXYgY2xhc3M9XCJyb3dcIj5cbjxkaXYgY2xhc3M9XCJjb2wtbGctMyBjb2wtbWQtNiB0ZXh0LWNlbnRlclwiPlxuPGRpdiBjbGFzcz1cIm10LTVcIj48ZW0gY2xhc3M9XCJmYXMgZmEtNHggZmEtaGVhcnQgdGV4dC1wcmltYXJ5IG1iLTRcIj4mbmJzcDs8L2VtPlxuPGgzIGNsYXNzPVwiaDQgbWItMlwiPlJlYWN0UEhQPC9oMz5cbjxwIGNsYXNzPVwidGV4dC1tdXRlZCBtYi0wXCI+UmVhY3RQSFAgaXMgYSBsb3ctbGV2ZWwgbGlicmFyeSBmb3IgZXZlbnQtZHJpdmVuIHByb2dyYW1taW5nIGluIFBIUC4gQXQgaXRzIGNvcmUgaXMgYW4gZXZlbnQgbG9vcCwgb24gdG9wIG9mIHdoaWNoIGl0IHByb3ZpZGVzIGxvdy1sZXZlbCB1dGlsaXRpZXMuPC9wPlxuPC9kaXY+XG48L2Rpdj5cbjxkaXYgY2xhc3M9XCJjb2wtbGctMyBjb2wtbWQtNiB0ZXh0LWNlbnRlclwiPlxuPGRpdiBjbGFzcz1cIm10LTVcIj48ZW0gY2xhc3M9XCJmYXMgZmEtNHggZmEtZGF0YWJhc2UgdGV4dC1wcmltYXJ5IG1iLTRcIj4mbmJzcDs8L2VtPlxuPGgzIGNsYXNzPVwiaDQgbWItMlwiPkVsYXN0aWNzZWFyY2ggRGF0YWJhc2U8L2gzPlxuPHAgY2xhc3M9XCJ0ZXh0LW11dGVkIG1iLTBcIj5FbGFzdGljc2VhcmNoIGlzIGEgZGF0YWJhc2UgdGhhdCBzdG9yZXMsIHJldHJpZXZlcywgYW5kIG1hbmFnZXMgZG9jdW1lbnQtb3JpZW50ZWQgYW5kIHNpaS1zdHJ1Y3R1cmVkIGRhdGEuPC9wPlxuPC9kaXY+XG48L2Rpdj5cbjxkaXYgY2xhc3M9XCJjb2wtbGctMyBjb2wtbWQtNiB0ZXh0LWNlbnRlclwiPlxuPGRpdiBjbGFzcz1cIm10LTVcIj48ZW0gY2xhc3M9XCJmYXMgZmEtNHggZmEtbG9jayB0ZXh0LXByaW1hcnkgbWItNFwiPiZuYnNwOzwvZW0+XG48aDMgY2xhc3M9XCJoNCBtYi0yXCI+QmxvY2tjaGFpbjwvaDM+XG48cCBjbGFzcz1cInRleHQtbXV0ZWQgbWItMFwiPkEgYmxvY2tjaGFpbiwgb3JpZ2luYWxseSBibG9jayBjaGFpbiwgaXMgYSBncm93aW5nIGxpc3Qgb2YgcmVjb3JkcywgY2FsbGVkIGJsb2NrcywgdGhhdCBhcmUgbGlua2VkIHVzaW5nIGNyeXB0b2dyYXBoeS48L3A+XG48L2Rpdj5cbjwvZGl2PlxuPGRpdiBjbGFzcz1cImNvbC1sZy0zIGNvbC1tZC02IHRleHQtY2VudGVyXCI+XG48ZGl2IGNsYXNzPVwibXQtNVwiPjxlbSBjbGFzcz1cImZhcyBmYS00eCBmYS1nbG9iZSB0ZXh0LXByaW1hcnkgbWItNFwiPiZuYnNwOzwvZW0+XG48aDMgY2xhc3M9XCJoNCBtYi0yXCI+UDJQIE5ldHdvdGs8L2gzPlxuPHAgY2xhc3M9XCJ0ZXh0LW11dGVkIG1iLTBcIj5TdGFuZHMgZm9yIFwiUGVlciB0byBQZWVyLlwiIEluIGEgUDJQIG5ldHdvcmssIHRoZSBcInBlZXJzXCIgYXJlIGNvbXB1dGVyIHN5c3RlbXMgd2hpY2ggYXJlIGNvbm5lY3RlZCB0byBlYWNoIG90aGVyIHZpYSB0aGUgSW50ZXJuZXQuPC9wPlxuPC9kaXY+XG48L2Rpdj5cbjwvZGl2PlxuPC9kaXY+IiwiYmFja2dyb3VuZE9wYWNpdHkiOjEwMCwiaGVpZ2h0IjoiIiwiYmFja2dyb3VuZEltYWdlIjoiIn0seyJtZW51VGl0bGUiOiIiLCJzaG93bkluTWVudSI6ZmFsc2UsImlzTGluayI6ZmFsc2UsImxpbmtVcmwiOiIiLCJkaXZJZCI6IiIsImxhYmVsIjoiIiwiYm9keSI6IjxkaXYgY2xhc3M9XCJjb250YWluZXIgdGV4dC1jZW50ZXJcIj48aW1nIGNsYXNzPVwiaW1nLWZsdWlkXCIgc3JjPVwiaHR0cHM6Ly9yZXMuY2xvdWRpbmFyeS5jb20vZHpmYnhsdHp4L2ltYWdlL3VwbG9hZC92MTU3MTY1OTIzMS9JbmVzY29pbi9pbmVzY29pbi1ibG9ja2NoYWluLW5ldHdvcmtfYmpxZm02LmpwZ1wiIC8+PC9kaXY+IiwiYmFja2dyb3VuZE9wYWNpdHkiOjEwMCwiaGVpZ2h0IjoiIiwiYmFja2dyb3VuZEltYWdlIjoiIn0seyJtZW51VGl0bGUiOiIiLCJzaG93bkluTWVudSI6ZmFsc2UsImlzTGluayI6ZmFsc2UsImxpbmtVcmwiOiIiLCJkaXZJZCI6IiIsImxhYmVsIjoiIiwiYm9keSI6IjxkaXYgY2xhc3M9XCJjb250YWluZXIgdGV4dC1jZW50ZXJcIj48aW1nIGNsYXNzPVwiaW1nLWZsdWlkXCIgc3JjPVwiaHR0cHM6Ly9yZXMuY2xvdWRpbmFyeS5jb20vZHpmYnhsdHp4L2ltYWdlL3VwbG9hZC92MTU3MTY1OTIzMS9JbmVzY29pbi9pbmVzY29pbi1ibG9ja2NoYWluLWJsb2NrX2dqc3ZyZi5qcGdcIiAvPjwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiIiwic2hvd25Jbk1lbnUiOmZhbHNlLCJpc0xpbmsiOmZhbHNlLCJsaW5rVXJsIjoiIiwiZGl2SWQiOiIiLCJsYWJlbCI6IiIsImJvZHkiOiI8ZGl2IGNsYXNzPVwiY29udGFpbmVyIHRleHQtY2VudGVyXCI+PGltZyBjbGFzcz1cImltZy1mbHVpZFwiIHNyYz1cImh0dHBzOi8vcmVzLmNsb3VkaW5hcnkuY29tL2R6ZmJ4bHR6eC9pbWFnZS91cGxvYWQvdjE1NzE2NTkyMzEvSW5lc2NvaW4vaW5lc2NvaW4tYmxvY2tjaGFpbi10cmFuc2FjdGlvbi1jb25zZW5zdXNfeXlmeW04LmpwZ1wiIC8+PC9kaXY+IiwiYmFja2dyb3VuZE9wYWNpdHkiOjEwMCwiaGVpZ2h0IjoiIiwiYmFja2dyb3VuZEltYWdlIjoiIn0seyJtZW51VGl0bGUiOiIiLCJzaG93bkluTWVudSI6ZmFsc2UsImlzTGluayI6ZmFsc2UsImxpbmtVcmwiOiIiLCJkaXZJZCI6IiIsImxhYmVsIjoiIiwiYm9keSI6IjxkaXYgY2xhc3M9XCJjb250YWluZXIgdGV4dC1jZW50ZXJcIj48aW1nIGNsYXNzPVwiaW1nLWZsdWlkXCIgc3JjPVwiaHR0cHM6Ly9yZXMuY2xvdWRpbmFyeS5jb20vZHpmYnhsdHp4L2ltYWdlL3VwbG9hZC92MTU3MTY1OTIzMC9JbmVzY29pbi9pbmVzY29pbi1ibG9ja2NoYWluLWJhbmstY29uc2Vuc3VzX2J0eTl1ZC5qcGdcIiAvPjwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiIiwic2hvd25Jbk1lbnUiOmZhbHNlLCJpc0xpbmsiOmZhbHNlLCJsaW5rVXJsIjoiIiwiZGl2SWQiOiIiLCJsYWJlbCI6IiIsImJvZHkiOiI8ZGl2IGNsYXNzPVwiY29udGFpbmVyIHRleHQtY2VudGVyXCI+PGltZyBjbGFzcz1cImltZy1mbHVpZFwiIHNyYz1cImh0dHBzOi8vcmVzLmNsb3VkaW5hcnkuY29tL2R6ZmJ4bHR6eC9pbWFnZS91cGxvYWQvdjE1NzE2NTkyMzAvSW5lc2NvaW4vaW5lc2NvaW4tYmxvY2tjaGFpbi1wZWVycy1jb25zZW5zdXNfY2R5NG5uLmpwZ1wiIC8+PC9kaXY+IiwiYmFja2dyb3VuZE9wYWNpdHkiOjEwMCwiaGVpZ2h0IjoiIiwiYmFja2dyb3VuZEltYWdlIjoiIn0seyJtZW51VGl0bGUiOiJUZWFtIiwic2hvd25Jbk1lbnUiOnRydWUsImlzTGluayI6ZmFsc2UsImxpbmtVcmwiOiIiLCJkaXZJZCI6InRlYW0iLCJsYWJlbCI6IiIsImJvZHkiOiI8ZGl2IGNsYXNzPVwiY29udGFpbmVyIG10LTRcIj5cbjxoMSBjbGFzcz1cIm1iLTUgdGV4dC1jZW50ZXJcIj5UZWFtPC9oMT5cbjxkaXYgY2xhc3M9XCJyb3cganVzdGlmeS1jb250ZW50LW1kLWNlbnRlclwiPlxuPGRpdiBjbGFzcz1cImNvbC14bC0zIGNvbC1tZC02IG1iLTRcIj5cbjxkaXYgY2xhc3M9XCJjYXJkIGJvcmRlci0wIHNoYWRvd1wiPjxpbWcgY2xhc3M9XCJjYXJkLWltZy10b3BcIiBzcmM9XCJodHRwczovL3Jlcy5jbG91ZGluYXJ5LmNvbS9kemZieGx0engvaW1hZ2UvdXBsb2FkL3YxNTcxNjU5MjMxL0luZXNjb2luL2luZXNjb2luLW1vb25faDBxOHloLmpwZ1wiIGFsdD1cIk1vdW5pciBSJ1F1aWJhXCIgLz5cbjxkaXYgY2xhc3M9XCJjYXJkLWJvZHkgdGV4dC1jZW50ZXJcIj5cbjxoNSBjbGFzcz1cImNhcmQtdGl0bGUgbWItMFwiPk1vdW5pciBSJ1F1aWJhPC9oNT5cbjxkaXYgY2xhc3M9XCJjYXJkLXRleHQgdGV4dC1ibGFjay01MFwiPkNyZWF0b3I8L2Rpdj5cbjxkaXYgY2xhc3M9XCJjYXJkLXRleHQgdGV4dC1ibGFjay01MFwiPjxhIGhyZWY9XCJodHRwczovL2xpbmtlZGluLmNvbS9pbi9tb3VuaXItci1xdWliYS0xNGFhODRiYS9cIj48ZW0gY2xhc3M9XCJmYWIgZmEtMnggZmEtbGlua2VkaW4gbWItNFwiPiZuYnNwOzwvZW0+PC9hPjwvZGl2PlxuPC9kaXY+XG48L2Rpdj5cbjwvZGl2PlxuPC9kaXY+XG48L2Rpdj4iLCJiYWNrZ3JvdW5kT3BhY2l0eSI6MTAwLCJoZWlnaHQiOiIiLCJiYWNrZ3JvdW5kSW1hZ2UiOiIifSx7Im1lbnVUaXRsZSI6IkNvbnRhY3QiLCJzaG93bkluTWVudSI6dHJ1ZSwiaXNMaW5rIjpmYWxzZSwibGlua1VybCI6IiIsImRpdklkIjoiY29udGFjdCIsImxhYmVsIjoiIiwiYm9keSI6IjxkaXYgY2xhc3M9XCJjb250YWluZXJcIj5cbjxkaXYgY2xhc3M9XCJyb3cganVzdGlmeS1jb250ZW50LWNlbnRlclwiPlxuPGRpdiBjbGFzcz1cImNvbC1sZy04IHRleHQtY2VudGVyXCI+XG48aDIgY2xhc3M9XCJtdC0wXCI+TGV0J3MgR2V0IEluIFRvdWNoITwvaDI+XG48aHIgY2xhc3M9XCJkaXZpZGVyIG15LTRcIiAvPlxuPHAgY2xhc3M9XCJ0ZXh0LW11dGVkIG1iLTVcIj5Zb3UgY2FuIHN1cHBvcnQgdGhpcyBwcm9qZWN0PC9wPlxuPC9kaXY+XG48L2Rpdj5cbjxkaXYgY2xhc3M9XCJyb3dcIj5cbjxkaXYgY2xhc3M9XCJjb2wtbGctMTIgbXItYXV0byB0ZXh0LWNlbnRlclwiPjxhIGNsYXNzPVwiYmxvY2tcIiBocmVmPVwiaHR0cHM6Ly9naXRodWIuY29tL2luZXNjb2luXCI+PGVtIGNsYXNzPVwiZmFiIGZhLWdpdGh1YiBmYS0zeCBtYi0zIHRleHQtbXV0ZWRcIj4mbmJzcDs8L2VtPjwvYT4gPGEgY2xhc3M9XCJibG9ja1wiIGhyZWY9XCJodHRwczovL3QubWUvam9pbmNoYXQvSVREMEVCTWNTYmJTQUxnV2dSUmxXd1wiPjxlbSBjbGFzcz1cImZhYiBmYS10ZWxlZ3JhbSBmYS0zeCBtYi0zIHRleHQtbXV0ZWRcIj4mbmJzcDs8L2VtPjwvYT48L2Rpdj5cbjwvZGl2PlxuPC9kaXY+IiwiYmFja2dyb3VuZE9wYWNpdHkiOjEwMCwiaGVpZ2h0IjoiIiwiYmFja2dyb3VuZEltYWdlIjoiIn0seyJtZW51VGl0bGUiOiJFeHBsb3JlciIsInNob3duSW5NZW51Ijp0cnVlLCJpc0xpbmsiOnRydWUsImxpbmtVcmwiOiJodHRwczovL2V4cGxvcmVyLmluZXNjb2luLm9yZyIsImRpdklkIjoiIiwibGFiZWwiOiIiLCJib2R5IjoiIiwiYmFja2dyb3VuZE9wYWNpdHkiOjEwMCwiaGVpZ2h0IjoiIiwiYmFja2dyb3VuZEltYWdlIjoiIn0seyJtZW51VGl0bGUiOiJXYWxsZXQiLCJzaG93bkluTWVudSI6dHJ1ZSwiaXNMaW5rIjp0cnVlLCJsaW5rVXJsIjoiaHR0cHM6Ly93YWxsZXQuaW5lc2NvaW4ub3JnIiwiZGl2SWQiOiIiLCJsYWJlbCI6IiIsImJvZHkiOiIiLCJiYWNrZ3JvdW5kT3BhY2l0eSI6MTAwLCJoZWlnaHQiOiIiLCJiYWNrZ3JvdW5kSW1hZ2UiOiIifV0sInRoZW1lIjp7ImpzIjp7InZhbHVlIjoiKGZ1bmN0aW9uKCQpIHtcbiAgXCJ1c2Ugc3RyaWN0XCI7IC8vIFN0YXJ0IG9mIHVzZSBzdHJpY3RcblxuICAvLyBTbW9vdGggc2Nyb2xsaW5nIHVzaW5nIGpRdWVyeSBlYXNpbmdcbiAgJCgnYS5qcy1zY3JvbGwtdHJpZ2dlcltocmVmKj1cIiNcIl06bm90KFtocmVmPVwiI1wiXSknKS5jbGljayhmdW5jdGlvbigpIHtcbiAgICBpZiAobG9jYXRpb24ucGF0aG5hbWUucmVwbGFjZSgvXlxcLy8sICcnKSA9PSB0aGlzLnBhdGhuYW1lLnJlcGxhY2UoL15cXC8vLCAnJykgJiYgbG9jYXRpb24uaG9zdG5hbWUgPT0gdGhpcy5ob3N0bmFtZSkge1xuICAgICAgdmFyIHRhcmdldCA9ICQodGhpcy5oYXNoKTtcbiAgICAgIHRhcmdldCA9IHRhcmdldC5sZW5ndGggPyB0YXJnZXQgOiAkKCdbbmFtZT0nICsgdGhpcy5oYXNoLnNsaWNlKDEpICsgJ10nKTtcbiAgICAgIGlmICh0YXJnZXQubGVuZ3RoKSB7XG4gICAgICAgICQoJ2h0bWwsIGJvZHknKS5hbmltYXRlKHtcbiAgICAgICAgICBzY3JvbGxUb3A6ICh0YXJnZXQub2Zmc2V0KCkudG9wIC0gNzIpXG4gICAgICAgIH0sIDEwMDAsIFwiZWFzZUluT3V0RXhwb1wiKTtcbiAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgfVxuICAgIH1cbiAgfSk7XG5cbiAgLy8gQ2xvc2VzIHJlc3BvbnNpdmUgbWVudSB3aGVuIGEgc2Nyb2xsIHRyaWdnZXIgbGluayBpcyBjbGlja2VkXG4gICQoJy5qcy1zY3JvbGwtdHJpZ2dlcicpLmNsaWNrKGZ1bmN0aW9uKCkge1xuICAgICQoJy5uYXZiYXItY29sbGFwc2UnKS5jb2xsYXBzZSgnaGlkZScpO1xuICB9KTtcblxuICAvLyBBY3RpdmF0ZSBzY3JvbGxzcHkgdG8gYWRkIGFjdGl2ZSBjbGFzcyB0byBuYXZiYXIgaXRlbXMgb24gc2Nyb2xsXG4gICQoJ2JvZHknKS5zY3JvbGxzcHkoe1xuICAgIHRhcmdldDogJyNtYWluTmF2JyxcbiAgICBvZmZzZXQ6IDc1XG4gIH0pO1xuXG4gIC8vIENvbGxhcHNlIE5hdmJhclxuICB2YXIgbmF2YmFyQ29sbGFwc2UgPSBmdW5jdGlvbigpIHtcbiAgICBpZiAoJChcIiNtYWluTmF2XCIpLm9mZnNldCgpLnRvcCA+IDEwMCkge1xuICAgICAgJChcIiNtYWluTmF2XCIpLmFkZENsYXNzKFwibmF2YmFyLXNjcm9sbGVkXCIpO1xuICAgIH0gZWxzZSB7XG4gICAgICAkKFwiI21haW5OYXZcIikucmVtb3ZlQ2xhc3MoXCJuYXZiYXItc2Nyb2xsZWRcIik7XG4gICAgfVxuICB9O1xuICAvLyBDb2xsYXBzZSBub3cgaWYgcGFnZSBpcyBub3QgYXQgdG9wXG4gIG5hdmJhckNvbGxhcHNlKCk7XG4gIC8vIENvbGxhcHNlIHRoZSBuYXZiYXIgd2hlbiBwYWdlIGlzIHNjcm9sbGVkXG4gICQod2luZG93KS5zY3JvbGwobmF2YmFyQ29sbGFwc2UpO1xufSkoalF1ZXJ5KTtcbiIsImxpbmtzIjpbeyJsaW5rIjoiLy9jb2RlLmpxdWVyeS5jb20vanF1ZXJ5LTMuMy4xLm1pbi5qcyJ9LHsibGluayI6Ii8vc3RhY2twYXRoLmJvb3RzdHJhcGNkbi5jb20vYm9vdHN0cmFwLzQuMy4xL2pzL2Jvb3RzdHJhcC5taW4uanMifSx7ImxpbmsiOiIvL2NkbmpzLmNsb3VkZmxhcmUuY29tL2FqYXgvbGlicy9wb3BwZXIuanMvMS4xNC43L3VtZC9wb3BwZXIubWluLmpzIn0seyJsaW5rIjoiLy9jZG5qcy5jbG91ZGZsYXJlLmNvbS9hamF4L2xpYnMvanF1ZXJ5LWVhc2luZy8xLjQuMS9qcXVlcnkuZWFzaW5nLm1pbi5qcyJ9LHsibGluayI6Ii8vY2RuanMuY2xvdWRmbGFyZS5jb20vYWpheC9saWJzL21hZ25pZmljLXBvcHVwLmpzLzEuMS4wL2pxdWVyeS5tYWduaWZpYy1wb3B1cC5taW4uanMifV19LCJjc3MiOnsidmFsdWUiOiJib2R5LFxuaHRtbCB7XG4gIHdpZHRoOiAxMDAlO1xuICBoZWlnaHQ6IDEwMCU7XG59XG5cbi50ZXh0LXdoaXRlLTc1IHtcbiAgY29sb3I6IHJnYmEoMjU1LCAyNTUsIDI1NSwgMC43NSk7XG59XG5cbmhyLmRpdmlkZXIge1xuICBtYXgtd2lkdGg6IDMuMjVyZW07XG4gIGJvcmRlci13aWR0aDogMC4ycmVtO1xuICBib3JkZXItY29sb3I6ICM5OTAwQ0M7XG59XG5cbmhyLmxpZ2h0IHtcbiAgYm9yZGVyLWNvbG9yOiAjZmZmO1xufVxuXG4uYnRuIHtcbiAgZm9udC1mYW1pbHk6IFwiTWVycml3ZWF0aGVyIFNhbnNcIiwgLWFwcGxlLXN5c3RlbSwgQmxpbmtNYWNTeXN0ZW1Gb250LCBcIlNlZ29lIFVJXCIsIFJvYm90bywgXCJIZWx2ZXRpY2EgTmV1ZVwiLCBBcmlhbCwgXCJOb3RvIFNhbnNcIiwgc2Fucy1zZXJpZiwgXCJBcHBsZSBDb2xvciBFbW9qaVwiLCBcIlNlZ29lIFVJIEVtb2ppXCIsIFwiU2Vnb2UgVUkgU3ltYm9sXCIsIFwiTm90byBDb2xvciBFbW9qaVwiO1xufVxuXG4uYnRuLXhsIHtcbiAgcGFkZGluZzogMS4yNXJlbSAyLjI1cmVtO1xuICBmb250LXNpemU6IDAuODVyZW07XG4gIGZvbnQtd2VpZ2h0OiA3MDA7XG4gIHRleHQtdHJhbnNmb3JtOiB1cHBlcmNhc2U7XG4gIGJvcmRlcjogbm9uZTtcbiAgYm9yZGVyLXJhZGl1czogMTByZW07XG59XG5cbi5wYWdlLXNlY3Rpb24ge1xuICBwYWRkaW5nOiAzcmVtIDA7XG59XG5cbiNtYWluTmF2IHtcbiAgLXdlYmtpdC1ib3gtc2hhZG93OiAwIDAuNXJlbSAxcmVtIHJnYmEoMCwgMCwgMCwgMC4xNSk7XG4gIGJveC1zaGFkb3c6IDAgMC41cmVtIDFyZW0gcmdiYSgwLCAwLCAwLCAwLjE1KTtcbiAgYmFja2dyb3VuZC1jb2xvcjogI2ZmZjtcbiAgLXdlYmtpdC10cmFuc2l0aW9uOiBiYWNrZ3JvdW5kLWNvbG9yIDAuMnMgZWFzZTtcbiAgdHJhbnNpdGlvbjogYmFja2dyb3VuZC1jb2xvciAwLjJzIGVhc2U7XG59XG5cbiNtYWluTmF2IC5uYXZiYXItYnJhbmQge1xuICBmb250LWZhbWlseTogXCJNZXJyaXdlYXRoZXIgU2Fuc1wiLCAtYXBwbGUtc3lzdGVtLCBCbGlua01hY1N5c3RlbUZvbnQsIFwiU2Vnb2UgVUlcIiwgUm9ib3RvLCBcIkhlbHZldGljYSBOZXVlXCIsIEFyaWFsLCBcIk5vdG8gU2Fuc1wiLCBzYW5zLXNlcmlmLCBcIkFwcGxlIENvbG9yIEVtb2ppXCIsIFwiU2Vnb2UgVUkgRW1vamlcIiwgXCJTZWdvZSBVSSBTeW1ib2xcIiwgXCJOb3RvIENvbG9yIEVtb2ppXCI7XG4gIGZvbnQtd2VpZ2h0OiA3MDA7XG4gIGNvbG9yOiAjMjEyNTI5O1xufVxuXG4jbWFpbk5hdiAubmF2YmFyLW5hdiAubmF2LWl0ZW0gLm5hdi1saW5rIHtcbiAgY29sb3I6ICM2Yzc1N2Q7XG4gIGZvbnQtZmFtaWx5OiBcIk1lcnJpd2VhdGhlciBTYW5zXCIsIC1hcHBsZS1zeXN0ZW0sIEJsaW5rTWFjU3lzdGVtRm9udCwgXCJTZWdvZSBVSVwiLCBSb2JvdG8sIFwiSGVsdmV0aWNhIE5ldWVcIiwgQXJpYWwsIFwiTm90byBTYW5zXCIsIHNhbnMtc2VyaWYsIFwiQXBwbGUgQ29sb3IgRW1vamlcIiwgXCJTZWdvZSBVSSBFbW9qaVwiLCBcIlNlZ29lIFVJIFN5bWJvbFwiLCBcIk5vdG8gQ29sb3IgRW1vamlcIjtcbiAgZm9udC13ZWlnaHQ6IDcwMDtcbiAgZm9udC1zaXplOiAwLjlyZW07XG4gIHBhZGRpbmc6IDAuNzVyZW0gMDtcbn1cblxuI21haW5OYXYgLm5hdmJhci1uYXYgLm5hdi1pdGVtIC5uYXYtbGluazpob3ZlciwgI21haW5OYXYgLm5hdmJhci1uYXYgLm5hdi1pdGVtIC5uYXYtbGluazphY3RpdmUge1xuICBjb2xvcjogIzk5MDBDQztcbn1cblxuI21haW5OYXYgLm5hdmJhci1uYXYgLm5hdi1pdGVtIC5uYXYtbGluay5hY3RpdmUge1xuICBjb2xvcjogIzk5MDBDQyAhaW1wb3J0YW50O1xufVxuXG5AbWVkaWEgKG1pbi13aWR0aDogOTkycHgpIHtcbiAgI21haW5OYXYge1xuICAgIC13ZWJraXQtYm94LXNoYWRvdzogbm9uZTtcbiAgICBib3gtc2hhZG93OiBub25lO1xuICAgIGJhY2tncm91bmQtY29sb3I6IHRyYW5zcGFyZW50O1xuICB9XG4gICNtYWluTmF2IC5uYXZiYXItYnJhbmQge1xuICAgIGNvbG9yOiByZ2JhKDI1NSwgMjU1LCAyNTUsIDAuNyk7XG4gIH1cbiAgI21haW5OYXYgLm5hdmJhci1icmFuZDpob3ZlciB7XG4gICAgY29sb3I6ICNmZmY7XG4gIH1cbiAgI21haW5OYXYgLm5hdmJhci1uYXYgLm5hdi1pdGVtIC5uYXYtbGluayB7XG4gICAgY29sb3I6IHJnYmEoMjU1LCAyNTUsIDI1NSwgMC43KTtcbiAgICBwYWRkaW5nOiAwIDFyZW07XG4gIH1cbiAgI21haW5OYXYgLm5hdmJhci1uYXYgLm5hdi1pdGVtIC5uYXYtbGluazpob3ZlciB7XG4gICAgY29sb3I6ICNmZmY7XG4gIH1cbiAgI21haW5OYXYgLm5hdmJhci1uYXYgLm5hdi1pdGVtOmxhc3QtY2hpbGQgLm5hdi1saW5rIHtcbiAgICBwYWRkaW5nLXJpZ2h0OiAwO1xuICB9XG4gICNtYWluTmF2Lm5hdmJhci1zY3JvbGxlZCB7XG4gICAgLXdlYmtpdC1ib3gtc2hhZG93OiAwIDAuNXJlbSAxcmVtIHJnYmEoMCwgMCwgMCwgMC4xNSk7XG4gICAgYm94LXNoYWRvdzogMCAwLjVyZW0gMXJlbSByZ2JhKDAsIDAsIDAsIDAuMTUpO1xuICAgIGJhY2tncm91bmQtY29sb3I6ICNmZmY7XG4gIH1cbiAgI21haW5OYXYubmF2YmFyLXNjcm9sbGVkIC5uYXZiYXItYnJhbmQge1xuICAgIGNvbG9yOiAjMjEyNTI5O1xuICB9XG4gICNtYWluTmF2Lm5hdmJhci1zY3JvbGxlZCAubmF2YmFyLWJyYW5kOmhvdmVyIHtcbiAgICBjb2xvcjogIzk5MDBDQztcbiAgfVxuICAjbWFpbk5hdi5uYXZiYXItc2Nyb2xsZWQgLm5hdmJhci1uYXYgLm5hdi1pdGVtIC5uYXYtbGluayB7XG4gICAgY29sb3I6ICMyMTI1Mjk7XG4gIH1cbiAgI21haW5OYXYubmF2YmFyLXNjcm9sbGVkIC5uYXZiYXItbmF2IC5uYXYtaXRlbSAubmF2LWxpbms6aG92ZXIge1xuICAgIGNvbG9yOiAjOTkwMENDO1xuICB9XG59XG5cbmhlYWRlci5tYXN0aGVhZCB7XG4gIHBhZGRpbmctdG9wOiAxMHJlbTtcbiAgcGFkZGluZy1ib3R0b206IGNhbGMoMTByZW0gLSA3MnB4KTtcbiAgYmFja2dyb3VuZDogLXdlYmtpdC1ncmFkaWVudChsaW5lYXIsIGxlZnQgdG9wLCBsZWZ0IGJvdHRvbSwgZnJvbShyZ2JhKDkyLCA3NywgNjYsIDAuOCkpLCB0byhyZ2JhKDkyLCA3NywgNjYsIDAuOCkpKSwgdXJsKFwiLi4vaW1nL2JnLW1hc3RoZWFkLmpwZ1wiKTtcbiAgYmFja2dyb3VuZDogbGluZWFyLWdyYWRpZW50KHRvIGJvdHRvbSwgcmdiYSg5MiwgNzcsIDY2LCAwLjgpIDAlLCByZ2JhKDkyLCA3NywgNjYsIDAuOCkgMTAwJSksIHVybChcIi4uL2ltZy9iZy1tYXN0aGVhZC5qcGdcIik7XG4gIGJhY2tncm91bmQtcG9zaXRpb246IGNlbnRlcjtcbiAgYmFja2dyb3VuZC1yZXBlYXQ6IG5vLXJlcGVhdDtcbiAgYmFja2dyb3VuZC1hdHRhY2htZW50OiBzY3JvbGw7XG4gIGJhY2tncm91bmQtc2l6ZTogY292ZXI7XG59XG5cbmhlYWRlci5tYXN0aGVhZCBoMSB7XG4gIGZvbnQtc2l6ZTogMi4yNXJlbTtcbn1cblxuQG1lZGlhIChtaW4td2lkdGg6IDk5MnB4KSB7XG4gIGhlYWRlci5tYXN0aGVhZCB7XG4gICAgaGVpZ2h0OiAxMDB2aDtcbiAgICBtaW4taGVpZ2h0OiA0MHJlbTtcbiAgICBwYWRkaW5nLXRvcDogNzJweDtcbiAgICBwYWRkaW5nLWJvdHRvbTogMDtcbiAgfVxuICBoZWFkZXIubWFzdGhlYWQgcCB7XG4gICAgZm9udC1zaXplOiAxLjE1cmVtO1xuICB9XG4gIGhlYWRlci5tYXN0aGVhZCBoMSB7XG4gICAgZm9udC1zaXplOiAzcmVtO1xuICB9XG59XG5cbkBtZWRpYSAobWluLXdpZHRoOiAxMjAwcHgpIHtcbiAgaGVhZGVyLm1hc3RoZWFkIGgxIHtcbiAgICBmb250LXNpemU6IDMuNXJlbTtcbiAgfVxufVxuXG4jcG9ydGZvbGlvIC5jb250YWluZXItZmx1aWQge1xuICBtYXgtd2lkdGg6IDE5MjBweDtcbn1cblxuI3BvcnRmb2xpbyAuY29udGFpbmVyLWZsdWlkIC5wb3J0Zm9saW8tYm94IHtcbiAgcG9zaXRpb246IHJlbGF0aXZlO1xuICBkaXNwbGF5OiBibG9jaztcbn1cblxuI3BvcnRmb2xpbyAuY29udGFpbmVyLWZsdWlkIC5wb3J0Zm9saW8tYm94IC5wb3J0Zm9saW8tYm94LWNhcHRpb24ge1xuICBkaXNwbGF5OiAtd2Via2l0LWJveDtcbiAgZGlzcGxheTogLW1zLWZsZXhib3g7XG4gIGRpc3BsYXk6IGZsZXg7XG4gIC13ZWJraXQtYm94LWFsaWduOiBjZW50ZXI7XG4gIC1tcy1mbGV4LWFsaWduOiBjZW50ZXI7XG4gIGFsaWduLWl0ZW1zOiBjZW50ZXI7XG4gIC13ZWJraXQtYm94LXBhY2s6IGNlbnRlcjtcbiAgLW1zLWZsZXgtcGFjazogY2VudGVyO1xuICBqdXN0aWZ5LWNvbnRlbnQ6IGNlbnRlcjtcbiAgLXdlYmtpdC1ib3gtb3JpZW50OiB2ZXJ0aWNhbDtcbiAgLXdlYmtpdC1ib3gtZGlyZWN0aW9uOiBub3JtYWw7XG4gIC1tcy1mbGV4LWRpcmVjdGlvbjogY29sdW1uO1xuICBmbGV4LWRpcmVjdGlvbjogY29sdW1uO1xuICB3aWR0aDogMTAwJTtcbiAgaGVpZ2h0OiAxMDAlO1xuICBwb3NpdGlvbjogYWJzb2x1dGU7XG4gIGJvdHRvbTogMDtcbiAgdGV4dC1hbGlnbjogY2VudGVyO1xuICBvcGFjaXR5OiAwO1xuICBjb2xvcjogI2ZmZjtcbiAgYmFja2dyb3VuZDogcmdiYSgxNTMsIDAsIDIwNCwgMC45KTtcbiAgLXdlYmtpdC10cmFuc2l0aW9uOiBvcGFjaXR5IDAuMjVzIGVhc2U7XG4gIHRyYW5zaXRpb246IG9wYWNpdHkgMC4yNXMgZWFzZTtcbiAgdGV4dC1hbGlnbjogY2VudGVyO1xufVxuXG4jcG9ydGZvbGlvIC5jb250YWluZXItZmx1aWQgLnBvcnRmb2xpby1ib3ggLnBvcnRmb2xpby1ib3gtY2FwdGlvbiAucHJvamVjdC1jYXRlZ29yeSB7XG4gIGZvbnQtZmFtaWx5OiBcIk1lcnJpd2VhdGhlciBTYW5zXCIsIC1hcHBsZS1zeXN0ZW0sIEJsaW5rTWFjU3lzdGVtRm9udCwgXCJTZWdvZSBVSVwiLCBSb2JvdG8sIFwiSGVsdmV0aWNhIE5ldWVcIiwgQXJpYWwsIFwiTm90byBTYW5zXCIsIHNhbnMtc2VyaWYsIFwiQXBwbGUgQ29sb3IgRW1vamlcIiwgXCJTZWdvZSBVSSBFbW9qaVwiLCBcIlNlZ29lIFVJIFN5bWJvbFwiLCBcIk5vdG8gQ29sb3IgRW1vamlcIjtcbiAgZm9udC1zaXplOiAwLjg1cmVtO1xuICBmb250LXdlaWdodDogNjAwO1xuICB0ZXh0LXRyYW5zZm9ybTogdXBwZXJjYXNlO1xufVxuXG4jcG9ydGZvbGlvIC5jb250YWluZXItZmx1aWQgLnBvcnRmb2xpby1ib3ggLnBvcnRmb2xpby1ib3gtY2FwdGlvbiAucHJvamVjdC1uYW1lIHtcbiAgZm9udC1zaXplOiAxLjJyZW07XG59XG5cbiNwb3J0Zm9saW8gLmNvbnRhaW5lci1mbHVpZCAucG9ydGZvbGlvLWJveDpob3ZlciAucG9ydGZvbGlvLWJveC1jYXB0aW9uIHtcbiAgb3BhY2l0eTogMTtcbn0iLCJsaW5rcyI6W3sibGluayI6Ii8vY2RuanMuY2xvdWRmbGFyZS5jb20vYWpheC9saWJzL2ZvbnQtYXdlc29tZS81LjEwLjIvY3NzL2FsbC5taW4uY3NzIn0seyJsaW5rIjoiLy9mb250cy5nb29nbGVhcGlzLmNvbS9jc3M\/ZmFtaWx5PU1lcnJpd2VhdGhlcitTYW5zOjQwMCw3MDAifSx7ImxpbmsiOiIvL2ZvbnRzLmdvb2dsZWFwaXMuY29tL2Nzcz9mYW1pbHk9TWVycml3ZWF0aGVyOjQwMCwzMDAsMzAwaXRhbGljLDQwMGl0YWxpYyw3MDAsNzAwaXRhbGljIn0seyJsaW5rIjoiLy9jZG5qcy5jbG91ZGZsYXJlLmNvbS9hamF4L2xpYnMvbWFnbmlmaWMtcG9wdXAuanMvMS4xLjAvbWFnbmlmaWMtcG9wdXAubWluLmNzcyJ9LHsibGluayI6Ii8vc3RhY2twYXRoLmJvb3RzdHJhcGNkbi5jb20vYm9vdHN0cmFwLzQuMy4xL2Nzcy9ib290c3RyYXAubWluLmNzcyJ9XX19LCJwcm9kdWN0cyI6W3siYWN0aXZlIjoiZmFsc2UiLCJza3UiOiI2ZTNkNWQxYy02OTZiLTQyMDYtODEzZi1hMjdmZGY4YmI1ZTIiLCJ0aXRsZSI6Ikx1ZWlsd2l0ei1IYWhuIiwiZGVzY3JpcHRpb24iOiJ1dCB0ZWxsdXMgbnVsbGEgdXQgZXJhdCBpZCBtYXVyaXMgdnVscHV0YXRlIGVsZW1lbnR1bSBudWxsYW0gdmFyaXVzIG51bGxhIiwiYW1vdW50IjoiNjUiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuYm1wL2ZmNDQ0NC9mZmZmZmYifSx7ImFjdGl2ZSI6IiIsInNrdSI6IjQwOWZiYjYzLTU4ZDUtNDY3MS1hMzEzLTA3N2UxMTQ1NDY1NCIsInRpdGxlIjoiT3NpbnNraSwgSGVybWFubiBhbmQgV2lsbCIsImRlc2NyaXB0aW9uIjoidXQgc3VzY2lwaXQgYSBmZXVnaWF0IGV0IGVyb3MgdmVzdGlidWx1bSBhYyBlc3QgbGFjaW5pYSBuaXNpIHZlbmVuYXRpcyB0cmlzdGlxdWUgZnVzY2UgY29uZ3VlIGRpYW0iLCJhbW91bnQiOiI3OSIsImN1cnJlbmN5IjoidXNkIiwiaW1hZ2UiOiJodHRwOi8vZHVtbXlpbWFnZS5jb20vMzAweDQwMC5qcGcvZGRkZGRkLzAwMDAwMCJ9LHsiYWN0aXZlIjoidHJ1ZSIsInNrdSI6IjE4MWUzZTk2LTMxN2ItNDBlOC1hY2M2LTNkODNhNTYxYTM0YiIsInRpdGxlIjoiVHVyY290dGUtRGlldHJpY2giLCJkZXNjcmlwdGlvbiI6InF1aXMgbGliZXJvIG51bGxhbSBzaXQgYW1ldCB0dXJwaXMgZWxlbWVudHVtIGxpZ3VsYSB2ZWhpY3VsYSBjb25zZXF1YXQgbW9yYmkgYSBpcHN1bSBpbnRlZ2VyIGEgbmliaCIsImFtb3VudCI6IjU1IiwiY3VycmVuY3kiOiJ1c2QiLCJpbWFnZSI6Imh0dHA6Ly9kdW1teWltYWdlLmNvbS8zMDB4NDAwLnBuZy9jYzAwMDAvZmZmZmZmIn0seyJhY3RpdmUiOiJmYWxzZSIsInNrdSI6IjQxZjU1YTlhLTZjNDktNDE0Zi1hZDM1LTZmZjgzMWE2YzY1ZCIsInRpdGxlIjoiQ3J1aWNrc2hhbmstV2Vpc3NuYXQiLCJkZXNjcmlwdGlvbiI6ImVyYXQgdmVzdGlidWx1bSBzZWQgbWFnbmEgYXQgbnVuYyBjb21tb2RvIHBsYWNlcmF0IHByYWVzZW50IGJsYW5kaXQgbmFtIiwiYW1vdW50IjoiMzMiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAucG5nL2NjMDAwMC9mZmZmZmYifSx7ImFjdGl2ZSI6IiIsInNrdSI6ImZlZjE4Nzk2LWYxZDYtNDM1OS04NTAwLTEyMjhjYzk5MTNhMCIsInRpdGxlIjoiU2NoYWRlbi1GZWVuZXkiLCJkZXNjcmlwdGlvbiI6InF1aXNxdWUgZXJhdCBlcm9zIHZpdmVycmEgZWdldCBjb25ndWUgZWdldCBzZW1wZXIgcnV0cnVtIG51bGxhIG51bmMgcHVydXMgcGhhc2VsbHVzIGluIiwiYW1vdW50IjoiMjkiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAucG5nLzVmYTJkZC9mZmZmZmYifSx7ImFjdGl2ZSI6InRydWUiLCJza3UiOiI5MzVlNDgwNi05Y2E3LTQxZDUtYWY2Yi0xYzFmMTc1ZTcxY2UiLCJ0aXRsZSI6IkhhbWlsbCBHcm91cCIsImRlc2NyaXB0aW9uIjoidXQgb2RpbyBjcmFzIG1pIHBlZGUgbWFsZXN1YWRhIGluIGltcGVyZGlldCBldCBjb21tb2RvIHZ1bHB1dGF0ZSIsImFtb3VudCI6IjIiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuYm1wL2NjMDAwMC9mZmZmZmYifSx7ImFjdGl2ZSI6IiIsInNrdSI6ImJiM2IzOTMwLWQ4OGEtNDYxOC1iOTVjLTYzYzRhNzY0ZmQ1MiIsInRpdGxlIjoiRmFkZWwsIExlZmZsZXIgYW5kIE1pbGxlciIsImRlc2NyaXB0aW9uIjoibWFnbmEgYmliZW5kdW0gaW1wZXJkaWV0IG51bGxhbSBvcmNpIHBlZGUgdmVuZW5hdGlzIG5vbiBzb2RhbGVzIHNlZCB0aW5jaWR1bnQgZXUgZmVsaXMiLCJhbW91bnQiOiIyNyIsImN1cnJlbmN5IjoidXNkIiwiaW1hZ2UiOiJodHRwOi8vZHVtbXlpbWFnZS5jb20vMzAweDQwMC5qcGcvY2MwMDAwL2ZmZmZmZiJ9LHsiYWN0aXZlIjoiIiwic2t1IjoiNzljMGVlNDYtNWU0ZC00NmM4LTgxYjEtMjBlZWIyZGIwNTA3IiwidGl0bGUiOiJNYW5uIEdyb3VwIiwiZGVzY3JpcHRpb24iOiJlbGVtZW50dW0gZXUgaW50ZXJkdW0gZXUgdGluY2lkdW50IGluIGxlbyBtYWVjZW5hcyBwdWx2aW5hciBsb2JvcnRpcyBlc3QgcGhhc2VsbHVzIHNpdCBhbWV0IGVyYXQiLCJhbW91bnQiOiI5MSIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOi8vZHVtbXlpbWFnZS5jb20vMzAweDQwMC5qcGcvZGRkZGRkLzAwMDAwMCJ9LHsiYWN0aXZlIjoidHJ1ZSIsInNrdSI6IjFjYzdjMTgyLThkMWYtNDA0ZC04NTMyLTlmODc0ZmNlMWMwZSIsInRpdGxlIjoiTWFnZ2lvLUFua3VuZGluZyIsImRlc2NyaXB0aW9uIjoib2RpbyBwb3J0dGl0b3IgaWQgY29uc2VxdWF0IGluIGNvbnNlcXVhdCB1dCBudWxsYSBzZWQgYWNjdW1zYW4iLCJhbW91bnQiOiI0MiIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOi8vZHVtbXlpbWFnZS5jb20vMzAweDQwMC5qcGcvZGRkZGRkLzAwMDAwMCJ9LHsiYWN0aXZlIjoidHJ1ZSIsInNrdSI6IjQ0YTZiM2RmLTVlMjUtNDAyNC05YTJiLWY3MDRmYjQ1MzZjZCIsInRpdGxlIjoiS2lobiwgTWNLZW56aWUgYW5kIEhlYW5leSIsImRlc2NyaXB0aW9uIjoiYWNjdW1zYW4gdG9ydG9yIHF1aXMgdHVycGlzIHNlZCBhbnRlIHZpdmFtdXMgdG9ydG9yIGR1aXMgbWF0dGlzIGVnZXN0YXMgbWV0dXMgYWVuZWFuIiwiYW1vdW50IjoiODciLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuanBnLzVmYTJkZC9mZmZmZmYifSx7ImFjdGl2ZSI6IiIsInNrdSI6IjE4YjE0YmExLWNlZTctNGNkYS04OTc3LTFlODFhNDc4MmMwYiIsInRpdGxlIjoiSGFydmV5LCBDb25yb3kgYW5kIEJlY2tlciIsImRlc2NyaXB0aW9uIjoiZWdldCBydXRydW0gYXQgbG9yZW0gaW50ZWdlciB0aW5jaWR1bnQgYW50ZSB2ZWwgaXBzdW0gcHJhZXNlbnQgYmxhbmRpdCBsYWNpbmlhIGVyYXQiLCJhbW91bnQiOiI0NCIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOi8vZHVtbXlpbWFnZS5jb20vMzAweDQwMC5qcGcvNWZhMmRkL2ZmZmZmZiJ9LHsiYWN0aXZlIjoiZmFsc2UiLCJza3UiOiJlNTIwNzcxNC1jNGY3LTRmNjQtYmFiYy1mMzY1MmZjZTU3M2EiLCJ0aXRsZSI6IkdyaW1lcyBJbmMiLCJkZXNjcmlwdGlvbiI6ImFudGUgaXBzdW0gcHJpbWlzIGluIGZhdWNpYnVzIG9yY2kgbHVjdHVzIGV0IHVsdHJpY2VzIHBvc3VlcmUgY3ViaWxpYSBjdXJhZSBudWxsYSBkYXBpYnVzIGRvbG9yIiwiYW1vdW50IjoiNDUiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuYm1wLzVmYTJkZC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiNmZiNjkzYzQtM2FhYi00YjdkLWE5ZDctYWU3ZTEzYzBmMjA2IiwidGl0bGUiOiJWb24tQ3VtbWVyYXRhIiwiZGVzY3JpcHRpb24iOiJhIGxpYmVybyBuYW0gZHVpIHByb2luIGxlbyBvZGlvIHBvcnR0aXRvciBpZCBjb25zZXF1YXQgaW4gY29uc2VxdWF0IHV0IG51bGxhIHNlZCBhY2N1bXNhbiBmZWxpcyB1dCBhdCBkb2xvciIsImFtb3VudCI6IjYzIiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6Ly9kdW1teWltYWdlLmNvbS8zMDB4NDAwLmJtcC81ZmEyZGQvZmZmZmZmIn0seyJhY3RpdmUiOiJ0cnVlIiwic2t1IjoiZjY5YmI5NDUtNThhYS00NDI1LWEwZWUtZjg0MzMyOTQzYjRmIiwidGl0bGUiOiJQZmFubmVyc3RpbGwsIFNjaGltbWVsIGFuZCBDYXJ0ZXIiLCJkZXNjcmlwdGlvbiI6InBlZGUgdmVuZW5hdGlzIG5vbiBzb2RhbGVzIHNlZCB0aW5jaWR1bnQgZXUgZmVsaXMgZnVzY2UgcG9zdWVyZSBmZWxpcyBzZWQgbGFjdXMiLCJhbW91bnQiOiI3NSIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOi8vZHVtbXlpbWFnZS5jb20vMzAweDQwMC5wbmcvZmY0NDQ0L2ZmZmZmZiJ9LHsiYWN0aXZlIjoiIiwic2t1IjoiM2JmNDIwMTItMzBkNy00ODk1LWI4ODQtMGNkNmEzY2VlZmEwIiwidGl0bGUiOiJNZXJ0eiwgU3RlaHIgYW5kIENvbm5lbGx5IiwiZGVzY3JpcHRpb24iOiJlcmF0IHZlc3RpYnVsdW0gc2VkIG1hZ25hIGF0IG51bmMgY29tbW9kbyBwbGFjZXJhdCBwcmFlc2VudCBibGFuZGl0IG5hbSBudWxsYSBpbnRlZ2VyIHBlZGUiLCJhbW91bnQiOiIxOCIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOi8vZHVtbXlpbWFnZS5jb20vMzAweDQwMC5qcGcvY2MwMDAwL2ZmZmZmZiJ9LHsiYWN0aXZlIjoidHJ1ZSIsInNrdSI6ImE4NzIxZDNmLWU4MDktNGZiZi1iNTdmLTM2OWVlYmRhYjFmMyIsInRpdGxlIjoiRGF2aXMtTGVmZmxlciIsImRlc2NyaXB0aW9uIjoibGVjdHVzIGluIHF1YW0gZnJpbmdpbGxhIHJob25jdXMgbWF1cmlzIGVuaW0gbGVvIHJob25jdXMgc2VkIHZlc3RpYnVsdW0gc2l0IGFtZXQgY3Vyc3VzIGlkIHR1cnBpcyBpbnRlZ2VyIGFsaXF1ZXQiLCJhbW91bnQiOiI3MSIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOi8vZHVtbXlpbWFnZS5jb20vMzAweDQwMC5wbmcvZmY0NDQ0L2ZmZmZmZiJ9LHsiYWN0aXZlIjoidHJ1ZSIsInNrdSI6IjkxMzE1ZThhLTg1OTAtNGU3YS04ZmM0LWE5YzVmMDEzMGMxMyIsInRpdGxlIjoiR2xlYXNvbi1CcmFkdGtlIiwiZGVzY3JpcHRpb24iOiJtaSBpbiBwb3J0dGl0b3IgcGVkZSBqdXN0byBldSBtYXNzYSBkb25lYyBkYXBpYnVzIGR1aXMiLCJhbW91bnQiOiI5NiIsImN1cnJlbmN5IjoidXNkIiwiaW1hZ2UiOiJodHRwOi8vZHVtbXlpbWFnZS5jb20vMzAweDQwMC5ibXAvZGRkZGRkLzAwMDAwMCJ9LHsiYWN0aXZlIjoiIiwic2t1IjoiNjZlMzk5MDUtZDMyNy00ZWNkLTg0NTUtZGY2YTg5YzA4YjI0IiwidGl0bGUiOiJLaXJsaW4tS3VoaWMiLCJkZXNjcmlwdGlvbiI6ImluIGxpYmVybyB1dCBtYXNzYSB2b2x1dHBhdCBjb252YWxsaXMgbW9yYmkgb2RpbyBvZGlvIGVsZW1lbnR1bSBldSBpbnRlcmR1bSIsImFtb3VudCI6Ijg1IiwiY3VycmVuY3kiOiJ1c2QiLCJpbWFnZSI6Imh0dHA6Ly9kdW1teWltYWdlLmNvbS8zMDB4NDAwLmJtcC9jYzAwMDAvZmZmZmZmIn0seyJhY3RpdmUiOiJmYWxzZSIsInNrdSI6Ijc4MDRiNTY5LWI1ZjYtNDlkMy1hZTM4LWI2ZGRmZmFlNTJlNCIsInRpdGxlIjoiTydDb25uZXItS29zcyIsImRlc2NyaXB0aW9uIjoic2l0IGFtZXQgc2FwaWVuIGRpZ25pc3NpbSB2ZXN0aWJ1bHVtIHZlc3RpYnVsdW0gYW50ZSBpcHN1bSBwcmltaXMgaW4gZmF1Y2lidXMgb3JjaSBsdWN0dXMgZXQgdWx0cmljZXMgcG9zdWVyZSBjdWJpbGlhIGN1cmFlIiwiYW1vdW50IjoiMzQiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuanBnL2RkZGRkZC8wMDAwMDAifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiNWQyOWZkMjctMmI1NC00YWEwLTljMDUtM2Y3NTg0NmZhNjMyIiwidGl0bGUiOiJTY2h1bHR6IGFuZCBTb25zIiwiZGVzY3JpcHRpb24iOiJwcmFlc2VudCBpZCBtYXNzYSBpZCBuaXNsIHZlbmVuYXRpcyBsYWNpbmlhIGFlbmVhbiBzaXQgYW1ldCBqdXN0byBtb3JiaSB1dCBvZGlvIGNyYXMgbWkgcGVkZSBtYWxlc3VhZGEiLCJhbW91bnQiOiI5MSIsImN1cnJlbmN5IjoidXNkIiwiaW1hZ2UiOiJodHRwOi8vZHVtbXlpbWFnZS5jb20vMzAweDQwMC5wbmcvZmY0NDQ0L2ZmZmZmZiJ9LHsiYWN0aXZlIjoiIiwic2t1IjoiYTliZDQwYzMtMDhiMC00YTMxLWEwYzAtZmI4YjhkYzJhMDQ4IiwidGl0bGUiOiJTdHJlaWNoLUhhbWlsbCIsImRlc2NyaXB0aW9uIjoiaGVuZHJlcml0IGF0IHZ1bHB1dGF0ZSB2aXRhZSBuaXNsIGFlbmVhbiBsZWN0dXMgcGVsbGVudGVzcXVlIGVnZXQgbnVuYyBkb25lYyBxdWlzIiwiYW1vdW50IjoiMTgiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuYm1wL2ZmNDQ0NC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiNjY3NDIwOGYtMzUyYi00MzJkLTlkMDAtNzVjNzQyNmVmMDI5IiwidGl0bGUiOiJQYXVjZWstU2NobWlkdCIsImRlc2NyaXB0aW9uIjoiY29uc2VxdWF0IHV0IG51bGxhIHNlZCBhY2N1bXNhbiBmZWxpcyB1dCBhdCBkb2xvciBxdWlzIG9kaW8gY29uc2VxdWF0IHZhcml1cyBpbnRlZ2VyIiwiYW1vdW50IjoiNDUiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuYm1wLzVmYTJkZC9mZmZmZmYifSx7ImFjdGl2ZSI6IiIsInNrdSI6IjUwOTMyNzMyLWI1ZjItNDgxZi1iYzcyLTkyNTdlYzI1ODIzMSIsInRpdGxlIjoiU3Rva2VzLVBmZWZmZXIiLCJkZXNjcmlwdGlvbiI6ImxvcmVtIGludGVnZXIgdGluY2lkdW50IGFudGUgdmVsIGlwc3VtIHByYWVzZW50IGJsYW5kaXQgbGFjaW5pYSBlcmF0IHZlc3RpYnVsdW0gc2VkIG1hZ25hIGF0IiwiYW1vdW50IjoiMzYiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuYm1wL2NjMDAwMC9mZmZmZmYifSx7ImFjdGl2ZSI6IiIsInNrdSI6ImZlZGIyNzJhLTMxYjYtNDkwZS1iZWE5LWQyYTFlZmMwZGE1YSIsInRpdGxlIjoiQ3JlbWluLCBLc2hsZXJpbiBhbmQgS3V0Y2giLCJkZXNjcmlwdGlvbiI6ImxpYmVybyB1dCBtYXNzYSB2b2x1dHBhdCBjb252YWxsaXMgbW9yYmkgb2RpbyBvZGlvIGVsZW1lbnR1bSBldSBpbnRlcmR1bSBldSB0aW5jaWR1bnQgaW4gbGVvIG1hZWNlbmFzIHB1bHZpbmFyIGxvYm9ydGlzIGVzdCIsImFtb3VudCI6IjIyIiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6Ly9kdW1teWltYWdlLmNvbS8zMDB4NDAwLmpwZy81ZmEyZGQvZmZmZmZmIn0seyJhY3RpdmUiOiIiLCJza3UiOiI4NzdhNDEyMi0xOTY0LTQ2NzItYmRmOC1jZWVhNTdiMTBkOGYiLCJ0aXRsZSI6IkhhZ2VuZXMgSW5jIiwiZGVzY3JpcHRpb24iOiJjb25ndWUgZWxlbWVudHVtIGluIGhhYyBoYWJpdGFzc2UgcGxhdGVhIGRpY3R1bXN0IG1vcmJpIHZlc3RpYnVsdW0gdmVsaXQgaWQgcHJldGl1bSBpYWN1bGlzIGRpYW0gZXJhdCBmZXJtZW50dW0ganVzdG8iLCJhbW91bnQiOiI5NSIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOi8vZHVtbXlpbWFnZS5jb20vMzAweDQwMC5ibXAvZGRkZGRkLzAwMDAwMCJ9LHsiYWN0aXZlIjoidHJ1ZSIsInNrdSI6ImM3YmY0MTNkLTIxNTAtNDBlZC04MDVhLTZkMmI3NjVmYmFjNCIsInRpdGxlIjoiRmF5IGFuZCBTb25zIiwiZGVzY3JpcHRpb24iOiJhdWN0b3Igc2VkIHRyaXN0aXF1ZSBpbiB0ZW1wdXMgc2l0IGFtZXQgc2VtIGZ1c2NlIGNvbnNlcXVhdCBudWxsYSIsImFtb3VudCI6IjM0IiwiY3VycmVuY3kiOiJ1c2QiLCJpbWFnZSI6Imh0dHA6Ly9kdW1teWltYWdlLmNvbS8zMDB4NDAwLmpwZy9jYzAwMDAvZmZmZmZmIn0seyJhY3RpdmUiOiJmYWxzZSIsInNrdSI6IjU0ZWQ3YmQ0LWE2ODYtNDkxMi05ZTA4LWRkMjNkZWM3Nzk5MyIsInRpdGxlIjoiSGFtaWxsLCBQb2xsaWNoIGFuZCBNb2VuIiwiZGVzY3JpcHRpb24iOiJlZ2V0IHJ1dHJ1bSBhdCBsb3JlbSBpbnRlZ2VyIHRpbmNpZHVudCBhbnRlIHZlbCBpcHN1bSBwcmFlc2VudCBibGFuZGl0IGxhY2luaWEgZXJhdCB2ZXN0aWJ1bHVtIiwiYW1vdW50IjoiMTgiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuYm1wLzVmYTJkZC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiNTVkNTkxNjYtZjczMy00ZTBhLTg4OWUtYjVjODM4ZThiMTk1IiwidGl0bGUiOiJTY2h1bGlzdC1MYW5nIiwiZGVzY3JpcHRpb24iOiJzb2RhbGVzIHNjZWxlcmlzcXVlIG1hdXJpcyBzaXQgYW1ldCBlcm9zIHN1c3BlbmRpc3NlIGFjY3Vtc2FuIHRvcnRvciBxdWlzIHR1cnBpcyBzZWQgYW50ZSB2aXZhbXVzIHRvcnRvciBkdWlzIG1hdHRpcyBlZ2VzdGFzIiwiYW1vdW50IjoiMjEiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuanBnL2ZmNDQ0NC9mZmZmZmYifSx7ImFjdGl2ZSI6IiIsInNrdSI6IjM5N2VhZWZiLTBiNGMtNGIxZS1hMThjLWFhOGE4NjkzZjc0OCIsInRpdGxlIjoiQ29ubmVsbHkgSW5jIiwiZGVzY3JpcHRpb24iOiJtYWduaXMgZGlzIHBhcnR1cmllbnQgbW9udGVzIG5hc2NldHVyIHJpZGljdWx1cyBtdXMgdml2YW11cyB2ZXN0aWJ1bHVtIHNhZ2l0dGlzIHNhcGllbiBjdW0iLCJhbW91bnQiOiI0NiIsImN1cnJlbmN5IjoiZXVyIiwiaW1hZ2UiOiJodHRwOi8vZHVtbXlpbWFnZS5jb20vMzAweDQwMC5ibXAvZmY0NDQ0L2ZmZmZmZiJ9LHsiYWN0aXZlIjoiZmFsc2UiLCJza3UiOiIwNThmMGE3Mi05NWRmLTQ0M2QtYWQwYi0wNmE2YzJkYzUyMTkiLCJ0aXRsZSI6IlN0cmFja2UsIEZlZXN0IGFuZCBBbHRlbndlcnRoIiwiZGVzY3JpcHRpb24iOiJ0aW5jaWR1bnQgaW4gbGVvIG1hZWNlbmFzIHB1bHZpbmFyIGxvYm9ydGlzIGVzdCBwaGFzZWxsdXMgc2l0IGFtZXQgZXJhdCBudWxsYSB0ZW1wdXMgdml2YW11cyBpbiBmZWxpcyBldSBzYXBpZW4gY3Vyc3VzIHZlc3RpYnVsdW0iLCJhbW91bnQiOiI2OCIsImN1cnJlbmN5IjoidXNkIiwiaW1hZ2UiOiJodHRwOi8vZHVtbXlpbWFnZS5jb20vMzAweDQwMC5qcGcvY2MwMDAwL2ZmZmZmZiJ9LHsiYWN0aXZlIjoidHJ1ZSIsInNrdSI6ImVmYzZjZjgwLTFkYTgtNDI1OS05MzJlLTBiZTk1NDVhNWYzMyIsInRpdGxlIjoiVGhpZWwtSGlja2xlIiwiZGVzY3JpcHRpb24iOiJudW5jIHZlc3RpYnVsdW0gYW50ZSBpcHN1bSBwcmltaXMgaW4gZmF1Y2lidXMgb3JjaSBsdWN0dXMgZXQgdWx0cmljZXMgcG9zdWVyZSBjdWJpbGlhIGN1cmFlIG1hdXJpcyB2aXZlcnJhIiwiYW1vdW50IjoiNzUiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAucG5nL2NjMDAwMC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiMTYzN2FlYzEtMmQzNi00YzFmLWJlYjgtYWYzYjE5Nzg1OTU5IiwidGl0bGUiOiJHb2xkbmVyLVJvd2UiLCJkZXNjcmlwdGlvbiI6ImF1Z3VlIHZlc3RpYnVsdW0gcnV0cnVtIHJ1dHJ1bSBuZXF1ZSBhZW5lYW4gYXVjdG9yIGdyYXZpZGEgc2VtIHByYWVzZW50IGlkIG1hc3NhIGlkIG5pc2wgdmVuZW5hdGlzIGxhY2luaWEgYWVuZWFuIHNpdCIsImFtb3VudCI6IjEzIiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6Ly9kdW1teWltYWdlLmNvbS8zMDB4NDAwLmpwZy81ZmEyZGQvZmZmZmZmIn0seyJhY3RpdmUiOiJ0cnVlIiwic2t1IjoiMWQ0ZGZkMGQtNDgyOS00MWEyLTg1OGMtNTY0MDM3OWExZDU4IiwidGl0bGUiOiJDdW1tZXJhdGEsIEtvaGxlciBhbmQgTWlsbGVyIiwiZGVzY3JpcHRpb24iOiJwZWxsZW50ZXNxdWUgcXVpc3F1ZSBwb3J0YSB2b2x1dHBhdCBlcmF0IHF1aXNxdWUgZXJhdCBlcm9zIHZpdmVycmEgZWdldCBjb25ndWUgZWdldCBzZW1wZXIgcnV0cnVtIiwiYW1vdW50IjoiNzIiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuanBnL2RkZGRkZC8wMDAwMDAifSx7ImFjdGl2ZSI6IiIsInNrdSI6Ijg3ZmNjMmI3LWYwY2UtNDYyNS1iNTM1LTIwNDg1MjljMDkzOCIsInRpdGxlIjoiSGVyem9nLVdpc29reSIsImRlc2NyaXB0aW9uIjoiYXVndWUgcXVhbSBzb2xsaWNpdHVkaW4gdml0YWUgY29uc2VjdGV0dWVyIGVnZXQgcnV0cnVtIGF0IGxvcmVtIGludGVnZXIgdGluY2lkdW50IGFudGUgdmVsIGlwc3VtIHByYWVzZW50IGJsYW5kaXQgbGFjaW5pYSBlcmF0IHZlc3RpYnVsdW0gc2VkIiwiYW1vdW50IjoiMzUiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAucG5nL2RkZGRkZC8wMDAwMDAifSx7ImFjdGl2ZSI6IiIsInNrdSI6IjViMzBkZDg3LTkwODctNDllMy1hOGI1LTczZTFlYTQ1ZmQ5NCIsInRpdGxlIjoiT2t1bmV2YS1IYXVjayIsImRlc2NyaXB0aW9uIjoiZXRpYW0ganVzdG8gZXRpYW0gcHJldGl1bSBpYWN1bGlzIGp1c3RvIGluIGhhYyBoYWJpdGFzc2UgcGxhdGVhIGRpY3R1bXN0IGV0aWFtIGZhdWNpYnVzIGN1cnN1cyB1cm5hIHV0IiwiYW1vdW50IjoiNjciLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuYm1wL2NjMDAwMC9mZmZmZmYifSx7ImFjdGl2ZSI6IiIsInNrdSI6IjJmZDg5ZWFiLTg4ZjktNGRkZi05YmJhLTM3YWVkNzIzMTU5ZSIsInRpdGxlIjoiRmF5LVd1Y2tlcnQiLCJkZXNjcmlwdGlvbiI6InN1c2NpcGl0IG51bGxhIGVsaXQgYWMgbnVsbGEgc2VkIHZlbCBlbmltIHNpdCBhbWV0IG51bmMgdml2ZXJyYSBkYXBpYnVzIG51bGxhIiwiYW1vdW50IjoiNTciLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuYm1wL2NjMDAwMC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiMDE4Y2FmNjQtYTAzOC00NWI5LTg1OGQtZTBmNTMzY2I4NjE2IiwidGl0bGUiOiJUcm9tcC1SZWljaGVsIiwiZGVzY3JpcHRpb24iOiJxdWlzIHR1cnBpcyBzZWQgYW50ZSB2aXZhbXVzIHRvcnRvciBkdWlzIG1hdHRpcyBlZ2VzdGFzIG1ldHVzIGFlbmVhbiBmZXJtZW50dW0gZG9uZWMiLCJhbW91bnQiOiI0NCIsImN1cnJlbmN5IjoidXNkIiwiaW1hZ2UiOiJodHRwOi8vZHVtbXlpbWFnZS5jb20vMzAweDQwMC5ibXAvZmY0NDQ0L2ZmZmZmZiJ9LHsiYWN0aXZlIjoiIiwic2t1IjoiNzlkYmNhNjAtNTJiMC00MjdkLTg2ZjctNGUyMzBiZTMxODQxIiwidGl0bGUiOiJGcmllc2VuLVdoaXRlIiwiZGVzY3JpcHRpb24iOiJtb3JiaSB2ZXN0aWJ1bHVtIHZlbGl0IGlkIHByZXRpdW0gaWFjdWxpcyBkaWFtIGVyYXQgZmVybWVudHVtIGp1c3RvIG5lYyBjb25kaW1lbnR1bSBuZXF1ZSBzYXBpZW4gcGxhY2VyYXQgYW50ZSBudWxsYSBqdXN0byBhbGlxdWFtIiwiYW1vdW50IjoiNTAiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuYm1wL2RkZGRkZC8wMDAwMDAifSx7ImFjdGl2ZSI6InRydWUiLCJza3UiOiJiZTJiNTBiYy1kYWY1LTRmYmMtYTIzNC0xZjkzMTg5ZGE1YWYiLCJ0aXRsZSI6Ik9sc29uLCBCYXJyb3dzIGFuZCBRdWlnbGV5IiwiZGVzY3JpcHRpb24iOiJudWxsYSB1dCBlcmF0IGlkIG1hdXJpcyB2dWxwdXRhdGUgZWxlbWVudHVtIG51bGxhbSB2YXJpdXMgbnVsbGEgZmFjaWxpc2kgY3JhcyBub24gdmVsaXQgbmVjIG5pc2kgdnVscHV0YXRlIG5vbnVtbXkgbWFlY2VuYXMgdGluY2lkdW50IiwiYW1vdW50IjoiODAiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuanBnL2NjMDAwMC9mZmZmZmYifSx7ImFjdGl2ZSI6IiIsInNrdSI6ImVhOThjZWNlLWViMTMtNDNiMy1hZDFkLWEyOTY3MWMzZDhiNSIsInRpdGxlIjoiTGVncm9zLUdsb3ZlciIsImRlc2NyaXB0aW9uIjoiY3VtIHNvY2lpcyBuYXRvcXVlIHBlbmF0aWJ1cyBldCBtYWduaXMgZGlzIHBhcnR1cmllbnQgbW9udGVzIG5hc2NldHVyIHJpZGljdWx1cyIsImFtb3VudCI6IjkwIiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6Ly9kdW1teWltYWdlLmNvbS8zMDB4NDAwLnBuZy9mZjQ0NDQvZmZmZmZmIn0seyJhY3RpdmUiOiJ0cnVlIiwic2t1IjoiZmI4OGEwMDgtMzVkOC00MmQ1LThjMjctYmI1MmUxMzZlYjIxIiwidGl0bGUiOiJXYXRzaWNhLCBDb3JrZXJ5IGFuZCBGYXkiLCJkZXNjcmlwdGlvbiI6Imlwc3VtIHByaW1pcyBpbiBmYXVjaWJ1cyBvcmNpIGx1Y3R1cyBldCB1bHRyaWNlcyBwb3N1ZXJlIGN1YmlsaWEgY3VyYWUgZHVpcyBmYXVjaWJ1cyBhY2N1bXNhbiBvZGlvIiwiYW1vdW50IjoiMjkiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAuanBnLzVmYTJkZC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiYzQ5MjViYjUtMGY5MS00OWUwLWJmOTctMTUzNmYxNzk2NDJhIiwidGl0bGUiOiJQdXJkeSwgS2lybGluIGFuZCBKb2huc3RvbiIsImRlc2NyaXB0aW9uIjoibm9uIHByZXRpdW0gcXVpcyBsZWN0dXMgc3VzcGVuZGlzc2UgcG90ZW50aSBpbiBlbGVpZmVuZCBxdWFtIGEgb2RpbyIsImFtb3VudCI6IjI5IiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6Ly9kdW1teWltYWdlLmNvbS8zMDB4NDAwLmpwZy9mZjQ0NDQvZmZmZmZmIn0seyJhY3RpdmUiOiIiLCJza3UiOiJkZWY1ZWRhMC0zODk3LTRkOGItOTVhMi0xMjUyODI4MGJmNzMiLCJ0aXRsZSI6IlRpbGxtYW4gR3JvdXAiLCJkZXNjcmlwdGlvbiI6InZlbGl0IGV1IGVzdCBjb25ndWUgZWxlbWVudHVtIGluIGhhYyBoYWJpdGFzc2UgcGxhdGVhIGRpY3R1bXN0IG1vcmJpIHZlc3RpYnVsdW0gdmVsaXQiLCJhbW91bnQiOiIxIiwiY3VycmVuY3kiOiJ1c2QiLCJpbWFnZSI6Imh0dHA6Ly9kdW1teWltYWdlLmNvbS8zMDB4NDAwLmJtcC9jYzAwMDAvZmZmZmZmIn0seyJhY3RpdmUiOiJmYWxzZSIsInNrdSI6Ijc3MTcwYjE4LTJjNTEtNDkwOC1hNmYwLTZjNGJjZGZiM2ZkMiIsInRpdGxlIjoiS2VybHVrZSBMTEMiLCJkZXNjcmlwdGlvbiI6ImN1cmFlIGR1aXMgZmF1Y2lidXMgYWNjdW1zYW4gb2RpbyBjdXJhYml0dXIgY29udmFsbGlzIGR1aXMgY29uc2VxdWF0IGR1aSBuZWMgbmlzaSB2b2x1dHBhdCBlbGVpZmVuZCBkb25lYyB1dCBkb2xvciBtb3JiaSB2ZWwgbGVjdHVzIiwiYW1vdW50IjoiMjIiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAucG5nL2RkZGRkZC8wMDAwMDAifSx7ImFjdGl2ZSI6InRydWUiLCJza3UiOiI2MjNkZTU4NS1iYjFiLTQ0OTAtOTRkYi00NTMxZjUxMDgzOTciLCJ0aXRsZSI6IldvbGZmLUJlY2h0ZWxhciIsImRlc2NyaXB0aW9uIjoiZXQgdGVtcHVzIHNlbXBlciBlc3QgcXVhbSBwaGFyZXRyYSBtYWduYSBhYyBjb25zZXF1YXQgbWV0dXMgc2FwaWVuIiwiYW1vdW50IjoiNDIiLCJjdXJyZW5jeSI6ImV1ciIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAucG5nL2NjMDAwMC9mZmZmZmYifSx7ImFjdGl2ZSI6ImZhbHNlIiwic2t1IjoiNDRhMGJiZjEtOTAxMC00ZjdmLWE4OTctY2VjYjcwYzMwNTlkIiwidGl0bGUiOiJIaWxscyBhbmQgU29ucyIsImRlc2NyaXB0aW9uIjoiYW1ldCBlcmF0IG51bGxhIHRlbXB1cyB2aXZhbXVzIGluIGZlbGlzIGV1IHNhcGllbiBjdXJzdXMgdmVzdGlidWx1bSIsImFtb3VudCI6IjQyIiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6Ly9kdW1teWltYWdlLmNvbS8zMDB4NDAwLmJtcC81ZmEyZGQvZmZmZmZmIn0seyJhY3RpdmUiOiIiLCJza3UiOiIxZTQ3NDBmNS1kMGZkLTRmZDctOWM5MS04MzM5YWRlNzU4MzUiLCJ0aXRsZSI6Ikdsb3ZlciBhbmQgU29ucyIsImRlc2NyaXB0aW9uIjoidmVoaWN1bGEgY29uZGltZW50dW0gY3VyYWJpdHVyIGluIGxpYmVybyB1dCBtYXNzYSB2b2x1dHBhdCBjb252YWxsaXMgbW9yYmkgb2RpbyIsImFtb3VudCI6IjkyIiwiY3VycmVuY3kiOiJldXIiLCJpbWFnZSI6Imh0dHA6Ly9kdW1teWltYWdlLmNvbS8zMDB4NDAwLmpwZy9mZjQ0NDQvZmZmZmZmIn0seyJhY3RpdmUiOiJ0cnVlIiwic2t1IjoiM2EzYzVjOTUtYzk2MS00MTgxLWJlOTctZDkwYzFmNWU1NTBmIiwidGl0bGUiOiJXeW1hbi1EaWV0cmljaCIsImRlc2NyaXB0aW9uIjoiaWQgY29uc2VxdWF0IGluIGNvbnNlcXVhdCB1dCBudWxsYSBzZWQgYWNjdW1zYW4gZmVsaXMgdXQgYXQgZG9sb3IgcXVpcyBvZGlvIGNvbnNlcXVhdCB2YXJpdXMgaW50ZWdlciBhYyBsZW8gcGVsbGVudGVzcXVlIiwiYW1vdW50IjoiODYiLCJjdXJyZW5jeSI6InVzZCIsImltYWdlIjoiaHR0cDovL2R1bW15aW1hZ2UuY29tLzMwMHg0MDAucG5nLzVmYTJkZC9mZmZmZmYifV0sImNhdGVnb3JpZXMiOlt7InNrdSI6IjhJTVpOQlM3V1NLMVVEIiwidGl0bGUiOiJsZWd1bWVzIn0seyJza3UiOiJUWlpBSzVRUzlUVkdXQyIsInRpdGxlIjoiZ2F0ZWF1IiwiY2hpbGRyZW4iOlt7InNrdSI6IkE2UzQ5WjFHSUtBOTI4IiwidGl0bGUiOiJjb29raWVzcyIsInBhcmVudCI6IlRaWkFLNVFTOVRWR1dDIn1dfSx7InNrdSI6IkMxNkJDVjkxTFlWNkpOIiwidGl0bGUiOiJib25ib24iLCJjaGlsZHJlbiI6W3sic2t1IjoiMVZRRlRFUFk5RjJIN0oiLCJ0aXRsZSI6InNtYXJ0aWVzIiwicGFyZW50IjoiQzE2QkNWOTFMWVY2Sk4ifV19XSwidGFncyI6W119LCJmciI6eyJsYWJlbCI6IkZyYW5jYWlzIiwid2Vic2l0ZSI6eyJ0aXRsZSI6IkluZXNjb2luIiwiaWNvbiI6IiIsInRpbWV6b25lIjoiIiwiYWN0aXZlIjp0cnVlLCJhbmFseXRpY3MiOnsiYWN0aXZlIjpmYWxzZSwiY29kZSI6IiJ9LCJtZXRhIjpbeyJ0eXBlIjoibmFtZSIsIm5hbWUiOiJkZXNjcmlwdGlvbiIsImNvbnRlbnQiOiJJbmVzY29pbiwgRG9tYWluLCBXZWJzaXRlIGFuZCBNZXNzZW5nZXIgaW50byBCbG9ja2NoYWluIn0seyJ0eXBlIjoibmFtZSIsIm5hbWUiOiJrZXl3b3JkcyIsImNvbnRlbnQiOiJJbmVzY29pbiwgYmxvY2tjaGFpbiwgZG9tYWluLCBjcnlwdG8sIHdlYnNpdGUsIG1lc3NlbmdlciJ9LHsidHlwZSI6Im5hbWUiLCJuYW1lIjoiYXV0aG9yIiwiY29udGVudCI6IkluZXNjb2luIE5ldHdvcmsifV19LCJjb21wYW55Ijp7Im5hbWUiOiJJbmVzY29pbiIsInNsb2dhbiI6IiIsImRlc2NyaXB0aW9uIjoiIiwibG9nbyI6IiIsInllYXIiOjIwMTksInRlcm1zT2ZTZXJ2aWNlIjoiIiwidGVybXNPZlNhbGVzIjoiIiwicHJpdmFjeVBvbGljeSI6IiIsImZhcSI6IiJ9LCJsb2NhdGlvbiI6W3siYWRkcmVzcyI6IiIsInJlZ2lvbiI6IiIsInppcGNvZGUiOiIiLCJjaXR5IjoiIiwiY291bnRyeSI6IiIsImxvbmdpdHVkZSI6IiIsImxhdGl0dWRlIjoiIiwicGhvbmUiOiIiLCJlbWFpbCI6IiJ9XSwibmV0d29yayI6eyJnaXRodWIiOiIiLCJmYWNlYm9vayI6IiIsInR3aXR0ZXIiOiIiLCJsaW5rZWRpbiI6IiIsInlvdXR1YmUiOiIiLCJpbnN0YWdyYW0iOiIiLCJ3ZWNoYXQiOiIiLCJ3ZWlibyI6IiIsImRvdXlpbiI6IiIsInZrb250YWt0ZSI6IiIsIm9kbm9LbGFzc25pa2kiOiIiLCJ0ZWxlZ3JhbSI6IiIsIndoYXRzYXBwIjoiIn0sInBhZ2VzIjpbeyJtZW51VGl0bGUiOiIiLCJzaG93bkluTWVudSI6dHJ1ZSwiaXNMaW5rIjpmYWxzZSwibGlua1VybCI6IiIsImRpdklkIjoiIiwibGFiZWwiOiIiLCJib2R5IjoiPGRpdiBjbGFzcz1cImNvbnRhaW5lciBoLTEwMFwiPlxuPGRpdiBjbGFzcz1cInJvdyBoLTEwMCBhbGlnbi1pdGVtcy1jZW50ZXIganVzdGlmeS1jb250ZW50LWNlbnRlciB0ZXh0LWNlbnRlclwiPlxuPGRpdiBjbGFzcz1cImNvbC1sZy0xMCBhbGlnbi1zZWxmLWVuZFwiPlxuPGgxIGNsYXNzPVwidGV4dC11cHBlcmNhc2UgdGV4dC13aGl0ZSBmb250LXdlaWdodC1ib2xkXCI+Q3ImZWFjdXRlO2V6IHZvdHJlIG5vbSBkZSBkb21haW5lIGV0IHZvdHJlIHNpdGUgd2ViIGRhbnMgdW5lIGJsb2NrY2hhaW4sIGF2ZWMgdW4gbWVzc2FnZXIgY3J5cHQmZWFjdXRlOzwvaDE+XG48aHIgY2xhc3M9XCJkaXZpZGVyIG15LTRcIiAvPjwvZGl2PlxuPGRpdiBjbGFzcz1cImNvbC1sZy04IGFsaWduLXNlbGYtYmFzZWxpbmVcIj5cbjxwIGNsYXNzPVwidGV4dC13aGl0ZS03NSBmb250LXdlaWdodC1saWdodCBtYi01XCI+VGVjaG5vbG9naWUgZGUgYmxvY2tjaGFpbiBkJmVhY3V0ZTtjZW50cmFsaXMmZWFjdXRlO2U8L3A+XG48YSBjbGFzcz1cImJ0biBidG4tbGlnaHQgYnRuLXhsIGpzLXNjcm9sbC10cmlnZ2VyXCIgdGl0bGU9XCJUcmFuc2FjdGlvbiBldCBleHBsb3JhdGV1ciBkZSBkb21haW5lXCIgaHJlZj1cImh0dHBzOi8vZXhwbG9yZXIuaW5lc2NvaW4ub3JnXCI+VHJhbnNhY3Rpb24gZXQgZXhwbG9yYXRldXIgZGUgZG9tYWluZTwvYT4gPGEgY2xhc3M9XCJidG4gYnRuLWxpZ2h0IGJ0bi14bCBqcy1zY3JvbGwtdHJpZ2dlclwiIHRpdGxlPVwiUG9ydGVmZXVpbGxlIGhvcnMgbGlnbmUsIHNpdGUgV2ViIENNUyBldCBNZXNzZW5nZXJcIiBocmVmPVwiaHR0cHM6Ly93YWxsZXQuaW5lc2NvaW4ub3JnXCI+UG9ydGVmZXVpbGxlIGhvcnMgbGlnbmUsIHNpdGUgV2ViIENNUyBldCBNZXNzZW5nZXI8L2E+PC9kaXY+XG48L2Rpdj5cbjwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6Imh0dHBzOi8vcmVzLmNsb3VkaW5hcnkuY29tL2R6ZmJ4bHR6eC9pbWFnZS91cGxvYWQvdjE1NzE2NTkyMzEvSW5lc2NvaW4vYmctbWFzdGhlYWRfeGNqbTFyLmpwZyJ9LHsibWVudVRpdGxlIjoiVGVjaG5vbG9naWVzIiwic2hvd25Jbk1lbnUiOnRydWUsImlzTGluayI6ZmFsc2UsImxpbmtVcmwiOiIiLCJkaXZJZCI6InRlY2hub2xvZ2llcyIsImxhYmVsIjoiIiwiYm9keSI6IjxkaXYgY2xhc3M9XCJjb250YWluZXJcIj5cbjxoMiBjbGFzcz1cInRleHQtY2VudGVyIG10LTBcIj5Ob3MgdGVjaG5vbG9naWVzPC9oMj5cbjxociBjbGFzcz1cImRpdmlkZXIgbXktNFwiIC8+XG48ZGl2IGNsYXNzPVwicm93XCI+XG48ZGl2IGNsYXNzPVwiY29sLWxnLTMgY29sLW1kLTYgdGV4dC1jZW50ZXJcIj5cbjxkaXYgY2xhc3M9XCJtdC01XCI+PGVtIGNsYXNzPVwiZmFzIGZhLTR4IGZhLWhlYXJ0IHRleHQtcHJpbWFyeSBtYi00XCI+Jm5ic3A7PC9lbT5cbjxoMyBjbGFzcz1cImg0IG1iLTJcIj5SZWFjdFBIUDwvaDM+XG48cCBjbGFzcz1cInRleHQtbXV0ZWQgbWItMFwiPlJlYWN0UEhQIGVzdCB1bmUgYmlibGlvdGgmZWdyYXZlO3F1ZSBkZSBiYXMgbml2ZWF1IHBvdXIgbGEgcHJvZ3JhbW1hdGlvbiAmZWFjdXRlO3YmZWFjdXRlO25lbWVudGllbGxlIGVuIFBIUC4gJkFncmF2ZTsgc2EgYmFzZSwgaWwgeSBhIHVuZSBib3VjbGUgZCcmZWFjdXRlO3YmZWFjdXRlO25lbWVudCwgYXUtZGVzc3VzIGRlIGxhcXVlbGxlIGlsIGZvdXJuaXQgZGVzIHV0aWxpdGFpcmVzIGRlIGJhcyBuaXZlYXUuPC9wPlxuPC9kaXY+XG48L2Rpdj5cbjxkaXYgY2xhc3M9XCJjb2wtbGctMyBjb2wtbWQtNiB0ZXh0LWNlbnRlclwiPlxuPGRpdiBjbGFzcz1cIm10LTVcIj48ZW0gY2xhc3M9XCJmYXMgZmEtNHggZmEtZGF0YWJhc2UgdGV4dC1wcmltYXJ5IG1iLTRcIj4mbmJzcDs8L2VtPlxuPGgzIGNsYXNzPVwiaDQgbWItMlwiPkVsYXN0aWNzZWFyY2ggRGF0YWJhc2U8L2gzPlxuPHAgY2xhc3M9XCJ0ZXh0LW11dGVkIG1iLTBcIj5FbGFzdGljc2VhcmNoIGVzdCB1bmUgYmFzZSBkZSBkb25uJmVhY3V0ZTtlcyBxdWkgc3RvY2tlLCByJmVhY3V0ZTtjdXAmZWdyYXZlO3JlIGV0IGcmZWdyYXZlO3JlIGRlcyBkb25uJmVhY3V0ZTtlcyBvcmllbnQmZWFjdXRlO2VzIGRvY3VtZW50IGV0IHN0cnVjdHVyJmVhY3V0ZTtlcyBwYXIgc2lpLjwvcD5cbjwvZGl2PlxuPC9kaXY+XG48ZGl2IGNsYXNzPVwiY29sLWxnLTMgY29sLW1kLTYgdGV4dC1jZW50ZXJcIj5cbjxkaXYgY2xhc3M9XCJtdC01XCI+PGVtIGNsYXNzPVwiZmFzIGZhLTR4IGZhLWxvY2sgdGV4dC1wcmltYXJ5IG1iLTRcIj4mbmJzcDs8L2VtPlxuPGgzIGNsYXNzPVwiaDQgbWItMlwiPkJsb2NrY2hhaW48L2gzPlxuPHAgY2xhc3M9XCJ0ZXh0LW11dGVkIG1iLTBcIj5VbmUgYmxvY2tjaGFpbiwgJmFncmF2ZTsgbCdvcmlnaW5lIHVuZSBjaGEmaWNpcmM7bmUgZGUgYmxvY3MsIGVzdCB1bmUgbGlzdGUgY3JvaXNzYW50ZSBkJ2VucmVnaXN0cmVtZW50cywgYXBwZWwmZWFjdXRlO3MgYmxvY3MsIGxpJmVhY3V0ZTtzICZhZ3JhdmU7IGwnYWlkZSBkZSBsYSBjcnlwdG9ncmFwaGllLjwvcD5cbjwvZGl2PlxuPC9kaXY+XG48ZGl2IGNsYXNzPVwiY29sLWxnLTMgY29sLW1kLTYgdGV4dC1jZW50ZXJcIj5cbjxkaXYgY2xhc3M9XCJtdC01XCI+PGVtIGNsYXNzPVwiZmFzIGZhLTR4IGZhLWdsb2JlIHRleHQtcHJpbWFyeSBtYi00XCI+Jm5ic3A7PC9lbT5cbjxoMyBjbGFzcz1cImg0IG1iLTJcIj5QMlAgTmV0d290azwvaDM+XG48cCBjbGFzcz1cInRleHQtbXV0ZWQgbWItMFwiPlNpZ25pZmllICZsYXF1bztQZWVyIHRvIFBlZXImcmFxdW87LiBEYW5zIHVuIHImZWFjdXRlO3NlYXUgUDJQLCBsZXMgXCJob21vbG9ndWVzXCIgc29udCBkZXMgc3lzdCZlZ3JhdmU7bWVzIGluZm9ybWF0aXF1ZXMgcXVpIHNvbnQgY29ubmVjdCZlYWN1dGU7cyBsZXMgdW5zIGF1eCBhdXRyZXMgdmlhIEludGVybmV0LjwvcD5cbjwvZGl2PlxuPC9kaXY+XG48L2Rpdj5cbjwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiIiwic2hvd25Jbk1lbnUiOmZhbHNlLCJpc0xpbmsiOmZhbHNlLCJsaW5rVXJsIjoiIiwiZGl2SWQiOiIiLCJsYWJlbCI6IiIsImJvZHkiOiI8ZGl2IGNsYXNzPVwiY29udGFpbmVyIHRleHQtY2VudGVyXCI+PGltZyBjbGFzcz1cImltZy1mbHVpZFwiIHNyYz1cImh0dHBzOi8vcmVzLmNsb3VkaW5hcnkuY29tL2R6ZmJ4bHR6eC9pbWFnZS91cGxvYWQvdjE1NzE2NTkyMzEvSW5lc2NvaW4vaW5lc2NvaW4tYmxvY2tjaGFpbi1uZXR3b3JrX2JqcWZtNi5qcGdcIiAvPjwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiIiwic2hvd25Jbk1lbnUiOmZhbHNlLCJpc0xpbmsiOmZhbHNlLCJsaW5rVXJsIjoiIiwiZGl2SWQiOiIiLCJsYWJlbCI6IiIsImJvZHkiOiI8ZGl2IGNsYXNzPVwiY29udGFpbmVyIHRleHQtY2VudGVyXCI+PGltZyBjbGFzcz1cImltZy1mbHVpZFwiIHNyYz1cImh0dHBzOi8vcmVzLmNsb3VkaW5hcnkuY29tL2R6ZmJ4bHR6eC9pbWFnZS91cGxvYWQvdjE1NzE2NTkyMzEvSW5lc2NvaW4vaW5lc2NvaW4tYmxvY2tjaGFpbi1ibG9ja19nanN2cmYuanBnXCIgLz48L2Rpdj4iLCJiYWNrZ3JvdW5kT3BhY2l0eSI6MTAwLCJoZWlnaHQiOiIiLCJiYWNrZ3JvdW5kSW1hZ2UiOiIifSx7Im1lbnVUaXRsZSI6IiIsInNob3duSW5NZW51IjpmYWxzZSwiaXNMaW5rIjpmYWxzZSwibGlua1VybCI6IiIsImRpdklkIjoiIiwibGFiZWwiOiIiLCJib2R5IjoiPGRpdiBjbGFzcz1cImNvbnRhaW5lciB0ZXh0LWNlbnRlclwiPjxpbWcgY2xhc3M9XCJpbWctZmx1aWRcIiBzcmM9XCJodHRwczovL3Jlcy5jbG91ZGluYXJ5LmNvbS9kemZieGx0engvaW1hZ2UvdXBsb2FkL3YxNTcxNjU5MjMxL0luZXNjb2luL2luZXNjb2luLWJsb2NrY2hhaW4tdHJhbnNhY3Rpb24tY29uc2Vuc3VzX3l5ZnltOC5qcGdcIiAvPjwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiIiwic2hvd25Jbk1lbnUiOmZhbHNlLCJpc0xpbmsiOmZhbHNlLCJsaW5rVXJsIjoiIiwiZGl2SWQiOiIiLCJsYWJlbCI6IiIsImJvZHkiOiI8ZGl2IGNsYXNzPVwiY29udGFpbmVyIHRleHQtY2VudGVyXCI+PGltZyBjbGFzcz1cImltZy1mbHVpZFwiIHNyYz1cImh0dHBzOi8vcmVzLmNsb3VkaW5hcnkuY29tL2R6ZmJ4bHR6eC9pbWFnZS91cGxvYWQvdjE1NzE2NTkyMzAvSW5lc2NvaW4vaW5lc2NvaW4tYmxvY2tjaGFpbi1iYW5rLWNvbnNlbnN1c19idHk5dWQuanBnXCIgLz48L2Rpdj4iLCJiYWNrZ3JvdW5kT3BhY2l0eSI6MTAwLCJoZWlnaHQiOiIiLCJiYWNrZ3JvdW5kSW1hZ2UiOiIifSx7Im1lbnVUaXRsZSI6IiIsInNob3duSW5NZW51IjpmYWxzZSwiaXNMaW5rIjpmYWxzZSwibGlua1VybCI6IiIsImRpdklkIjoiIiwibGFiZWwiOiIiLCJib2R5IjoiPGRpdiBjbGFzcz1cImNvbnRhaW5lciB0ZXh0LWNlbnRlclwiPjxpbWcgY2xhc3M9XCJpbWctZmx1aWRcIiBzcmM9XCJodHRwczovL3Jlcy5jbG91ZGluYXJ5LmNvbS9kemZieGx0engvaW1hZ2UvdXBsb2FkL3YxNTcxNjU5MjMwL0luZXNjb2luL2luZXNjb2luLWJsb2NrY2hhaW4tcGVlcnMtY29uc2Vuc3VzX2NkeTRubi5qcGdcIiAvPjwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiRXF1aXBlIiwic2hvd25Jbk1lbnUiOnRydWUsImlzTGluayI6ZmFsc2UsImxpbmtVcmwiOiIiLCJkaXZJZCI6ImVxdWlwZSIsImxhYmVsIjoiIiwiYm9keSI6IjxkaXYgY2xhc3M9XCJjb250YWluZXIgbXQtNFwiPlxuPGgxIGNsYXNzPVwibWItNSB0ZXh0LWNlbnRlclwiPlRlYW08L2gxPlxuPGRpdiBjbGFzcz1cInJvdyBqdXN0aWZ5LWNvbnRlbnQtbWQtY2VudGVyXCI+XG48ZGl2IGNsYXNzPVwiY29sLXhsLTMgY29sLW1kLTYgbWItNFwiPlxuPGRpdiBjbGFzcz1cImNhcmQgYm9yZGVyLTAgc2hhZG93XCI+PGltZyBjbGFzcz1cImNhcmQtaW1nLXRvcFwiIHNyYz1cImh0dHBzOi8vcmVzLmNsb3VkaW5hcnkuY29tL2R6ZmJ4bHR6eC9pbWFnZS91cGxvYWQvdjE1NzE2NTkyMzEvSW5lc2NvaW4vaW5lc2NvaW4tbW9vbl9oMHE4eWguanBnXCIgYWx0PVwiTW91bmlyIFInUXVpYmFcIiAvPlxuPGRpdiBjbGFzcz1cImNhcmQtYm9keSB0ZXh0LWNlbnRlclwiPlxuPGg1IGNsYXNzPVwiY2FyZC10aXRsZSBtYi0wXCI+TW91bmlyIFInUXVpYmE8L2g1PlxuPGRpdiBjbGFzcz1cImNhcmQtdGV4dCB0ZXh0LWJsYWNrLTUwXCI+Q3JlYXRvcjwvZGl2PlxuPGRpdiBjbGFzcz1cImNhcmQtdGV4dCB0ZXh0LWJsYWNrLTUwXCI+PGEgaHJlZj1cImh0dHBzOi8vbGlua2VkaW4uY29tL2luL21vdW5pci1yLXF1aWJhLTE0YWE4NGJhL1wiPjxlbSBjbGFzcz1cImZhYiBmYS0yeCBmYS1saW5rZWRpbiBtYi00XCI+Jm5ic3A7PC9lbT48L2E+PC9kaXY+XG48L2Rpdj5cbjwvZGl2PlxuPC9kaXY+XG48ZGl2IGNsYXNzPVwiY29sLXhsLTMgY29sLW1kLTYgbWItNFwiPlxuPGRpdiBjbGFzcz1cImNhcmQgYm9yZGVyLTAgc2hhZG93XCI+PGltZyBjbGFzcz1cImNhcmQtaW1nLXRvcFwiIHNyYz1cImh0dHBzOi8vcmVzLmNsb3VkaW5hcnkuY29tL2R6ZmJ4bHR6eC9pbWFnZS91cGxvYWQvdjE1NzI1Mzk2NjQvSW5lc2NvaW4vZmxvcmVudC41YTQ3MDljNV9tbXN3d3YuanBnXCIgYWx0PVwiRmxvcmVudCBEYXF1ZXRcIiAvPlxuPGRpdiBjbGFzcz1cImNhcmQtYm9keSB0ZXh0LWNlbnRlclwiPlxuPGg1IGNsYXNzPVwiY2FyZC10aXRsZSBtYi0wXCI+RmxvcmVudCBEYXF1ZXQ8L2g1PlxuPGRpdiBjbGFzcz1cImNhcmQtdGV4dCB0ZXh0LWJsYWNrLTUwXCI+RXhwZXJ0cyBlbiBDeWJlcnMmZWFjdXRlO2N1cml0JmVhY3V0ZTsgZXQgQmxvY2tjaGFpbiwgQ29mb25kYXRldXIgY2hleiA8YSBocmVmPVwiaHR0cHM6Ly9kaXNjb2luLmlvL1wiPmh0dHBzOi8vZGlzY29pbi5pby88L2E+PC9kaXY+XG48ZGl2IGNsYXNzPVwiY2FyZC10ZXh0IHRleHQtYmxhY2stNTBcIj48YSBocmVmPVwiaHR0cHM6Ly93d3cubGlua2VkaW4uY29tL2luL2Zsb3JlbnRkYXF1ZXQvXCI+PGVtIGNsYXNzPVwiZmFiIGZhLTJ4IGZhLWxpbmtlZGluIG1iLTRcIj4mbmJzcDs8L2VtPjwvYT48L2Rpdj5cbjwvZGl2PlxuPC9kaXY+XG48L2Rpdj5cbjxkaXYgY2xhc3M9XCJjb2wteGwtMyBjb2wtbWQtNiBtYi00XCI+XG48ZGl2IGNsYXNzPVwiY2FyZCBib3JkZXItMCBzaGFkb3dcIj48aW1nIGNsYXNzPVwiY2FyZC1pbWctdG9wXCIgc3JjPVwiaHR0cHM6Ly9yZXMuY2xvdWRpbmFyeS5jb20vZHpmYnhsdHp4L2ltYWdlL3VwbG9hZC92MTU3MjU1NjY2NC9JbmVzY29pbi9pbmVzY29pbl9mcmFuY2tfc2FsaGkuanBnXCIgYWx0PVwiRnJhbmNrIFNhbGhpXCIgLz5cbjxkaXYgY2xhc3M9XCJjYXJkLWJvZHkgdGV4dC1jZW50ZXJcIj5cbjxoNSBjbGFzcz1cImNhcmQtdGl0bGUgbWItMFwiPkZyYW5jayBTYWxoaTwvaDU+XG48ZGl2IGNsYXNzPVwiY2FyZC10ZXh0IHRleHQtYmxhY2stNTBcIj5JQ08gTWFuYWdlcjwvZGl2PlxuPGRpdiBjbGFzcz1cImNhcmQtdGV4dCB0ZXh0LWJsYWNrLTUwXCI+PGEgaHJlZj1cImh0dHBzOi8vd3d3LmxpbmtlZGluLmNvbS9pbi9mcmFuY2stc2FsaGktNTA4NzFiMTYzL1wiPjxlbSBjbGFzcz1cImZhYiBmYS0yeCBmYS1saW5rZWRpbiBtYi00XCI+Jm5ic3A7PC9lbT48L2E+PC9kaXY+XG48L2Rpdj5cbjwvZGl2PlxuPC9kaXY+XG48L2Rpdj5cbjwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiQ29udGFjdCIsInNob3duSW5NZW51Ijp0cnVlLCJpc0xpbmsiOmZhbHNlLCJsaW5rVXJsIjoiIiwiZGl2SWQiOiJjb250YWN0IiwibGFiZWwiOiIiLCJib2R5IjoiPGRpdiBjbGFzcz1cImNvbnRhaW5lclwiPlxuPGRpdiBjbGFzcz1cInJvdyBqdXN0aWZ5LWNvbnRlbnQtY2VudGVyXCI+XG48ZGl2IGNsYXNzPVwiY29sLWxnLTggdGV4dC1jZW50ZXJcIj5cbjxoMiBjbGFzcz1cIm10LTBcIj5FbnRyb25zIGVuIGNvbnRhY3QhPC9oMj5cbjxociBjbGFzcz1cImRpdmlkZXIgbXktNFwiIC8+XG48cCBjbGFzcz1cInRleHQtbXV0ZWQgbWItNVwiPlZvdXMgcG91dmV6IHNvdXRlbmlyIGNlIHByb2pldDwvcD5cbjwvZGl2PlxuPC9kaXY+XG48ZGl2IGNsYXNzPVwicm93XCI+XG48ZGl2IGNsYXNzPVwiY29sLWxnLTEyIG1yLWF1dG8gdGV4dC1jZW50ZXJcIj48YSBjbGFzcz1cImJsb2NrXCIgaHJlZj1cImh0dHBzOi8vZ2l0aHViLmNvbS9pbmVzY29pblwiPjxlbSBjbGFzcz1cImZhYiBmYS1naXRodWIgZmEtM3ggbWItMyB0ZXh0LW11dGVkXCI+Jm5ic3A7PC9lbT48L2E+IDxhIGNsYXNzPVwiYmxvY2tcIiBocmVmPVwiaHR0cHM6Ly90Lm1lL2pvaW5jaGF0L0lURDBFQk1jU2JiU0FMZ1dnUlJsV3dcIj48ZW0gY2xhc3M9XCJmYWIgZmEtdGVsZWdyYW0gZmEtM3ggbWItMyB0ZXh0LW11dGVkXCI+Jm5ic3A7PC9lbT48L2E+PC9kaXY+XG48L2Rpdj5cbjwvZGl2PiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiRXhwbG9yYXRldXIiLCJzaG93bkluTWVudSI6dHJ1ZSwiaXNMaW5rIjp0cnVlLCJsaW5rVXJsIjoiaHR0cHM6Ly9leHBsb3Jlci5pbmVzY29pbi5vcmciLCJkaXZJZCI6IiIsImxhYmVsIjoiIiwiYm9keSI6IiIsImJhY2tncm91bmRPcGFjaXR5IjoxMDAsImhlaWdodCI6IiIsImJhY2tncm91bmRJbWFnZSI6IiJ9LHsibWVudVRpdGxlIjoiUG9ydGVmZXVpbGxlIiwic2hvd25Jbk1lbnUiOnRydWUsImlzTGluayI6dHJ1ZSwibGlua1VybCI6Imh0dHBzOi8vd2FsbGV0LmluZXNjb2luLm9yZyIsImRpdklkIjoiIiwibGFiZWwiOiIiLCJib2R5IjoiIiwiYmFja2dyb3VuZE9wYWNpdHkiOjEwMCwiaGVpZ2h0IjoiIiwiYmFja2dyb3VuZEltYWdlIjoiIn1dLCJ0aGVtZSI6eyJqcyI6eyJ2YWx1ZSI6IihmdW5jdGlvbigkKSB7XG4gIFwidXNlIHN0cmljdFwiOyAvLyBTdGFydCBvZiB1c2Ugc3RyaWN0XG5cbiAgLy8gU21vb3RoIHNjcm9sbGluZyB1c2luZyBqUXVlcnkgZWFzaW5nXG4gICQoJ2EuanMtc2Nyb2xsLXRyaWdnZXJbaHJlZio9XCIjXCJdOm5vdChbaHJlZj1cIiNcIl0pJykuY2xpY2soZnVuY3Rpb24oKSB7XG4gICAgaWYgKGxvY2F0aW9uLnBhdGhuYW1lLnJlcGxhY2UoL15cXC8vLCAnJykgPT0gdGhpcy5wYXRobmFtZS5yZXBsYWNlKC9eXFwvLywgJycpICYmIGxvY2F0aW9uLmhvc3RuYW1lID09IHRoaXMuaG9zdG5hbWUpIHtcbiAgICAgIHZhciB0YXJnZXQgPSAkKHRoaXMuaGFzaCk7XG4gICAgICB0YXJnZXQgPSB0YXJnZXQubGVuZ3RoID8gdGFyZ2V0IDogJCgnW25hbWU9JyArIHRoaXMuaGFzaC5zbGljZSgxKSArICddJyk7XG4gICAgICBpZiAodGFyZ2V0Lmxlbmd0aCkge1xuICAgICAgICAkKCdodG1sLCBib2R5JykuYW5pbWF0ZSh7XG4gICAgICAgICAgc2Nyb2xsVG9wOiAodGFyZ2V0Lm9mZnNldCgpLnRvcCAtIDcyKVxuICAgICAgICB9LCAxMDAwLCBcImVhc2VJbk91dEV4cG9cIik7XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgIH1cbiAgICB9XG4gIH0pO1xuXG4gIC8vIENsb3NlcyByZXNwb25zaXZlIG1lbnUgd2hlbiBhIHNjcm9sbCB0cmlnZ2VyIGxpbmsgaXMgY2xpY2tlZFxuICAkKCcuanMtc2Nyb2xsLXRyaWdnZXInKS5jbGljayhmdW5jdGlvbigpIHtcbiAgICAkKCcubmF2YmFyLWNvbGxhcHNlJykuY29sbGFwc2UoJ2hpZGUnKTtcbiAgfSk7XG5cbiAgLy8gQWN0aXZhdGUgc2Nyb2xsc3B5IHRvIGFkZCBhY3RpdmUgY2xhc3MgdG8gbmF2YmFyIGl0ZW1zIG9uIHNjcm9sbFxuICAkKCdib2R5Jykuc2Nyb2xsc3B5KHtcbiAgICB0YXJnZXQ6ICcjbWFpbk5hdicsXG4gICAgb2Zmc2V0OiA3NVxuICB9KTtcblxuICAvLyBDb2xsYXBzZSBOYXZiYXJcbiAgdmFyIG5hdmJhckNvbGxhcHNlID0gZnVuY3Rpb24oKSB7XG4gICAgaWYgKCQoXCIjbWFpbk5hdlwiKS5vZmZzZXQoKS50b3AgPiAxMDApIHtcbiAgICAgICQoXCIjbWFpbk5hdlwiKS5hZGRDbGFzcyhcIm5hdmJhci1zY3JvbGxlZFwiKTtcbiAgICB9IGVsc2Uge1xuICAgICAgJChcIiNtYWluTmF2XCIpLnJlbW92ZUNsYXNzKFwibmF2YmFyLXNjcm9sbGVkXCIpO1xuICAgIH1cbiAgfTtcbiAgLy8gQ29sbGFwc2Ugbm93IGlmIHBhZ2UgaXMgbm90IGF0IHRvcFxuICBuYXZiYXJDb2xsYXBzZSgpO1xuICAvLyBDb2xsYXBzZSB0aGUgbmF2YmFyIHdoZW4gcGFnZSBpcyBzY3JvbGxlZFxuICAkKHdpbmRvdykuc2Nyb2xsKG5hdmJhckNvbGxhcHNlKTtcbn0pKGpRdWVyeSk7XG4iLCJsaW5rcyI6W3sibGluayI6Ii8vY29kZS5qcXVlcnkuY29tL2pxdWVyeS0zLjMuMS5taW4uanMifSx7ImxpbmsiOiIvL3N0YWNrcGF0aC5ib290c3RyYXBjZG4uY29tL2Jvb3RzdHJhcC80LjMuMS9qcy9ib290c3RyYXAubWluLmpzIn0seyJsaW5rIjoiLy9jZG5qcy5jbG91ZGZsYXJlLmNvbS9hamF4L2xpYnMvcG9wcGVyLmpzLzEuMTQuNy91bWQvcG9wcGVyLm1pbi5qcyJ9LHsibGluayI6Ii8vY2RuanMuY2xvdWRmbGFyZS5jb20vYWpheC9saWJzL2pxdWVyeS1lYXNpbmcvMS40LjEvanF1ZXJ5LmVhc2luZy5taW4uanMifSx7ImxpbmsiOiIvL2NkbmpzLmNsb3VkZmxhcmUuY29tL2FqYXgvbGlicy9tYWduaWZpYy1wb3B1cC5qcy8xLjEuMC9qcXVlcnkubWFnbmlmaWMtcG9wdXAubWluLmpzIn1dfSwiY3NzIjp7InZhbHVlIjoiYm9keSxcbmh0bWwge1xuICB3aWR0aDogMTAwJTtcbiAgaGVpZ2h0OiAxMDAlO1xufVxuXG4udGV4dC13aGl0ZS03NSB7XG4gIGNvbG9yOiByZ2JhKDI1NSwgMjU1LCAyNTUsIDAuNzUpO1xufVxuXG5oci5kaXZpZGVyIHtcbiAgbWF4LXdpZHRoOiAzLjI1cmVtO1xuICBib3JkZXItd2lkdGg6IDAuMnJlbTtcbiAgYm9yZGVyLWNvbG9yOiAjOTkwMENDO1xufVxuXG5oci5saWdodCB7XG4gIGJvcmRlci1jb2xvcjogI2ZmZjtcbn1cblxuLmJ0biB7XG4gIGZvbnQtZmFtaWx5OiBcIk1lcnJpd2VhdGhlciBTYW5zXCIsIC1hcHBsZS1zeXN0ZW0sIEJsaW5rTWFjU3lzdGVtRm9udCwgXCJTZWdvZSBVSVwiLCBSb2JvdG8sIFwiSGVsdmV0aWNhIE5ldWVcIiwgQXJpYWwsIFwiTm90byBTYW5zXCIsIHNhbnMtc2VyaWYsIFwiQXBwbGUgQ29sb3IgRW1vamlcIiwgXCJTZWdvZSBVSSBFbW9qaVwiLCBcIlNlZ29lIFVJIFN5bWJvbFwiLCBcIk5vdG8gQ29sb3IgRW1vamlcIjtcbn1cblxuLmJ0bi14bCB7XG4gIHBhZGRpbmc6IDEuMjVyZW0gMi4yNXJlbTtcbiAgZm9udC1zaXplOiAwLjg1cmVtO1xuICBmb250LXdlaWdodDogNzAwO1xuICB0ZXh0LXRyYW5zZm9ybTogdXBwZXJjYXNlO1xuICBib3JkZXI6IG5vbmU7XG4gIGJvcmRlci1yYWRpdXM6IDEwcmVtO1xufVxuXG4ucGFnZS1zZWN0aW9uIHtcbiAgcGFkZGluZzogM3JlbSAwO1xufVxuXG4jbWFpbk5hdiB7XG4gIC13ZWJraXQtYm94LXNoYWRvdzogMCAwLjVyZW0gMXJlbSByZ2JhKDAsIDAsIDAsIDAuMTUpO1xuICBib3gtc2hhZG93OiAwIDAuNXJlbSAxcmVtIHJnYmEoMCwgMCwgMCwgMC4xNSk7XG4gIGJhY2tncm91bmQtY29sb3I6ICNmZmY7XG4gIC13ZWJraXQtdHJhbnNpdGlvbjogYmFja2dyb3VuZC1jb2xvciAwLjJzIGVhc2U7XG4gIHRyYW5zaXRpb246IGJhY2tncm91bmQtY29sb3IgMC4ycyBlYXNlO1xufVxuXG4jbWFpbk5hdiAubmF2YmFyLWJyYW5kIHtcbiAgZm9udC1mYW1pbHk6IFwiTWVycml3ZWF0aGVyIFNhbnNcIiwgLWFwcGxlLXN5c3RlbSwgQmxpbmtNYWNTeXN0ZW1Gb250LCBcIlNlZ29lIFVJXCIsIFJvYm90bywgXCJIZWx2ZXRpY2EgTmV1ZVwiLCBBcmlhbCwgXCJOb3RvIFNhbnNcIiwgc2Fucy1zZXJpZiwgXCJBcHBsZSBDb2xvciBFbW9qaVwiLCBcIlNlZ29lIFVJIEVtb2ppXCIsIFwiU2Vnb2UgVUkgU3ltYm9sXCIsIFwiTm90byBDb2xvciBFbW9qaVwiO1xuICBmb250LXdlaWdodDogNzAwO1xuICBjb2xvcjogIzIxMjUyOTtcbn1cblxuI21haW5OYXYgLm5hdmJhci1uYXYgLm5hdi1pdGVtIC5uYXYtbGluayB7XG4gIGNvbG9yOiAjNmM3NTdkO1xuICBmb250LWZhbWlseTogXCJNZXJyaXdlYXRoZXIgU2Fuc1wiLCAtYXBwbGUtc3lzdGVtLCBCbGlua01hY1N5c3RlbUZvbnQsIFwiU2Vnb2UgVUlcIiwgUm9ib3RvLCBcIkhlbHZldGljYSBOZXVlXCIsIEFyaWFsLCBcIk5vdG8gU2Fuc1wiLCBzYW5zLXNlcmlmLCBcIkFwcGxlIENvbG9yIEVtb2ppXCIsIFwiU2Vnb2UgVUkgRW1vamlcIiwgXCJTZWdvZSBVSSBTeW1ib2xcIiwgXCJOb3RvIENvbG9yIEVtb2ppXCI7XG4gIGZvbnQtd2VpZ2h0OiA3MDA7XG4gIGZvbnQtc2l6ZTogMC45cmVtO1xuICBwYWRkaW5nOiAwLjc1cmVtIDA7XG59XG5cbiNtYWluTmF2IC5uYXZiYXItbmF2IC5uYXYtaXRlbSAubmF2LWxpbms6aG92ZXIsICNtYWluTmF2IC5uYXZiYXItbmF2IC5uYXYtaXRlbSAubmF2LWxpbms6YWN0aXZlIHtcbiAgY29sb3I6ICM5OTAwQ0M7XG59XG5cbiNtYWluTmF2IC5uYXZiYXItbmF2IC5uYXYtaXRlbSAubmF2LWxpbmsuYWN0aXZlIHtcbiAgY29sb3I6ICM5OTAwQ0MgIWltcG9ydGFudDtcbn1cblxuQG1lZGlhIChtaW4td2lkdGg6IDk5MnB4KSB7XG4gICNtYWluTmF2IHtcbiAgICAtd2Via2l0LWJveC1zaGFkb3c6IG5vbmU7XG4gICAgYm94LXNoYWRvdzogbm9uZTtcbiAgICBiYWNrZ3JvdW5kLWNvbG9yOiB0cmFuc3BhcmVudDtcbiAgfVxuICAjbWFpbk5hdiAubmF2YmFyLWJyYW5kIHtcbiAgICBjb2xvcjogcmdiYSgyNTUsIDI1NSwgMjU1LCAwLjcpO1xuICB9XG4gICNtYWluTmF2IC5uYXZiYXItYnJhbmQ6aG92ZXIge1xuICAgIGNvbG9yOiAjZmZmO1xuICB9XG4gICNtYWluTmF2IC5uYXZiYXItbmF2IC5uYXYtaXRlbSAubmF2LWxpbmsge1xuICAgIGNvbG9yOiByZ2JhKDI1NSwgMjU1LCAyNTUsIDAuNyk7XG4gICAgcGFkZGluZzogMCAxcmVtO1xuICB9XG4gICNtYWluTmF2IC5uYXZiYXItbmF2IC5uYXYtaXRlbSAubmF2LWxpbms6aG92ZXIge1xuICAgIGNvbG9yOiAjZmZmO1xuICB9XG4gICNtYWluTmF2IC5uYXZiYXItbmF2IC5uYXYtaXRlbTpsYXN0LWNoaWxkIC5uYXYtbGluayB7XG4gICAgcGFkZGluZy1yaWdodDogMDtcbiAgfVxuICAjbWFpbk5hdi5uYXZiYXItc2Nyb2xsZWQge1xuICAgIC13ZWJraXQtYm94LXNoYWRvdzogMCAwLjVyZW0gMXJlbSByZ2JhKDAsIDAsIDAsIDAuMTUpO1xuICAgIGJveC1zaGFkb3c6IDAgMC41cmVtIDFyZW0gcmdiYSgwLCAwLCAwLCAwLjE1KTtcbiAgICBiYWNrZ3JvdW5kLWNvbG9yOiAjZmZmO1xuICB9XG4gICNtYWluTmF2Lm5hdmJhci1zY3JvbGxlZCAubmF2YmFyLWJyYW5kIHtcbiAgICBjb2xvcjogIzIxMjUyOTtcbiAgfVxuICAjbWFpbk5hdi5uYXZiYXItc2Nyb2xsZWQgLm5hdmJhci1icmFuZDpob3ZlciB7XG4gICAgY29sb3I6ICM5OTAwQ0M7XG4gIH1cbiAgI21haW5OYXYubmF2YmFyLXNjcm9sbGVkIC5uYXZiYXItbmF2IC5uYXYtaXRlbSAubmF2LWxpbmsge1xuICAgIGNvbG9yOiAjMjEyNTI5O1xuICB9XG4gICNtYWluTmF2Lm5hdmJhci1zY3JvbGxlZCAubmF2YmFyLW5hdiAubmF2LWl0ZW0gLm5hdi1saW5rOmhvdmVyIHtcbiAgICBjb2xvcjogIzk5MDBDQztcbiAgfVxufVxuXG5oZWFkZXIubWFzdGhlYWQge1xuICBwYWRkaW5nLXRvcDogMTByZW07XG4gIHBhZGRpbmctYm90dG9tOiBjYWxjKDEwcmVtIC0gNzJweCk7XG4gIGJhY2tncm91bmQ6IC13ZWJraXQtZ3JhZGllbnQobGluZWFyLCBsZWZ0IHRvcCwgbGVmdCBib3R0b20sIGZyb20ocmdiYSg5MiwgNzcsIDY2LCAwLjgpKSwgdG8ocmdiYSg5MiwgNzcsIDY2LCAwLjgpKSksIHVybChcIi4uL2ltZy9iZy1tYXN0aGVhZC5qcGdcIik7XG4gIGJhY2tncm91bmQ6IGxpbmVhci1ncmFkaWVudCh0byBib3R0b20sIHJnYmEoOTIsIDc3LCA2NiwgMC44KSAwJSwgcmdiYSg5MiwgNzcsIDY2LCAwLjgpIDEwMCUpLCB1cmwoXCIuLi9pbWcvYmctbWFzdGhlYWQuanBnXCIpO1xuICBiYWNrZ3JvdW5kLXBvc2l0aW9uOiBjZW50ZXI7XG4gIGJhY2tncm91bmQtcmVwZWF0OiBuby1yZXBlYXQ7XG4gIGJhY2tncm91bmQtYXR0YWNobWVudDogc2Nyb2xsO1xuICBiYWNrZ3JvdW5kLXNpemU6IGNvdmVyO1xufVxuXG5oZWFkZXIubWFzdGhlYWQgaDEge1xuICBmb250LXNpemU6IDIuMjVyZW07XG59XG5cbkBtZWRpYSAobWluLXdpZHRoOiA5OTJweCkge1xuICBoZWFkZXIubWFzdGhlYWQge1xuICAgIGhlaWdodDogMTAwdmg7XG4gICAgbWluLWhlaWdodDogNDByZW07XG4gICAgcGFkZGluZy10b3A6IDcycHg7XG4gICAgcGFkZGluZy1ib3R0b206IDA7XG4gIH1cbiAgaGVhZGVyLm1hc3RoZWFkIHAge1xuICAgIGZvbnQtc2l6ZTogMS4xNXJlbTtcbiAgfVxuICBoZWFkZXIubWFzdGhlYWQgaDEge1xuICAgIGZvbnQtc2l6ZTogM3JlbTtcbiAgfVxufVxuXG5AbWVkaWEgKG1pbi13aWR0aDogMTIwMHB4KSB7XG4gIGhlYWRlci5tYXN0aGVhZCBoMSB7XG4gICAgZm9udC1zaXplOiAzLjVyZW07XG4gIH1cbn1cblxuI3BvcnRmb2xpbyAuY29udGFpbmVyLWZsdWlkIHtcbiAgbWF4LXdpZHRoOiAxOTIwcHg7XG59XG5cbiNwb3J0Zm9saW8gLmNvbnRhaW5lci1mbHVpZCAucG9ydGZvbGlvLWJveCB7XG4gIHBvc2l0aW9uOiByZWxhdGl2ZTtcbiAgZGlzcGxheTogYmxvY2s7XG59XG5cbiNwb3J0Zm9saW8gLmNvbnRhaW5lci1mbHVpZCAucG9ydGZvbGlvLWJveCAucG9ydGZvbGlvLWJveC1jYXB0aW9uIHtcbiAgZGlzcGxheTogLXdlYmtpdC1ib3g7XG4gIGRpc3BsYXk6IC1tcy1mbGV4Ym94O1xuICBkaXNwbGF5OiBmbGV4O1xuICAtd2Via2l0LWJveC1hbGlnbjogY2VudGVyO1xuICAtbXMtZmxleC1hbGlnbjogY2VudGVyO1xuICBhbGlnbi1pdGVtczogY2VudGVyO1xuICAtd2Via2l0LWJveC1wYWNrOiBjZW50ZXI7XG4gIC1tcy1mbGV4LXBhY2s6IGNlbnRlcjtcbiAganVzdGlmeS1jb250ZW50OiBjZW50ZXI7XG4gIC13ZWJraXQtYm94LW9yaWVudDogdmVydGljYWw7XG4gIC13ZWJraXQtYm94LWRpcmVjdGlvbjogbm9ybWFsO1xuICAtbXMtZmxleC1kaXJlY3Rpb246IGNvbHVtbjtcbiAgZmxleC1kaXJlY3Rpb246IGNvbHVtbjtcbiAgd2lkdGg6IDEwMCU7XG4gIGhlaWdodDogMTAwJTtcbiAgcG9zaXRpb246IGFic29sdXRlO1xuICBib3R0b206IDA7XG4gIHRleHQtYWxpZ246IGNlbnRlcjtcbiAgb3BhY2l0eTogMDtcbiAgY29sb3I6ICNmZmY7XG4gIGJhY2tncm91bmQ6IHJnYmEoMTUzLCAwLCAyMDQsIDAuOSk7XG4gIC13ZWJraXQtdHJhbnNpdGlvbjogb3BhY2l0eSAwLjI1cyBlYXNlO1xuICB0cmFuc2l0aW9uOiBvcGFjaXR5IDAuMjVzIGVhc2U7XG4gIHRleHQtYWxpZ246IGNlbnRlcjtcbn1cblxuI3BvcnRmb2xpbyAuY29udGFpbmVyLWZsdWlkIC5wb3J0Zm9saW8tYm94IC5wb3J0Zm9saW8tYm94LWNhcHRpb24gLnByb2plY3QtY2F0ZWdvcnkge1xuICBmb250LWZhbWlseTogXCJNZXJyaXdlYXRoZXIgU2Fuc1wiLCAtYXBwbGUtc3lzdGVtLCBCbGlua01hY1N5c3RlbUZvbnQsIFwiU2Vnb2UgVUlcIiwgUm9ib3RvLCBcIkhlbHZldGljYSBOZXVlXCIsIEFyaWFsLCBcIk5vdG8gU2Fuc1wiLCBzYW5zLXNlcmlmLCBcIkFwcGxlIENvbG9yIEVtb2ppXCIsIFwiU2Vnb2UgVUkgRW1vamlcIiwgXCJTZWdvZSBVSSBTeW1ib2xcIiwgXCJOb3RvIENvbG9yIEVtb2ppXCI7XG4gIGZvbnQtc2l6ZTogMC44NXJlbTtcbiAgZm9udC13ZWlnaHQ6IDYwMDtcbiAgdGV4dC10cmFuc2Zvcm06IHVwcGVyY2FzZTtcbn1cblxuI3BvcnRmb2xpbyAuY29udGFpbmVyLWZsdWlkIC5wb3J0Zm9saW8tYm94IC5wb3J0Zm9saW8tYm94LWNhcHRpb24gLnByb2plY3QtbmFtZSB7XG4gIGZvbnQtc2l6ZTogMS4ycmVtO1xufVxuXG4jcG9ydGZvbGlvIC5jb250YWluZXItZmx1aWQgLnBvcnRmb2xpby1ib3g6aG92ZXIgLnBvcnRmb2xpby1ib3gtY2FwdGlvbiB7XG4gIG9wYWNpdHk6IDE7XG59IiwibGlua3MiOlt7ImxpbmsiOiIvL2NkbmpzLmNsb3VkZmxhcmUuY29tL2FqYXgvbGlicy9mb250LWF3ZXNvbWUvNS4xMC4yL2Nzcy9hbGwubWluLmNzcyJ9LHsibGluayI6Ii8vZm9udHMuZ29vZ2xlYXBpcy5jb20vY3NzP2ZhbWlseT1NZXJyaXdlYXRoZXIrU2Fuczo0MDAsNzAwIn0seyJsaW5rIjoiLy9mb250cy5nb29nbGVhcGlzLmNvbS9jc3M\/ZmFtaWx5PU1lcnJpd2VhdGhlcjo0MDAsMzAwLDMwMGl0YWxpYyw0MDBpdGFsaWMsNzAwLDcwMGl0YWxpYyJ9LHsibGluayI6Ii8vY2RuanMuY2xvdWRmbGFyZS5jb20vYWpheC9saWJzL21hZ25pZmljLXBvcHVwLmpzLzEuMS4wL21hZ25pZmljLXBvcHVwLm1pbi5jc3MifSx7ImxpbmsiOiIvL3N0YWNrcGF0aC5ib290c3RyYXBjZG4uY29tL2Jvb3RzdHJhcC80LjMuMS9jc3MvYm9vdHN0cmFwLm1pbi5jc3MifV19fX19LCJ1cmwiOiJpbmVzY29pbiIsInNpZ25hdHVyZSI6IjMwNDQwMjIwNDdkNjBjMjNmZDhlY2Q1NGM0Mzk0NmY3NzE1MzVmNDg2NzEzOWMxMjc2MmM0OWIzMjEyNWE1ZGM2YzE5NDM2MDAyMjA3MDJmNTk5YjhjZTUzNTViOTFhYWFkYjQ5Njg2ZDA3M2IxMDg1M2E1OTFhYjhhYTFiNjkyMjc3NmZkNDllMmNhIiwib3duZXJBZGRyZXNzIjoiMHg5Yzc5ODNhZTc2QTAzNzFmRmNlNTBEZjMzODNlRjUzRGVhMDY0N2I4Iiwib3duZXJQdWJsaWNLZXkiOiIwM2JkYjQzYmMwNWMwMzA1MDdjZmYyNGY1MzQ0N2IxMGM0YjQzYmNiMmVmM2NlMThiZjY0YTJjMjNkMmZhMWRiMjIiLCJoYXNoIjoiMTNjN2QwNWJlYjE1MmYxOTQ0YWMzZWFjMWJmMGIxZGVkYTcwMGNkZDNkZjAyMzc1YmVmMzkyN2E2MzVhNmNmOSIsImJsb2NrSGVpZ2h0IjoyLCJ0cmFuc2FjdGlvbkhhc2giOiIxMWNlNTI5OTQ4ZGFjYzdkMDBkYjlkNWQzN2JiMmY2NGEyZjgwMDJjMTk1ZjljYTczYWNkZjY0MzBlMjU2ZGNiIiwiYmxvY2tIZWlnaHRFbmQiOjE1MDAwMDJ9LCJoYXNoIjoiMTNjN2QwNWJlYjE1MmYxOTQ0YWMzZWFjMWJmMGIxZGVkYTcwMGNkZDNkZjAyMzc1YmVmMzkyN2E2MzVhNmNmOSIsInNpZ25hdHVyZSI6IjMwNDQwMjIwNDdkNjBjMjNmZDhlY2Q1NGM0Mzk0NmY3NzE1MzVmNDg2NzEzOWMxMjc2MmM0OWIzMjEyNWE1ZGM2YzE5NDM2MDAyMjA3MDJmNTk5YjhjZTUzNTViOTFhYWFkYjQ5Njg2ZDA3M2IxMDg1M2E1OTFhYjhhYTFiNjkyMjc3NmZkNDllMmNhIn1d",
         "toDoHash": "60104c141be40c481dd2165a9b8ef5881a7f06f138800fbab1611ad4f276bb97",
         "transfers": "{\"710b6c4e3f9f671a2f8cebba6f15eb452d75469c5ba25dc12a58dce453b511f4\":{\"to\":\"0x5967a4016501465CD951a1e3984F772AfDeB5207\",\"amount\":999000000,\"nonce\":\"3437313338313538303034353333383330323437333834\",\"walletId\":\"\",\"hash\":\"710b6c4e3f9f671a2f8cebba6f15eb452d75469c5ba25dc12a58dce453b511f4\",\"transactionHash\":\"4dc0e65abfa9e7e6f0a512ed005e50799caecd73e97576e54a726990460cae56\",\"from\":\"0x9c7983ae76A0371fFce50Df3383eF53Dea0647b8\",\"height\":3,\"createdAt\": 1580045338}}",
         "amount": 999000000,
         "amountWithFee": 1000000000,
         "createdAt": 1580045338,
         "coinbase": false,
         "fee": 1000000,
         "publicKey": "03bdb43bc05c030507cff24f53447b10c4b43bcb2ef3ce18bf64a2c23d2fa1db22",
         "signature": "3045022100e6bd7234b1423ccfcb40ea4b0df0b5fa422dd699ad6ba6e6abd98cd59503b1d502203175fc86840d80575c97de791ec3b0bd2ea12edb09049592911610d361b7f8bf",
         "status": "pending",
         "url": "inescoin",
         "urlAction": "update"
      },
      { 
         "hash": "11ce529948dacc7d00db9d5d37bb2f64a2f8002c195f9ca73acdf6430e256dcb",
         "configHash": "fd6e25c5cfc7974db849f95b19973465",
         "bankHash": "8c2abbfa2cd8ad2c93512dd215d7408d687ae7a5ae7b81b06054b83759cc6f5e",
         "blockHeight":2,
         "from": "0x9c7983ae76A0371fFce50Df3383eF53Dea0647b8",
         "toDo": "W3siYWN0aW9uIjoiY3JlYXRlIiwibmFtZSI6ImluZXNjb2luIiwiaGFzaCI6IjEzYzdkMDViZWIxNTJmMTk0NGFjM2VhYzFiZjBiMWRlZGE3MDBjZGQzZGYwMjM3NWJlZjM5MjdhNjM1YTZjZjkiLCJzaWduYXR1cmUiOiIzMDQ0MDIyMDQ3ZDYwYzIzZmQ4ZWNkNTRjNDM5NDZmNzcxNTM1ZjQ4NjcxMzljMTI3NjJjNDliMzIxMjVhNWRjNmMxOTQzNjAwMjIwNzAyZjU5OWI4Y2U1MzU1YjkxYWFhZGI0OTY4NmQwNzNiMTA4NTNhNTkxYWI4YWExYjY5MjI3NzZmZDQ5ZTJjYSJ9XQ==",
         "toDoHash": "c7b99525bd3a6ff32199a8aab9237d051b9726275a48a64ab18080fc26eecc1a",
         "transfers": "{\"5bad6834d0022780aae01cf6ea5a9e178727f3bf1dea35cea1d4085948573395\":{\"to\":\"0x5967a4016501465CD951a1e3984F772AfDeB5207\",\"amount\":299999000000,\"nonce\":\"3239363631313538303034353138333338303437303933\",\"walletId\":\"\",\"hash\":\"5bad6834d0022780aae01cf6ea5a9e178727f3bf1dea35cea1d4085948573395\",\"transactionHash\":\"11ce529948dacc7d00db9d5d37bb2f64a2f8002c195f9ca73acdf6430e256dcb\",\"from\":\"0x9c7983ae76A0371fFce50Df3383eF53Dea0647b8\",\"height\":2,\"createdAt\": 1580045183}}",
         "amount": 299999000000,
         "amountWithFee": 300000000000,
         "createdAt": 1580045183,
         "coinbase": false,
         "fee": 1000000,
         "publicKey": "03bdb43bc05c030507cff24f53447b10c4b43bcb2ef3ce18bf64a2c23d2fa1db22",
         "signature": "3044022030fad5225cd65e46be7ec79feffb1be531fece02ee150dcef63cfd4ed242ac21022046bfebb335705ce829056f7cc0817ad8f1c177a94acf05bdf1e57084ef9a3a95",
         "status": "pending",
         "url": "inescoin",
         "urlAction": "create"
      }
   ]
}
```

**[Back to top](#Get-started)**

## Get wallet addresses domain

| #   | URI                             | Method    | Description                                                |
|-----|---------------------------------|-----------|------------------------------------------------------------|
| 19. | `/get-wallet-addresses-domain`  | POST      | Get domain details by wallet addresses                                         |

Request

```
    curl -X POST \
      https://node.inescoin.org/get-wallet-addresses-domain \
      -H 'Content-Type: application/json' \
      -d '{
      "walletAddresses": "0x9c7983ae76A0371fFce50Df3383eF53Dea0647b8,"
    }'
```

Error response

```
[]
```

Success response

```
{ 
   "domainList":[ 
      { 
         "hash":"13c7d05beb152f1944ac3eac1bf0b1deda700cdd3df02375bef3927a635a6cf9",
         "url":"inescoin",
         "ownerAddress":"0x9c7983ae76A0371fFce50Df3383eF53Dea0647b8",
         "ownerPublicKey":"03bdb43bc05c030507cff24f53447b10c4b43bcb2ef3ce18bf64a2c23d2fa1db22",
         "signature":"3044022047d60c23fd8ecd54c43946f771535f4867139c12762c49b32125a5dc6c1943600220702f599b8ce5355b91aaadb49686d073b10853a591ab8aa1b6922776fd49e2ca",
         "blockHeight":2,
         "transactionHash":"11ce529948dacc7d00db9d5d37bb2f64a2f8002c195f9ca73acdf6430e256dcb",
         "blockHeightEnd":1500002
      }
   ],
   "total":1
}
```

**[Back to top](#Get-started)**

# Backup blockchain database

## Export
Export all your inescoin blockchain into gz file:
```
  bin/inescoin-export --prefix=inescoin --force=true
  # Output file => ./inescoin.tar.gz
```

## Import
Import blockchain from gz file:
```
  bin/inescoin-import --file=inescoin.tar.gz --prefix=abcd --force=true
```

#### Cut or reset your blockchain
For any reason you can reset or cut your blockchain from height (0 for all)
```
  bin/inescoin-reset --prefix=abcd --height=777
```

**[Back to top](#Get-started)**

# Docker dev env

  1. Create inescoin folder
  ```
    mkdir inescoin && cd inescoin
  ```

  2. Clone projects
  ```
    git clone git@github.com:inescoin/inescoin-blockchain.git
    git clone git@github.com:inescoin/inescoin-wallet.git
    git clone git@github.com:inescoin/inescoin-explorer.git
    git clone git@github.com:inescoin/inescoin-website-viewer.git

  ```

  4. Build & start containers
  ```
    cd ./inescoin-blocchain
    docker-compose up
  ```

  5. Run inescoin-node container
  ```
    docker exec -it inescoin-node bash

    # root@inescoin-node:/#
    cd /opt/
    composer install
  ```

  6. Replace elasticsearch localhost by elasticsearch (container name)
  ```
    sed -i 's/localhost/elasticsearch/g' /opt/src/core/ES/ESService.php
  ```

  7. Replace remote ip peer by 0.0.0.0
  ```
    sed -i 's/198\.199\.73\.197/0\.0\.0\.0/g' /opt/src/bin/inescoin-node
  ```

  8. Start inescoin-node daemon
  ```
    cd /opt/
    src/bin/inescoin-node --rpc-bind-port=8087 --p2p-bind-port=3031 --network=MAINNET --prefix=moon
  ```

  9. Start inescoin-sync (inescoin-node container)
  ```
    cd /opt/src/
    ./bin/inescoin-sync --prefix=moon
  ```
  
  10. Start inescoin-consumer (inescoin-node container)
  ```
    cd /opt/src/
    ./bin/inescoin-consumer --prefix=moon
  ```

  11. Start miner into other terminal
  ```
    docker exec -it inescoin-node bash

    # root@inescoin-node:/#
    cd /opt/src/
    bin/inescoin-miner --wallet-address=0x5967a4016501465CD951a1e3984F772AfDeB5207 --rpc-port=8087 --rpc-ip=0.0.0.0
  ```

  12. Run inescoin explorer http://localhost:8000
  ```
    docker exec -it inescoin-explorer-phpfpm bash

    # root@phpfpm:/#
    cd /www/
    composer install
  ```

    11. Run inescoin website viewer http://localhost:8001
  ```
    docker exec -it inescoin-website-viewer-phpfpm bash

    # root@phpfpm:/#
    cd /www/
    composer install
  ```

  13. Replace remote node ip by inescoin-node (container name)
  ```
    sed -i 's#https:\/\/node.inescoin.org\/#http:\/\/inescoin-node:8087\/#g' /www/src/app/App.php
  ```

  14. Kibana
  ```
    Open in browser: http://localhost:5608/
  ```


**[Back to top](#Get-started)**

# License - GNU GPL v3

    Inescoin
    Copyright (C) 2020  Mounir R'Quiba

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.

**[Back to top](#Get-started)**
