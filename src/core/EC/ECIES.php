<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\EC;

class ECIES {

    private $privateKey;
    private $publicKey;
    private $rBuf;
    private $kEkM;
    private $kE;
    private $kM;
    private $opts;

    public function __construct($privateKey, $publicKey, $opts = array("noKey" => true, "shortTag" => true)) {
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
        $this->opts = $opts;
    }

    public function getRbuf() {
        if (is_null($this->rBuf)) {
            $this->rBuf = Utils::hex2bin($this->privateKey->getPublic(true, "hex"));
        }
        return $this->rBuf;
    }

    private function getSharedKey()
    {
        $shared = $this->privateKey->derive($this->publicKey->getPublic());
        $bin = Utils::hex2bin( $shared->toString("hex") );
        return hash("sha512", $bin, true);
    }

    public function getkEkM() {
        if (is_null($this->kEkM)) {
            $this->kEkM = $this->getSharedKey();
        }
        return $this->kEkM;
    }

    public function getkE() {
        if (is_null($this->kE)) {
            $this->kE = Utils::substring($this->getkEkM(), 0, 32);
        }
        return $this->kE;
    }

    public function getkM() {
        if (is_null($this->kM)) {
            $this->kM = Utils::substring($this->getkEkM(), 32, 64);
        }
        return $this->kM;
    }

    private function getPrivateEncKey()
    {
        $hex = $this->privateKey->getPrivate("hex");
        return Utils::hex2bin( $hex );
    }

    public function encrypt($message, $ivbuf = null) {
        if (is_null($ivbuf)) {
            $ivbuf = Utils::substring(Crypto::hmacSha256($this->getPrivateEncKey(), $message), 0, 16);
        }
        $c = $ivbuf . Crypto::aes256CbcPkcs7Encrypt($message, $this->getkE(), $ivbuf);
        $d = Crypto::hmacSha256($this->getkM(), $c);
        if (Utils::arrayValue($this->opts, "shortTag")) {
            $d = Utils::substring($d, 0, 4);
        }
        if (Utils::arrayValue($this->opts, "noKey")) {
            $encbuf = $c . $d;
        }
        else {
            $encbuf = $this->getRbuf() . $c . $d;
        }
        return $encbuf;
    }

    public function decrypt($encbuf) {
        $offset = 0;
        $tagLength = 32;
        if (Utils::arrayValue($this->opts, "shortTag")) {
            $tagLength = 4;
        }
        if (!Utils::arrayValue($this->opts, "noKey")) {
            $offset = 33;
             $this->publicKey = Utils::substring($encbuf, 0, 33);
        }

        $c = Utils::substring($encbuf, $offset, strlen($encbuf) - $tagLength);
        $d = Utils::substring($encbuf, strlen($encbuf) - $tagLength, strlen($encbuf));

        $d2 = Crypto::hmacSha256($this->getkM(), $c);
        if (Utils::arrayValue($this->opts, "shortTag")) {
            $d2 = Utils::substring($d2, 0, 4);
        }

        $equal = true;
        for ($i = 0; $i < strlen($d); $i++) {
            $equal &= ($d[$i] === $d2[$i]);
        }
        if (!$equal) {
            throw new \Exception("Invalid checksum");
        }

        return Crypto::aes256CbcPkcs7Decrypt(Utils::substring($c, 16, strlen($c)), $this->getkE(), Utils::substring($c, 0, 16));
    }
}
