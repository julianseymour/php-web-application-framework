<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait SignatureKeypairedTrait
{

	public abstract function getSignaturePublicKey();

	public abstract function getSignaturePrivateKey();

	public function verifySignedMessage($signature, $message): bool
	{
		$f = __METHOD__; //"KeypairedTrait(".static::getShortClass().")->verifySignedMessage()";
		$len = strlen(($signature));
		if ($len !== SODIUM_CRYPTO_SIGN_BYTES) {
			Debug::error("{$f} signature \"{$signature}\" is {$len} bytes, when it must be " . SODIUM_CRYPTO_SIGN_BYTES);
		}
		$spk = $this->getSignaturePublicKey();
		if ($spk === null) {
			Debug::error("{$f} signature public key is null");
		}
		$len2 = strlen($spk);
		if ($len2 !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
			Debug::error("{$f} signature public key is incorrect length ({$len2}, should be " . SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES . ")");
		}
		return sodium_crypto_sign_verify_detached($signature, $message, $spk);
	}

	public function signMessage($sign_me)
	{
		$f = __METHOD__; //"KeypairedTrait(".static::getShortClass().")->signMessage()";
		$signaturePrivateKey = $this->getSignaturePrivateKey();
		$len = strlen($signaturePrivateKey);
		if ($len !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
			Debug::error("{$f} signature private key is {$len} bytes, should be " . SODIUM_CRYPTO_SIGN_SECRETKEYBYTES);
			return null;
		}
		$signature = sodium_crypto_sign_detached($sign_me, $signaturePrivateKey);
		$length = strlen($signature);
		$shouldbe = SODIUM_CRYPTO_SIGN_BYTES;
		if ($length !== $shouldbe) {
			Debug::error("{$f} signature is wrong length ({$length}, should be {$shouldbe})");
		}
		return $signature;
	}
}