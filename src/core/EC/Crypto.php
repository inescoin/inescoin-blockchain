<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\EC;

class Crypto {

    public static function hmacSha256($key, $data) {
        return hash_hmac("sha256", $data, $key, true);
    }

    public static function aes256CbcPkcs7Encrypt($data, $key, $iv) {
        return openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }

    public static function aes256CbcPkcs7Decrypt($data, $key, $iv) {
        return openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}
