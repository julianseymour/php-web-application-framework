<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\CipherDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use Exception;

class AsymmetricEncryptionScheme extends EncryptionScheme
{

	public function generateComponents(?DataStructure $ds = null): array
	{
		$datum = $this->getColumn();
		$vn = $datum->getColumnName();
		$my_cipher = new CipherDatum("{$vn}_cipher");
		$my_cipher->setSensitiveFlag($datum->getSensitiveFlag());
		$my_cipher->setOriginalDatumClass($datum->getClass());
		$my_cipher->setEncryptionScheme(static::class);
		if($datum->isNullable()){
			$my_cipher->setNullable(true);
		}
		return [
			$my_cipher
		];
	}

	public static function encrypt($value, $key, $nonce = null)
	{
		$f = __METHOD__; //AsymmetricEncryptionScheme::getShortClass()."(".static::getShortClass().")::encrypt()";
		$print = false;
		if($print){
			Debug::printStackTraceNoExit("{$f} entered. Encrypting {$value}");
		}
		return sodium_crypto_box_seal($value, $key);
	}

	public static function decrypt($cipher, $keypair, $nonce = null)
	{
		return sodium_crypto_box_seal_open($cipher, $keypair);
	}

	public function extractDecryptionKey($datum)
	{
		$f = __METHOD__; //AsymmetricEncryptionScheme::getShortClass()."(".static::getShortClass().")->extractDecryptionKey()";
		$print = false;
		if ($datum->hasDecryptionKeyName()) {
			$dcn = $datum->getDecryptionKeyName();
			if ($print) {
				Debug::print("{$f} decryption key is \"{$dcn}\"");
			}
			$ds = $datum->getDataStructure();
			return $ds->getColumnValue($dcn);
		} elseif ($print) {
			$cn = $datum->getColumnName();
			Debug::print("{$f} datum \"{$cn}\" does not name a decryption key; returning current user keypair");
		}
		return user()->getKeypair();
	}

	public final function extractNonce($datum)
	{
		return null;
	}

	public final function generateNonce($datum)
	{
		return null;
	}

	protected function extractPublicKey($datum)
	{
		$f = __METHOD__; //AsymmetricEncryptionScheme::getShortClass()."(".static::getShortClass().")->extractPublicKey()";
		try {
			$print = false;
			$ds = $datum->getDataStructure();
			if ($ds instanceof UserData) {
				if ($print) {
					Debug::print("{$f} data structure is a user");
				}
				if ($ds instanceof PlayableUser) {
					$user = $ds; // user();'
				} else {
					$user = user();
				}
			} else {
				if ($print) {
					Debug::print("{$f} data structure is not a user");
				}
				$user = $ds->getUserData();
			}
			if ($user instanceof UserData && ! $user instanceof PlayableUser) {
				Debug::error("{$f} user data is not playable");
			}
			return $user->getPublicKey();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function generateEncryptionKey($datum)
	{
		return $this->extractPublicKey($datum);
	}

	public function extractEncryptionKey($datum)
	{
		return $this->extractPublicKey($datum);
	}
}
