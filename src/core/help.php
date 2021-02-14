<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

require __DIR__.'/../../vendor/autoload.php';

use Inescoin\BlockchainConfig;

$help['consumer'] = "
" . ucfirst(BlockchainConfig::NAME) . " [" . strtoupper(BlockchainConfig::SYMBOL). "] v". BlockchainConfig::VERSION ."

Usage:
  bin/" . BlockchainConfig::NAME . "-consumer --help
  bin/" . BlockchainConfig::NAME . "-consumer --prefix=" . BlockchainConfig::NAME . "

Options:
  --help               Show help console

  --prefix=<prefix>    Blockchain prefix (" . BlockchainConfig::NAME . ")
  ". PHP_EOL;

$help['sync'] = "
" . ucfirst(BlockchainConfig::NAME) . " [" . strtoupper(BlockchainConfig::SYMBOL). "] v". BlockchainConfig::VERSION ."

Usage:
  bin/" . BlockchainConfig::NAME . "-sync --help
  bin/" . BlockchainConfig::NAME . "-sync --prefix=" . BlockchainConfig::NAME . "

Options:
  --help               Show help console

  --prefix=<prefix>    Blockchain prefix (" . BlockchainConfig::NAME . ")
  ". PHP_EOL;

$help['export'] = "
" . ucfirst(BlockchainConfig::NAME) . " [" . strtoupper(BlockchainConfig::SYMBOL). "] v". BlockchainConfig::VERSION ."

Usage:
  bin/" . BlockchainConfig::NAME . "-export --help
  bin/" . BlockchainConfig::NAME . "-export --prefix=" . BlockchainConfig::NAME . " --file=./" . BlockchainConfig::NAME . " --force

Options:
  --help               Show help console

  --prefix=<prefix>    Blockchain prefix (" . BlockchainConfig::NAME . ")

  --file=<file>        TAR GZ file to export (./" . BlockchainConfig::NAME . ")
  --force              Remove archive file if exists
  ". PHP_EOL;

$help['import'] = "
" . ucfirst(BlockchainConfig::NAME) . " [" . strtoupper(BlockchainConfig::SYMBOL). "] v". BlockchainConfig::VERSION ."

Usage:
  bin/" . BlockchainConfig::NAME . "-import --help
  bin/" . BlockchainConfig::NAME . "-import --prefix=" . BlockchainConfig::NAME . " --file=./" . BlockchainConfig::NAME . ".tar.gz --force

Options:
  --help               Show help console

  --prefix=<prefix>    Blockchain prefix (" . BlockchainConfig::NAME . ")

  --file=<file>        TAR GZ file to import (./" . BlockchainConfig::NAME . ".tar.gz)
  --force              Clear database then start import
	". PHP_EOL;

$help['miner'] = "
" . ucfirst(BlockchainConfig::NAME) . " [" . strtoupper(BlockchainConfig::SYMBOL). "] v". BlockchainConfig::VERSION ."

Usage:
  bin/" . BlockchainConfig::NAME . "-miner --help
  bin/" . BlockchainConfig::NAME . "-miner --rpc-ip=0.0.0.0 --rpc-port=8086 --wallet-address=XXXXXXXXXXXXXXXXXXXXXXXXX

Options:
  --help                              Show help console

  --rpc-ip=<rpc-ip>                   Remote node RPC ip (127.0.0.0)
  --rpc-port=<rpc-port>               Remote node RPC port (8086)

  --wallet-address=<wallet-address>   Miner wallet address
	". PHP_EOL;

$help['node'] = "
" . ucfirst(BlockchainConfig::NAME) . " [" . strtoupper(BlockchainConfig::SYMBOL). "] v". BlockchainConfig::VERSION ."

Usage:
  bin/" . BlockchainConfig::NAME . "-node --help
  bin/" . BlockchainConfig::NAME . "-node --rpc-bind-ip=0.0.0.0 --data-folder=./ --rpc-bind-port=8087 --p2p-bind-port=3031 --network=MAINNET --prefix=bob
Options:
  --help                         Show help console

  --genesis                      Show genesis Hash and exit

  --reset                        Reset and clean blockchain

  --prefix=<prefix>              Blockchain prefix (" . BlockchainConfig::NAME . ")
  --network=<network>            MAINNET or TESTNET

  --data-folder=<folder>         Working directory (./)

  --rpc-bind-ip=<rpc-ip>         RPC ip (127.0.0.0)
  --rpc-bind-port=<rpc-port>     RPC port (8086)

  --p2p-bind-ip=<p2p-ip>         P2P ip (127.0.0.0)
  --p2p-bind-port=<p2p-port>     P2P port (3030)
	". PHP_EOL;

$help['reset'] = "
" . ucfirst(BlockchainConfig::NAME) . " [" . strtoupper(BlockchainConfig::SYMBOL). "] v". BlockchainConfig::VERSION ."
Usage:
  bin/" . BlockchainConfig::NAME . "-reset --help
  bin/" . BlockchainConfig::NAME . "-reset --height=0 --prefix=" . BlockchainConfig::NAME . "
Options:
  --help                       Show help console

  --prefix=<prefix>            Blockchain prefix (" . BlockchainConfig::NAME . ")
  --height=<height>            Cut from block height (0)
	". PHP_EOL;

$help['wallet'] = "
" . ucfirst(BlockchainConfig::NAME) . " [" . strtoupper(BlockchainConfig::SYMBOL). "] v". BlockchainConfig::VERSION ."

Usage:
  bin/" . BlockchainConfig::NAME . "-wallet --help
  bin/" . BlockchainConfig::NAME . "-wallet --wallet-file=moon-air --wallet-password=veryStrongPassword --node-address=127.0.0.1:8087 --node-protocol=http --data-folder=./ --prefix=" . BlockchainConfig::NAME . "
Options:
  --help                                 Show help console

  --prefix=<prefix>                      Blockchain prefix (" . BlockchainConfig::NAME . ")

  --wallet-file=<wallet-file>            Wallet file
  --wallet-password=<wallet-password>    Wallet password

  --data-folder=<data-folder>            Folder data (./)

  --node-address=<node-ip>:<node-port>   Remote " . BlockchainConfig::NAME . " node (127.0.0.1:8087)
  --node-protocol=<node-protocol>        Remote " . BlockchainConfig::NAME . " node protocol (http)
	". PHP_EOL;
