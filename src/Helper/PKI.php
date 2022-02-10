<?php

// Copyright 2019-2022 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\Helper;

use Sop\CryptoTypes\Asymmetric\EC\ECPublicKey;
use Sop\CryptoTypes\Asymmetric\EC\ECPrivateKey;
use Sop\CryptoTypes\Asymmetric\RSA\RSAPrivateKey;

use Sop\CryptoEncoding\PEM;
use kornrunner\Keccak;
use Elliptic\EC;
use Inescoin\EC\ECIES;

use BN\BN;

use Inescoin\BlockchainConfig;

class PKI
{
	public static function newEcKeys() {
		$ec = new EC('secp256k1');
		$keys = $ec->genKeyPair();

		$publicKeyHex = $keys->getPublic(true, "hex");

		return [
			'privateKey' => '0x' . $keys->getPrivate("hex"),
			'publicKey' => $publicKeyHex,
			'address' => self::publicKeyToAddress($publicKeyHex)
		];
	}

	public static function newChildEcKeys($publicKey, $privateKey) {
		$ec = new EC('secp256k1');

		$i = 2018;
		$I_key  = hex2bin($privateKey);
		$I_data = hex2bin($publicKey) . pack("N", $i);
		$I = hash_hmac("sha512", $I_data, $I_key);
		$I_L = substr($I, 0, 64);
		$I_R = substr($I, 64, 64);
		$c_i = $I_R;

		$K_par_point = $ec->curve->decodePoint($publicKey, "hex");
		$I_L_point = $ec->g->mul(new BN($I_L, 16));
		$K_i = $K_par_point->add($I_L_point);
		$K_i = $K_i->encodeCompressed("hex");

		return [
			'privateKey' => $c_i,
			'publicKey' => $K_i,
		];
	}

	public static function publicKeyToAddress($pubkeyHex) {
	    return "0x" . substr(Keccak::hash(substr(hex2bin($pubkeyHex), 1), 256), 24);
	}

	public static function validPublicKeyAddress($pubkeyHex, $address) {
		$ec = new EC('secp256k1');

		$keys = $ec->keyPair([
			'pub' => $pubkeyHex,
			'pubEnc' => 'hex'
		]);

		$publicKeyHex = $keys->getPublic(false, "hex");
		$publicKeyHexTrue = $keys->getPublic(true, "hex");

		$_address = self::publicKeyToAddress($publicKeyHex);
		$_addressTrue = self::publicKeyToAddress($publicKeyHexTrue);

		return strtolower($_address) === strtolower($address) || strtolower($_addressTrue) === strtolower($address);
	}

	public static function ecSign($message, $privateKeyHex)
	{
		$ecdsa = new EC('secp256k1');
		$ecdsa->keyFromPrivate($privateKeyHex, 'hex');
		$signature = $ecdsa->sign(bin2hex($message), $privateKeyHex);

		return $signature->toDER('hex');
	}

	public static function ecVerify($message, $signature, $publicKeyHex)
	{
		$ecdsa = new EC('secp256k1');

		return $ecdsa->verify(bin2hex($message), $signature, $publicKeyHex, 'hex');
	}

	public static function ecEncrypt($message, $privateKeyFrom, $publicKeyTo)
	{
		$ec = new EC('secp256k1');
		$privateKeyFrom = str_replace('0x', '', $privateKeyFrom);
		$ecdsa = new ECIES($ec->keyFromPrivate($privateKeyFrom, 'hex'), $ec->keyFromPublic($publicKeyTo, 'hex'));

		return bin2hex($ecdsa->encrypt($message));
	}

	public static function ecDecrypt($cipherHex, $privateKeyTo, $publicKeyFrom)
	{
		$ec = new EC('secp256k1');

		$privateKeyTo = str_replace('0x', '', $privateKeyTo);
		$ecdsa = new ECIES($ec->keyFromPrivate($privateKeyTo, 'hex'), $ec->keyFromPublic($publicKeyFrom, 'hex'));

		return $ecdsa->decrypt(hex2bin($cipherHex));
	}

	public static function ecVerifyHex($message, $signature, $publicKeyHex)
	{
		$ecdsa = new EC('secp256k1');

		return $ecdsa->verify($message, $signature, $publicKeyHex, 'hex');
	}

	private static function _generateKeys(string $type = 'RSA', int $bits = 512, $base64 = true)
	{
		$config = $type === 'EC'
			? [
				'curve_name' => 'secp256k1',
				'private_key_type' => OPENSSL_KEYTYPE_EC,
			] : [
				'digest_alg' => 'sha512',
				'private_key_bits' => $bits,
				'private_key_type' => OPENSSL_KEYTYPE_RSA,
			];

		$res = openssl_pkey_new($config);

		openssl_pkey_export($res, $privateKey);

		$keyDetail = openssl_pkey_get_details($res);
		$publicKey = $keyDetail["key"];

		return  [
			'publicKey' => $base64 ? base64_encode($publicKey) : $publicKey,
			'privateKey' => $base64 ? base64_encode($privateKey) : $privateKey
		];
	}

	public static function generateRSAKeys($bits = 512, $base64 = true)
	{
		return self::_generateKeys('RSA', $bits, $base64);
	}

	public static function generateECKeys($base64 = true)
	{
		$keys = self::_generateKeys('EC', 0, $base64);

		$privPem = PEM::fromString($base64 ? base64_decode($keys['privateKey']) : $keys['privateKey']);

		$ecPrivKey = ECPrivateKey::fromPEM($privPem);
		$ecPrivSeq = $ecPrivKey->toASN1();

		$privKeyHex = bin2hex($ecPrivSeq->at(1)->asOctetString()->string());

		$pubKeyHex = substr(bin2hex($ecPrivSeq->at(3)->asTagged()->asExplicit()->asBitString()->string()), 2);

		$hash = Keccak::hash(hex2bin($pubKeyHex), 256);

		$keys['publicAddress'] = '0x' . substr($hash, -40);
		$keys['privateAddress'] = '0x' . $privKeyHex;

		return $keys;
	}

	public static function encrypt($message, $privateKey)
	{
		openssl_private_encrypt($message, $crypted, $privateKey);

		return base64_encode($crypted);
	}

	public static function encryptFromPublicKey($message, $publicKey)
	{
		openssl_public_encrypt($message, $crypted, $publicKey);

		return base64_encode($crypted);
	}

	public static function decrypt($crypted, $publicKey)
	{
		openssl_public_decrypt(base64_decode($crypted), $decrypted, $publicKey);

		return $decrypted;
	}

	public static function decryptFromPrivateKey($crypted, $privateKey)
	{
		openssl_private_decrypt(base64_decode($crypted), $decrypted, $privateKey);

		return $decrypted;
	}

	public static function isValid($message, $crypted, $publicKey)
	{
		return $message == self::decrypt($crypted, $publicKey);
	}

	public static function encryptFromKey($data, $key)
    {
        $l = strlen($key);
        if ($l < 16) {
            $key = str_repeat($key, ceil(16/$l));
        }

        if ($m = strlen($data)%8) {
            $data .= str_repeat("\x00",  8 - $m);
        }

        if (function_exists('mcrypt_encrypt')){
            $val = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $data, MCRYPT_MODE_ECB);
        } else {
            $val = openssl_encrypt($data, 'BF-ECB', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
        }

        return $val;
    }

    public static function decryptFromKey($data, $key)
    {
        $l = strlen($key);
        if ($l < 16) {
            $key = str_repeat($key, ceil(16/$l));
        }

        if (function_exists('mcrypt_encrypt')) {
            $val = mcrypt_decrypt(MCRYPT_BLOWFISH, $key, $data, MCRYPT_MODE_ECB);
        } else {
            $val = openssl_decrypt($data, 'BF-ECB', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
        }

        return $val;
    }

    public static function sign($message, $privateKey, $algo = OPENSSL_ALGO_SHA256)
    {
		openssl_sign($message, $signature, $privateKey, $algo);

		return base64_encode($signature);
    }

    public static function verify($data, $signature, $pubkeyid, $algo = OPENSSL_ALGO_SHA256)
    {
    	// var_dump('verify', $data, base64_decode($signature), $pubkeyid, $algo);
    	return openssl_verify($data, base64_decode($signature), $pubkeyid, $algo);;
    }

    public static function generateCertificat($name = BlockchainConfig::NAME)
    {
    	$dn = array(
			"countryName" => "US",
			"stateOrProvinceName" => "California",
			"localityName" => "San Jose",
			"organizationName" => "Chat Inc.",
			"organizationalUnitName" => "Engineering",
			"commonName" => "localhost",
			"emailAddress" => "email@example.com"
		);

		$privkey = openssl_pkey_new(array(
		    "private_key_bits" => 2048,
		    "private_key_type" => OPENSSL_KEYTYPE_RSA,
		));

		$csr = openssl_csr_new($dn, $privkey, array('digest_alg' => 'sha256'));
		$reqCert = openssl_csr_sign($csr, null, $privkey, 365);

		openssl_x509_export($reqCert, $certOutx509);
		openssl_pkey_export($privkey, $privkeyOut);

		openssl_pkey_export_to_file($privkey, $name . '.key');
		file_put_contents($name . '-crt.pem', $privkeyOut . $certOutx509);

		chmod($name . '-crt.pem', 0600);

		// $reqCert = openssl_csr_sign($csr, NULL, $privkey, 365);
		// openssl_x509_export($reqCert, $certout);
		// openssl_x509_export_to_file($certout, $name . '-crt.pem');

		return $name . '-crt.pem';
    }

    public static function encryptForNode($dataArray, $publicKey, $privateKey, $nodePublicKey) {
    	if (!is_array($dataArray)) {
    		return [];
    	}

    	$b64 = base64_encode(json_encode($dataArray));
    	$b64Split = str_split($b64, 20);

    	$output = [
    		'publicKey' => $publicKey,
    		'message' => []
    	];

    	$privateKey = str_replace('0x', '', $privateKey);

    	foreach ($b64Split as $part) {
    		$_part = PKI::encryptFromPublicKey($part, base64_decode($nodePublicKey));
    		$output['message'][] = [
    			'd' => bin2hex($_part),
    			's' => PKI::ecSign($_part, $privateKey)
    		];
    	}

    	return $output;
    }

    public static function encryptForTransfer($messageBase64, $toPublicKey = '', $fromPrivatekey = '')
    {
    	// $toPublicKey = base64_decode("LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUF2YndRdXhLZ0hPenBzcDBWWC9vbgoydFdlek5aWm9tbmFlNERROEJEQVY3aGdLL2dtOFpXbkp1UmZ0QU9TOFdGVFhtdFpsRlNLTE5sbGRMd2YvN1BZClk5OXcyV01oaU54NER1VThTNEhqSGFyd3AyU3NNYlUwdC9PM3g4VFVPeFFWSG1lRnZ1a2hmWXlqN3BFYlJXTisKQ2NmK0kzVzZoTGV2M0k1MExVZmlUcU8zM21RYjcrZlgrNE4vSmJFY3FsbERZSUlRTVVneklNcnV2bUxBZVJpMQp1cHZsaXExZG05Z0E3TGg1RytuczAvREQ2L1VYM0Y1MGdyRUwvVnNjVFk4R0dDenJiMEZRLzlaZHRoejdsb1ZVCmRHWVN0eWVmSVVOUzFGaHNoSkR6RTFuZk13RG0xWkw3REpEaFR2UlZ1Y3FoWEhadVdOUEhhWGpndkR1WC9ocUwKRHdJREFRQUIKLS0tLS1FTkQgUFVCTElDIEtFWS0tLS0tCg==");

    	// $fromPrivatekey = base64_decode("LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JSUV2UUlCQURBTkJna3Foa2lHOXcwQkFRRUZBQVNDQktjd2dnU2pBZ0VBQW9JQkFRQzl2QkM3RXFBYzdPbXkKblJWZitpZmExWjdNMWxtaWFkcDdnTkR3RU1CWHVHQXIrQ2J4bGFjbTVGKzBBNUx4WVZOZWExbVVWSW9zMldWMAp2Qi8vczloajMzRFpZeUdJM0hnTzVUeExnZU1kcXZDblpLd3h0VFMzODdmSHhOUTdGQlVlWjRXKzZTRjlqS1B1CmtSdEZZMzRKeC80amRicUV0Ni9jam5RdFIrSk9vN2ZlWkJ2djU5ZjdnMzhsc1J5cVdVTmdnaEF4U0RNZ3l1NisKWXNCNUdMVzZtK1dLclYyYjJBRHN1SGtiNmV6VDhNUHI5UmZjWG5TQ3NRdjlXeHhOandZWUxPdHZRVkQvMWwyMgpIUHVXaFZSMFpoSzNKNThoUTFMVVdHeUVrUE1UV2Q4ekFPYlZrdnNNa09GTzlGVzV5cUZjZG01WTA4ZHBlT0M4Ck81ZitHb3NQQWdNQkFBRUNnZ0VBSTk2NE13WFVhMk9HMHhQTGhMZWdiVWpSbXR3eldmYzFMUUF2Z0JOS3FjcmIKczdSWWVIZllnQXZRNUJHQTZFMkVHMmVrS2R6SnVxem05MmpSaStBT1d1TlZUR1BuWlI1NVBDZXVmSC96MWhvSgpJVHh4S1h5ZW1PQmtzRW5QN2ROZ0lyMWpsYkl4ZUxEc0ZTQXR6YkovazQzUnlCWnJ5c2VIWWVVMHBaTGZnQW8wClRac2JvUnAvUkVGL2YvWXhkL1VQTUplOC9YbmhuUFgyQkl1M1pvNVViSll1MTZOa2Z5Q2NmRlJ0RTJMaGpwdmEKdXZ3ZUNtZ2VBSUJ1b21UR2JRek40R0doS1g5QzFZNWI1TnppUVpVOXF3S05iVmt4Q0dHYk5UZGlmQVF1dlJyQgpud3JVNUNvZVdNQi95ejBzaEM5Yk5raWRiYXNVTlgwQUlkekJhemx4TVFLQmdRRGloQkdXZncrYng4aDI1VzZsCmVOSmhETE4rYU5OTjkwa0g0TVRyM3dyZTR0NTNLZXZwOXdvMnFoaTMxWWNWWVFZNG95Rlk1a0FJM3k4RDEyMlcKc2Z2VFJkeVJZdHlBZjV1TWk5N0NmMWh1SXN6WWhkK2ErTVBQQWNaQjYydForTnFUbS9ZV0R4cW5sVFYvZ1UrZApQUHBMSFhneFlQc0VpbWFKZjhHRGVvdkZoUUtCZ1FEV2JtQmhadmM0WW41QURKSHhWOUt3ZFBoNjhvR1BHK0VoCjhySnp1OGVjTDMrMGx5eVBVZUsveVRKQ2RPQkgzQmpYR0pXOVFaMG0wbkZRU0QwWUpSMnlQVHAxcTdBTzEyWGoKUFZHS0UwS1JtWjhBNjRPamJha2k1czZybjM4MnBOdjRIdEtJdWtJNUN0MXFrYjdYbmhUODZXcFl4ZXJTZXVETgpNb3AxaXRBWWd3S0JnREh4a0xjd0dNN3VRK01EUDF3NHdab29aTU13ZGJheEdXY2xSZ1lEemEvTE1lWHdWbFhsCjVGaWROSW9FQ0o3TUg4VUpJdWNwRFdGblpFUmlrWVV5aFNYV040WE8ySE4wcjJWVHlhLzB5Qml3ajU0R0ZvRmkKN0RtT1dKcGNQL1U4aTJVVWREUDA3Sm1hcW9zTWhmTlRhSlI3VU84Q1JSYUJOWTZIbnJGUXFkVEpBb0dBRm8yagphM3MxODJOQW5pSDBVNnNHQ1BNMGsxSGdXSm41RXVZQTZQVk9LRnBDbDA3eks5dlQrcElCekVXWXRWWXI4cXV1ClRDcVRpZHJHZWtndXpOUlNqRVd1V1dRR2Iza3VTVGxRMHpIMVpYVC82VXZjRzV0VUY4eW8zaG8zZWhyYTIvejUKN2RHUlY0aVNBenh6RXlDWVdvVitYdm5xR2RzOHU3aGVJY0RJNUZzQ2dZRUFxS0xhb2V4eGp1S3J5VG1KdjlIRgpjb29LaHlTMXVvdGV1bkl4c3JURGg4emx3Ym40SDJJbFJNTThIZ3RmT3RoOTVYSUwvSVpQK1o2OTMzOEVoa2J5Cmt2OG1Zck9CVS8rSVlWY0wyQXpUUlJ6M0NvdnRWWlg3QzUrMVI5aTdWcEdSVFVhUldDQ1RKYzVsUFZ4ZG5vOXMKWHN3d0o0K2YwZWdDbmVRT2JkY3RhbTg9Ci0tLS0tRU5EIFBSSVZBVEUgS0VZLS0tLS0K");

    	// $messageBase64 = base64_encode(serialize($arrayMessage));
    	// var_dump($messageBase64);
    	// $encryptedMessage = self::encryptFromPublicKey($messageBase64, $toPublicKey);
    	// $encryptedMessageBase64 = base64_encode($encryptedMessage);

    	$buffer = [];
    	$message = $messageBase64;
		$len = strlen($message);
		$pos = 0;
		$by = 53; // Over 53 $part is empty :(
		try {
			for ($i = 0; $i < $len; $i += $by) {
				$part = self::encryptFromPublicKey(substr($message, $i, $by), $toPublicKey);
				$buffer[$pos]['d'] = base64_encode($part);

				if (!$pos) {
					$buffer[$pos]['s'] = base64_encode(self::sign($part, $fromPrivatekey));
				}
				$pos++;

				// var_dump('encryptForTransfer', $pos .'/' . (ceil($len / $by)));
			}
		} catch(\Exception $e) {
			var_dump($e->getMessage());
		}

		$encrypted = base64_encode(serialize($buffer));

		// var_dump('$encrypted', $encrypted, $buffer);

    	return $encrypted;
    }

    public static function decryptFromTransfer($messageArray, $toPublicKey = '', $fromPrivatekey = '')
    {
    	// $toPublicKey = base64_decode("LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUF2YndRdXhLZ0hPenBzcDBWWC9vbgoydFdlek5aWm9tbmFlNERROEJEQVY3aGdLL2dtOFpXbkp1UmZ0QU9TOFdGVFhtdFpsRlNLTE5sbGRMd2YvN1BZClk5OXcyV01oaU54NER1VThTNEhqSGFyd3AyU3NNYlUwdC9PM3g4VFVPeFFWSG1lRnZ1a2hmWXlqN3BFYlJXTisKQ2NmK0kzVzZoTGV2M0k1MExVZmlUcU8zM21RYjcrZlgrNE4vSmJFY3FsbERZSUlRTVVneklNcnV2bUxBZVJpMQp1cHZsaXExZG05Z0E3TGg1RytuczAvREQ2L1VYM0Y1MGdyRUwvVnNjVFk4R0dDenJiMEZRLzlaZHRoejdsb1ZVCmRHWVN0eWVmSVVOUzFGaHNoSkR6RTFuZk13RG0xWkw3REpEaFR2UlZ1Y3FoWEhadVdOUEhhWGpndkR1WC9ocUwKRHdJREFRQUIKLS0tLS1FTkQgUFVCTElDIEtFWS0tLS0tCg==");

    	// $fromPrivatekey = base64_decode("LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JSUV2UUlCQURBTkJna3Foa2lHOXcwQkFRRUZBQVNDQktjd2dnU2pBZ0VBQW9JQkFRQzl2QkM3RXFBYzdPbXkKblJWZitpZmExWjdNMWxtaWFkcDdnTkR3RU1CWHVHQXIrQ2J4bGFjbTVGKzBBNUx4WVZOZWExbVVWSW9zMldWMAp2Qi8vczloajMzRFpZeUdJM0hnTzVUeExnZU1kcXZDblpLd3h0VFMzODdmSHhOUTdGQlVlWjRXKzZTRjlqS1B1CmtSdEZZMzRKeC80amRicUV0Ni9jam5RdFIrSk9vN2ZlWkJ2djU5ZjdnMzhsc1J5cVdVTmdnaEF4U0RNZ3l1NisKWXNCNUdMVzZtK1dLclYyYjJBRHN1SGtiNmV6VDhNUHI5UmZjWG5TQ3NRdjlXeHhOandZWUxPdHZRVkQvMWwyMgpIUHVXaFZSMFpoSzNKNThoUTFMVVdHeUVrUE1UV2Q4ekFPYlZrdnNNa09GTzlGVzV5cUZjZG01WTA4ZHBlT0M4Ck81ZitHb3NQQWdNQkFBRUNnZ0VBSTk2NE13WFVhMk9HMHhQTGhMZWdiVWpSbXR3eldmYzFMUUF2Z0JOS3FjcmIKczdSWWVIZllnQXZRNUJHQTZFMkVHMmVrS2R6SnVxem05MmpSaStBT1d1TlZUR1BuWlI1NVBDZXVmSC96MWhvSgpJVHh4S1h5ZW1PQmtzRW5QN2ROZ0lyMWpsYkl4ZUxEc0ZTQXR6YkovazQzUnlCWnJ5c2VIWWVVMHBaTGZnQW8wClRac2JvUnAvUkVGL2YvWXhkL1VQTUplOC9YbmhuUFgyQkl1M1pvNVViSll1MTZOa2Z5Q2NmRlJ0RTJMaGpwdmEKdXZ3ZUNtZ2VBSUJ1b21UR2JRek40R0doS1g5QzFZNWI1TnppUVpVOXF3S05iVmt4Q0dHYk5UZGlmQVF1dlJyQgpud3JVNUNvZVdNQi95ejBzaEM5Yk5raWRiYXNVTlgwQUlkekJhemx4TVFLQmdRRGloQkdXZncrYng4aDI1VzZsCmVOSmhETE4rYU5OTjkwa0g0TVRyM3dyZTR0NTNLZXZwOXdvMnFoaTMxWWNWWVFZNG95Rlk1a0FJM3k4RDEyMlcKc2Z2VFJkeVJZdHlBZjV1TWk5N0NmMWh1SXN6WWhkK2ErTVBQQWNaQjYydForTnFUbS9ZV0R4cW5sVFYvZ1UrZApQUHBMSFhneFlQc0VpbWFKZjhHRGVvdkZoUUtCZ1FEV2JtQmhadmM0WW41QURKSHhWOUt3ZFBoNjhvR1BHK0VoCjhySnp1OGVjTDMrMGx5eVBVZUsveVRKQ2RPQkgzQmpYR0pXOVFaMG0wbkZRU0QwWUpSMnlQVHAxcTdBTzEyWGoKUFZHS0UwS1JtWjhBNjRPamJha2k1czZybjM4MnBOdjRIdEtJdWtJNUN0MXFrYjdYbmhUODZXcFl4ZXJTZXVETgpNb3AxaXRBWWd3S0JnREh4a0xjd0dNN3VRK01EUDF3NHdab29aTU13ZGJheEdXY2xSZ1lEemEvTE1lWHdWbFhsCjVGaWROSW9FQ0o3TUg4VUpJdWNwRFdGblpFUmlrWVV5aFNYV040WE8ySE4wcjJWVHlhLzB5Qml3ajU0R0ZvRmkKN0RtT1dKcGNQL1U4aTJVVWREUDA3Sm1hcW9zTWhmTlRhSlI3VU84Q1JSYUJOWTZIbnJGUXFkVEpBb0dBRm8yagphM3MxODJOQW5pSDBVNnNHQ1BNMGsxSGdXSm41RXVZQTZQVk9LRnBDbDA3eks5dlQrcElCekVXWXRWWXI4cXV1ClRDcVRpZHJHZWtndXpOUlNqRVd1V1dRR2Iza3VTVGxRMHpIMVpYVC82VXZjRzV0VUY4eW8zaG8zZWhyYTIvejUKN2RHUlY0aVNBenh6RXlDWVdvVitYdm5xR2RzOHU3aGVJY0RJNUZzQ2dZRUFxS0xhb2V4eGp1S3J5VG1KdjlIRgpjb29LaHlTMXVvdGV1bkl4c3JURGg4emx3Ym40SDJJbFJNTThIZ3RmT3RoOTVYSUwvSVpQK1o2OTMzOEVoa2J5Cmt2OG1Zck9CVS8rSVlWY0wyQXpUUlJ6M0NvdnRWWlg3QzUrMVI5aTdWcEdSVFVhUldDQ1RKYzVsUFZ4ZG5vOXMKWHN3d0o0K2YwZWdDbmVRT2JkY3RhbTg9Ci0tLS0tRU5EIFBSSVZBVEUgS0VZLS0tLS0K");

    	$messageArray = @unserialize(base64_decode($messageArray));

		if (!is_array($messageArray)) {
			return null;
		}

    	$messageBase64 = '';
    	$cMessageArray = count($messageArray);
    	foreach ($messageArray as $pos => $message) {
    		$part = base64_decode($message['d']);
			// var_dump('decryptFromTransfer', $pos .'/' . ($cMessageArray - 1));

    		if (!$pos) {
    			if (self::verify($part, base64_decode($message['s']), $toPublicKey)) {
	    			$messageBase64 .= self::decryptFromPrivateKey($part, $fromPrivatekey);
    			} else {
    				var_dump('Signature ERROR !!!!! <--------------------');
    				return null;
    			}
    		} else {
    			$messageBase64 .= self::decryptFromPrivateKey($part, $fromPrivatekey);
    		}
    	}

    	// $encryptedMessage = base64_decode($messageBase64);
    	// $clearMessageBase64 = self::decryptFromPrivateKey($encryptedMessage, $fromPrivatekey);
    	$clearMessage = @unserialize($messageBase64);

    	return $clearMessage;
    }
}
