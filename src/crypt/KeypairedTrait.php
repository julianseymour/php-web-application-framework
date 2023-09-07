<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\AsymmetricEncryptionScheme;
use Exception;

trait KeypairedTrait{

	public abstract function getPublicKey():string;

	public abstract function getPrivateKey():?string;

	protected abstract function nullPrivateKeyHook(): int;

	public function encrypt(string $encrypt_me):string{
		$f = __METHOD__;
		$print = false;
		$scheme = new AsymmetricEncryptionScheme();
		$pk = $this->getPublicKey();
		$cipher = $scheme->encrypt($encrypt_me, $pk);
		if ($print) {
			$cleartext = $this->decrypt($cipher);
			$hash = sha1($encrypt_me);
			Debug::print("{$f} cleartext \"{$encrypt_me}\" hash is \"{$hash}\"");
			$hash = sha1($pk);
			Debug::print("{$f} public key hash is \"{$hash}\"");
			$hash = sha1($cipher);
			Debug::print("{$f} cipher hash is \"{$hash}\"");
			if ($cleartext !== $encrypt_me) {
				Debug::error("{$f} decryption failed");
			}
			Debug::print("{$f} decryption successful");
		}
		return $cipher;
	}

	public function decrypt(string $encrypted):?string{
		$f = __METHOD__;
		try {
			$print = false;
			$privateKey = $this->getPrivateKey();
			$length = strlen($privateKey);
			if ($length !== SODIUM_CRYPTO_BOX_SECRETKEYBYTES) {
				Debug::error("{$f} secret key is {$length} bytes, should be " . SODIUM_CRYPTO_BOX_SECRETKEYBYTES);
			}
			$publicKey = $this->getPublicKey();
			if ($print) {
				$hash = sha1($encrypted);
				Debug::print("{$f} cipher hash is \"{$hash}\"");
				if ($privateKey == null) {
					Debug::print("{$f} private key returned null");
				} else {
					$hash = sha1($privateKey);
					Debug::print("{$f} private key hash is \"{$hash}\"");
				}
				if ($publicKey == null) {
					Debug::print("{$f} public key is null");
				} else {
					$hash = sha1($publicKey);
					Debug::print("{$f} public key hash is \"{$hash}\"");
				}
			}
			$keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey($privateKey, $publicKey);
			if ($print) {
				if ($keypair == null) {
					Debug::print("{$f} keypair is null");
				} else {
					$hash = sha1($keypair);
					Debug::print("{$f} keypair hash is \"{$hash}\"");
				}
			}
			// $scheme = new AsymmetricEncryptionScheme();
			$cleartext = AsymmetricEncryptionScheme::decrypt($encrypted, $keypair);
			if ($print) {
				$hash = sha1($cleartext);
				Debug::print("{$f} cleartext hash is \"{$hash}\"");
			}
			return $cleartext;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getKeypair():string{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} entered; about to get private key");
			}
			$priv = $this->getPrivateKey();
			if ($priv == null) {
				session_destroy();
				Debug::error("{$f} private key is null");
				$this->nullPrivateKeyHook();
			}
			$pub = $this->getPublicKey();
			if ($pub == null) {
				Debug::error("{$f} public key returned null");
			}
			$kp = sodium_crypto_box_keypair_from_secretkey_and_publickey($priv, $pub);
			// sodium_memzero($priv);
			if ($kp == null) {
				Debug::error("{$f} keypair returned null");
			}
			return $kp;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
