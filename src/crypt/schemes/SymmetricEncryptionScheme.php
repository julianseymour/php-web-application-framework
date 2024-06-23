<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\CipherDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\NonceDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;

class SymmetricEncryptionScheme extends EncryptionScheme{

	public function generateComponents(?DataStructure $ds = null):array{
		$datum = $this->getColumn();
		$vn = $datum->getName();
		$cipher = new CipherDatum("{$vn}Cipher");
		// cipher->setEncryptionComponent(ENCRYPTION_COMPONENT_SYMMETRIC_CIPHER);
		$cipher->setSensitiveFlag($datum->getSensitiveFlag());
		$cipher->setOriginalDatumClass($datum->getClass());
		$cipher->setEncryptionScheme(static::class);
		$nonce = new NonceDatum("{$vn}AesNonce");
		// $nonce->setEncryptionComponent(ENCRYPTION_COMPONENT_AES_NONCE);
		if($datum->isNullable()){
			$cipher->setNullable(true);
			$nonce->setNullable(true);
		}
		return [
			$cipher,
			$nonce
		];
	}

	public static function encrypt(string $value, string $key, ?string $nonce = null):string{
		$f = __METHOD__;
		$print = false;
		if(!is_string($value)){
			$typeof = gettype($value);
			Debug::error("{$f} sodium_crypto_aead_xchacha20poly1305_ietf_encrypt() expects parameter 1 to be string, {$typeof} given");
			return null;
		}
		$length = strlen($nonce);
		$proper = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
		if($length !== $proper){
			Debug::error("{$f} nonce is incorrect length ({$length}, should be {$proper})");
		}elseif($print){
			Debug::print("{$f} encrypting value with hash " . sha1($value));
			Debug::print("{$f} ... using key with hash " . sha1($key));
			if($nonce !== null){
				Debug::print("{$f} ... and nonce with hash " . sha1($nonce));
			}
			// Debug::printStackTraceNoExit();
		}
		$cipher = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($value, '', $nonce, $key);
		if($print){
			$cleartext = static::decrypt($cipher, $key, $nonce);
			if($value !== $cleartext){
				Debug::error("{$f} decrypted value does not equal the original");
			}
		}
		return $cipher;
	}

	public function generateEncryptionKey(Datum $datum):string{
		return $this->extractTranscryptionKey($datum);
	}

	public function extractNonce(Datum $datum):?string{
		$ds = $datum->getDataStructure();
		$vn = $datum->getName();
		$index = "{$vn}AesNonce";
		return $ds->getColumnValue($index);
	}

	public function generateNonce(Datum $datum):?string{
		$f = __METHOD__;
		$print = false;
		$vn = $datum->getName();
		$ds = $datum->getDataStructure();
		$index = "{$vn}AesNonce";
		$proper = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
		if($ds->hasColumnValue($index)){
			if($print){
				Debug::print("{$f} data structure already has a value for column {$index}");
			}
			$nonce = $ds->getColumnValue($index);
		}else{
			if($print){
				Debug::print("{$f} about to generate a nonce of length {$proper}");
			}
			$nonce = $ds->setColumnValue($index, random_bytes($proper));
		}
		$length = strlen($nonce);
		if($length !== $proper){
			Debug::error("{$f} generated a nonce with incorrect length ({$length}, should be {$proper})");
		}elseif($print){
			Debug::print("{$f} returning nonce with hash " . sha1($nonce));
		}
		return $nonce;
	}

	protected function extractTranscryptionKey(Datum $datum):?string{
		$f = __METHOD__;
		$print = false;
		if($print){
			$vn = $datum->getName();
			Debug::print("{$f} about to extract transcryption key for datum \"{$vn}\"");
		}
		$ds = $datum->getDataStructure();
		$tkn = $datum->getTranscryptionKeyName();
		if($print){
			$cn = $datum->getName();
			$dsc = $ds->getClass();
			$dsk = $ds->getIdentifierValue();
			$did = $ds->getDebugId();
			Debug::print("{$f} about to extract transcryption key \"{$tkn}\" for column \"{$cn}\" of data structure of class \"{$dsc}\" with key \"{$dsk}\" and debug Id \"{$did}\"");
		}
		return $ds->getColumnValue($tkn);
	}

	public function extractEncryptionKey(Datum $datum):?string{
		return $this->extractTranscryptionKey($datum);
	}

	public function extractDecryptionKey(Datum $datum):?string{
		$f = __METHOD__;
		if(!$datum->hasDataStructure()){
			Debug::error("{$f} we are being asked to extract a transcryption key from an orphaned datum");
		}
		return $this->extractTranscryptionKey($datum);
	}

	public static function decrypt(string $cipher, string $key, ?string $nonce = null):?string{
		$f = __METHOD__;
		$print = false;
		if(empty($cipher)){
			Debug::error("{$f} cipher is undefined");
		}elseif(empty($key)){
			Debug::error("{$f} decryption key is undefined");
		}elseif(empty($nonce)){
			Debug::error("{$f} nonce is undefined");
		}elseif($print){
			Debug::print("{$f} cipher has hash " . sha1($cipher));
			Debug::print("{$f} nonce has hash " . sha1($nonce));
			Debug::print("{$f} key has hash " . sha1($key));
		}
		$clear = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($cipher, '', $nonce, $key);
		if($print){
			$length = strlen($clear);
			if($clear === null || $clear === "" || $length === 0){
				Debug::error("{$f} cleartext is null or empty string");
			}else{
				Debug::print("{$f} cleartext is length {$length} with hash " . sha1($clear));
			}
		}
		return $clear;
	}
}
