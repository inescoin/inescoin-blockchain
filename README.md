# Inescoin Blockchain
## Encrypted messenger with unfalsifiable transactions

  1 - Install node with ansible (Ubuntu)

  ```
    git clone https://github.com/inescoin/inescoin-ansible
    
    # Update your /etc/ansible/hosts file  with remote IP
    cd inescoin-ansible && ansible-playbook inescoin.yml
     
    # New systemctrl inescoin-node.service is now available
    # /etc/systemd/system/inescoin-node.service
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
  ```

# Inescoin Node API

HTTP request to an entry point:
```
http://<ip>:<port>/<uri>
```
where:
* `<ip>` is IPv4 address of `inescoin-node` service. 
* `<port>` is TCP port of `inescoin-node`. By default the service is bound to `8087`.


## Methods

| #   | URI                                                             | Method    | Description                                                |
|-----|-----------------------------------------------------------------|-----------|------------------------------------------------------------|
| 1.  | [`/status`](#status)                                            | GET       | Get node status                                            |
| 2.  | [`/top-block`](#top-block)                                      | GET       | Get top block data                                         |
| 3.  | [`/top-height`](#top-height)                                    | GET       | Get top height.                                            |
| 4.  | [`/public-key`](#public-key)                                    | GET       | Get node public key.                                       |
| 5.  | [`/mempool`](#memory-transactions-pool)                         | GET       | Get memory transactions pool.                              |
| 6.  | [`/peers`](#peers)                                              | GET       | Get node peers list.                                       |
| 7.  | [`/get-blocks`](#get-blocks)                                    | POST      | Get blocks                                                 |
| 8.  | [`/get-block-by-height`](#get-block-by-height)                  | POST      | Get block by height                                        |
| 9.  | [`/get-block-by-hash`](#get-block-by-hash)                      | POST      | Get block by hash                                          |
| 10. | [`/get-transaction-by-hash`](#get-transaction-by-hash)          | POST      | Get transaction by hash                                    |
| 11. | [`/get-transfer-by-hash`](#get-transfer-by-hash)                | POST      | Get transfer by hash                                       |
| 12. | [`/get-wallet-address-infos`](#get-wallet-address-infos)        | POST      | Get wallet address details                                 |
| 13. | [`/get-wallet-addresses-infos`](#get-wallet-addresses-infos)    | POST      | Get wallet addresses balance                               |
| 14. | [`/transaction`](#transaction)                                  | POST      | Send transaction                                           |
| 15. | [`/getBlockTemplate`](#get-block-template)                        | POST      | Get block template with transactions pool                  |
| 16. | [`/submitBlockHash`](#submit-block-hash)                          | POST      | Submit block hash                                          |

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
  "height": 94966,
  "topKnowHeight": 94966,
  "cumulativeDifficulty": 127811977158,
  "totalTransfer": 94982,
  "totalTransaction": 94982,
  "bankAmount": 10496600000000000,
  "localPeerConfig": {
    "host": "<ip>",
    "port": 3031
  },
  "isSync": true,
  "peersPersistence": [],
  "peers": []
}
```

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

# Docker - Dev environment

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
    docker-composer up
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

  8. Start other inescoin-node daemon into other terminal
  ```
    docker exec -it inescoin-node bash

    # root@inescoin-node:/#
    cd /opt/
    src/bin/inescoin-node --rpc-bind-port=8087 --p2p-bind-port=3031 --network=MAINNET --prefix=alice
  ```

  9. Start miner into other terminal
  ```
    docker exec -it inescoin-node bash

    # root@inescoin-node:/#
    cd /opt/src/
    bin/inescoin-miner --wallet-address=0x5967a4016501465CD951a1e3984F772AfDeB5207 --rpc-port=8087 --rpc-ip=0.0.0.0
  ```

  10. Run inescoin explorer http://localhost:8000
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

  12. Replace remote node ip by inescoin-node (container name)
  ```
    sed -i 's#https:\/\/node.inescoin.org\/#http:\/\/inescoin-node:8087\/#g' /www/src/app/App.php
  ```

  13. Kibana
  ```
    Open in browser: http://localhost:5608/
  ```
