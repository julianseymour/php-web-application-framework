<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

use JulianSeymour\PHPWebApplicationFramework\crypt\EncryptionComponentDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\NonceDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

class SessionRecoveryEncryptionScheme extends SymmetricEncryptionScheme
{

	public function generateComponents(?DataStructure $ds = null): array
	{
		$components = parent::generateComponents();
		$datum = $this->getColumn();
		$vn = $datum->getColumnName();
		// $cipher = new CipherDatum("{$vn}_cipher", "{$short}_cipher");
		// $cipher->setEncryptionComponent(ENCRYPTION_COMPONENT_DETERMINISTIC_KEY_CIPHER);
		$server_secret = new EncryptionComponentDatum("{$vn}_serverSecret");
		// $server_secret->setEncryptionComponent(ENCRYPTION_COMPONENT_SERVER_SECRET);
		// $aes_nonce = new NonceDatum("{$vn}_aesNonce", "{$short}_aes_nonce");
		// $aes_nonce->setEncryptionComponent(ENCRYPTION_COMPONENT_AES_NONCE);
		$argon_nonce = new NonceDatum("{$vn}_argonNonce");
		// $argon_nonce->setEncryptionComponent(ENCRYPTION_COMPONENT_ARGON_NONCE);
		array_push($components, $server_secret, $argon_nonce);
		return $components;
	}

	protected function extractTranscryptionKey($datum)
	{
		$f = __METHOD__; //SessionRecoveryEncryptionScheme::getShortClass()."(".static::getShortClass().")->extractTranscryptionKey()";
		$vn = $datum->getColumnName();
		$xor = new XorEncryptionScheme();
		$ds = $datum->getDataStructure();
		$secret = $ds->getColumnValue("{$vn}_serverSecret");
		$secret64 = base64_encode($secret);
		$hash = $ds->getArgonHash();
		$hash64 = base64_encode($hash);
		$key = $xor->decrypt($secret, $hash);
		$key64 = base64_encode($key);
		$arr = [
			"secret64" => $secret64,
			"hash64" => $hash64,
			"key64" => $key64
		];
		// Debug::printArray($arr);
		// Debug::print("{$f} base64-encoded key is \"{$key64}\"");
		return $key;
	}
}
