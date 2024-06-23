<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\CipherDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;

class XorEncryptionScheme extends EncryptionScheme{

	public function generateComponents(?DataStructure $ds = null): array{
		$datum = $this->getColumn();
		$vn = $datum->getName();
		$cipher = new CipherDatum("{$vn}Cipher");
		$cipher->setNullable(false);
		$cipher->setNeverLeaveServer(true);
		$cipher->setUserWritableFlag(true);
		$cipher->setEncryptionScheme(static::class);
		if($datum->isNullable()){
			$cipher->setNullable(true);
		}
		return [
			$cipher
		];
	}

	public static function transcrypt(string $value, string $key):?string{
		$f = __METHOD__;
		$value_length = strlen($value);
		$key_length = strlen($key);
		if($value_length !== $key_length){
			Debug::error("{$f} cipher/cleartext length ({$value_length}) and key length ({$key_length}) are unequal");
		}
		return $value ^ $key;
	}

	public function generateEncryptionKey(Datum $datum){
		return $this->extractTranscryptionKey($datum);
	}

	public final function generateNonce(Datum $datum):?string{
		return null;
	}

	public static function encrypt(string $value, string $key, ?string $nonce = null):string{
		return static::transcrypt($value, $key);
	}

	public static function decrypt(string $cipher, string $key, ?string $nonce = null):?string{
		return static::transcrypt($cipher, $key);
	}

	public function extractTranscryptionKey(Datum $datum):?string{
		$ds = $datum->getDataStructure();
		return $ds->getColumnValue($datum->getTranscryptionKeyName());
	}

	public function extractDecryptionKey(Datum $datum):?string{
		return $this->extractTranscryptionKey($datum);
	}

	public function extractEncryptionKey(Datum $datum):?string{
		return $this->extractTranscryptionKey($datum);
	}

	public final function extractNonce(Datum $datum):?String{
		return null;
	}
}
