<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;
use Exception;

trait KeypairColumnsTrait
{

	use KeypairedTrait;
	use MultipleColumnDefiningTrait;

	public function hasPublicKey()
	{
		return $this->hasColumnValue("publicKey");
	}

	public function getPublicKey()
	{
		$f = __METHOD__; //"KeypairColumnsTrait(".static::getShortClass().")::getPublicKey()";
		try {
			$pk = $this->getColumnValue('publicKey');
			if (! isset($pk)) {
				Debug::error("{$f} public key is undefined");
			}
			return $pk;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setPublicKey($key)
	{
		$f = __METHOD__; //"KeypairColumnsTrait(".static::getShortClass().")->setPublicKey()";
		try {
			$status = SodiumCryptoBoxPublicKeyDatum::validateStatic($key);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} {$err}");
				return null;
			}
			return $this->setColumnValue('publicKey', $key);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setPrivateKey($privateKey)
	{
		return $this->setColumnValue('privateKey', $privateKey);
	}

	public function getPrivateKey()
	{
		$f = __METHOD__; //"KeypairColumnsTrait(".static::getShortClass().")->getPrivateKey()";
		$print = false;
		if ($print) {
			$pk = $this->getColumnValue("privateKey");
			if ($pk == null) {
				$class = $this->getClass();
				Debug::warning("{$f} private key is null for {$class} with key \"" . $this->getIdentifierValue() . "\"");
			}
		}
		return $this->getColumnValue("privateKey");
	}

	public function hasPrivateKey()
	{
		return $this->hasColumnValue("privateKey");
	}
}
