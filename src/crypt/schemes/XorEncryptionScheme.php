<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\CipherDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

class XorEncryptionScheme extends EncryptionScheme
{

	public function generateComponents(?DataStructure $ds = null): array
	{
		$datum = $this->getColumn();
		$vn = $datum->getColumnName();
		$cipher = new CipherDatum("{$vn}_cipher");
		$cipher->setNullable(false);
		$cipher->setNeverLeaveServer(true);
		$cipher->setUserWritableFlag(true);
		$cipher->setEncryptionScheme(static::class);
		return [
			$cipher
		];
	}

	public static function transcrypt($value, $key)
	{
		$f = __METHOD__; //XorEncryptionScheme::getShortClass()."(".static::getShortClass().")->transcrypt()";
		$value_length = strlen($value);
		$key_length = strlen($key);
		if ($value_length !== $key_length) {
			Debug::error("{$f} cipher/cleartext length ({$value_length}) and key length ({$key_length}) are unequal");
		}
		return $value ^ $key;
	}

	public function generateEncryptionKey($datum)
	{
		return $this->extractTranscryptionKey($datum);
		// $ds = $datum->getDataStructure();
		// return $ds->getColumnValue($datum->getTranscryptionKeyName()); //extractTranscryptionKey($datum->getColumnName());
	}

	public final function generateNonce($datum)
	{
		return null;
	}

	public static function encrypt($value, $key, $nonce = null)
	{
		return static::transcrypt($value, $key);
	}

	public static function decrypt($cipher, $key, $nonce = null)
	{
		return static::transcrypt($cipher, $key);
	}

	public function extractTranscryptionKey($datum)
	{
		$ds = $datum->getDataStructure();
		return $ds->getColumnValue($datum->getTranscryptionKeyName()); // extractTranscryptionKey($datum->getColumnName());
	}

	public function extractDecryptionKey($datum)
	{
		return $this->extractTranscryptionKey($datum);
	}

	public function extractEncryptionKey($datum)
	{
		return $this->extractTranscryptionKey($datum);
	}

	public final function extractNonce($datum)
	{
		return null;
	}
}
