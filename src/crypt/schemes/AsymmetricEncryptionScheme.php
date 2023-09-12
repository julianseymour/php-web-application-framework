<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\CipherDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use Exception;

class AsymmetricEncryptionScheme extends EncryptionScheme{

	public function generateComponents(?DataStructure $ds = null): array{
		$datum = $this->getColumn();
		$vn = $datum->getName();
		$my_cipher = new CipherDatum("{$vn}Cipher");
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

	public static function encrypt(string $value, string $key, ?string $nonce = null):string{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::printStackTraceNoExit("{$f} entered. Encrypting {$value}");
		}
		return sodium_crypto_box_seal($value, $key);
	}

	public static function decrypt(string $cipher, string $keypair, ?string $nonce = null):?string{
		return sodium_crypto_box_seal_open($cipher, $keypair);
	}

	public function extractDecryptionKey(Datum $datum):?string{
		$f = __METHOD__;
		$print = false;
		if($datum->hasDecryptionKeyName()) {
			$dcn = $datum->getDecryptionKeyName();
			if($print) {
				Debug::print("{$f} decryption key is \"{$dcn}\"");
			}
			$ds = $datum->getDataStructure();
			return $ds->getColumnValue($dcn);
		}elseif($print) {
			$cn = $datum->getName();
			Debug::print("{$f} datum \"{$cn}\" does not name a decryption key; returning current user keypair");
		}
		return user()->getKeypair();
	}

	public final function extractNonce(Datum $datum):?string{
		return null;
	}

	public final function generateNonce(Datum $datum):?string{
		return null;
	}

	protected function extractPublicKey(Datum $datum):string{
		$f = __METHOD__;
		try{
			$print = false;
			$ds = $datum->getDataStructure();
			if($ds instanceof UserData) {
				if($print) {
					Debug::print("{$f} data structure is a user");
				}
				if($ds instanceof PlayableUser) {
					$user = $ds; // user();'
				}else{
					$user = user();
				}
			}else{
				if($print) {
					Debug::print("{$f} data structure is not a user");
				}
				$user = $ds->getUserData();
			}
			if($user instanceof UserData && ! $user instanceof PlayableUser) {
				Debug::error("{$f} user data is not playable");
			}
			return $user->getPublicKey();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function generateEncryptionKey(Datum $datum):string{
		return $this->extractPublicKey($datum);
	}

	public function extractEncryptionKey(Datum $datum):?string{
		return $this->extractPublicKey($datum);
	}
}
