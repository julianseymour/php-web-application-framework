<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;
use Exception;

trait SignatureKeypairColumnsTrait
{

	use SignatureKeypairedTrait;
	use MultipleColumnDefiningTrait;

	public function getSignaturePublicKey()
	{
		$f = __METHOD__; //"SignatureKeypairColumnsTrait(".static::getShortClass().")->getSignaturePublicKey()";
		try{
			$spk = $this->getColumnValue('signaturePublicKey');
			if(! isset($spk)) {
				Debug::error("{$f} signature public key is undefined");
			}
			$len2 = strlen($spk);
			if($len2 !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
				Debug::error("{$f} signature public key is incorrect length ({$len2}, should be " . SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES . ")");
			}
			return $spk;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function setSignaturePrivateKey($spk)
	{
		$f = __METHOD__; //"SignatureKeypairColumnsTrait(".static::getShortClass().")->setSignaturePrivateKey()";
		$len = strlen($spk);
		if($len !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
			Debug::error("{$f} signature private key is {$len} bytes, should be " . SODIUM_CRYPTO_SIGN_SECRETKEYBYTES);
			return null;
		}
		return $this->setColumnValue("signaturePrivateKey", $spk);
	}

	public function getSignaturePrivateKey()
	{
		return $this->getColumnValue("signaturePrivateKey");
	}

	public function hasSignaturePrivateKey(): bool
	{
		return $this->hasColumnValue("signaturePrivateKey");
	}

	public function getSignatureKeypair()
	{
		return sodium_crypto_sign_seed_keypair($this->getSignaturePrivateKey());
	}

	public function setSignaturePublicKey($value)
	{
		return $this->setColumnValue("signaturePublicKey", $value);
	}
}
