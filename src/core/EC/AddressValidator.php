<?php

// Copyright 2019 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\EC;

use Inescoin\PKI;
use kornrunner\Keccak;

class AddressValidator
{
    public const ADDRESS_VALID = 'valid';

    public const ADDRESS_INVALID = 'invalid';

    public const ADDRESS_CHECKSUM_INVALID = 'checksum-invalid';

    public const ADDRESS_PUBLIC_INVALID = 'public-invalid';

    public const INVALID = [
        AddressValidator::ADDRESS_INVALID,
        AddressValidator::ADDRESS_CHECKSUM_INVALID,
        AddressValidator::ADDRESS_PUBLIC_INVALID,
    ];

    public static function isValid(string $address, $publicKeyHex = null)
    {
        if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $address)) {
            return self::ADDRESS_INVALID;
        }

        if (!empty($publicKeyHex) && !PKI::validPublicKeyAddress($publicKeyHex, $address)) {
            return self::ADDRESS_PUBLIC_INVALID;
        }

        if (preg_match('/^0x[a-f0-9]{40}$/', $address) || preg_match('/^0x[A-F0-9]{40}$/', $address)) {
            return self::ADDRESS_VALID;
        }

        return self::validateChecksum(substr($address, 2));
    }

    private static function validateChecksum($address)
    {
        $addressHash = Keccak::hash(strtolower($address), 256);
        $addressArray = str_split($address);
        $addressHashArray = str_split($addressHash);

        for ($i = 0; $i < 40; $i++) {
            if (
                (intval($addressHashArray[$i], 16) > 7
                    && strtoupper($addressArray[$i]) !== $addressArray[$i])
                || (intval($addressHashArray[$i], 16) <= 7
                    && strtolower($addressArray[$i]) !== $addressArray[$i])
            ) {
                return self::ADDRESS_CHECKSUM_INVALID;
            }
        }
        return self::ADDRESS_VALID;
    }


    public static function getCanonicalAddress(string $address)
    {
        if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $address)) {
            return NULL;
        }

        $address = substr($address, 2);
        $addressHash = Keccak::hash(strtolower($address), 256);
        $addressArray = str_split($address);
        $addressHashArray = str_split($addressHash);
        $ret = '';
        for ($i = 0; $i < 40; $i++) {
            // the nth letter should be uppercase if the nth digit of casemap is 1
            if (intval($addressHashArray[$i], 16) > 7) {
                $ret .= strtoupper($addressArray[$i]);
            } else /*if (intval($addressHashArray[$i], 16) <= 7)*/ {
                $ret .= strtolower($addressArray[$i]);
            }
        }
        return '0x' . $ret;
    }
}
