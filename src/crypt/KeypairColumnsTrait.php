<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;
use Exception;

trait KeypairColumnsTrait{

	use KeypairedTrait;
	use MultipleColumnDefiningTrait;

	public function hasPublicKey():bool{
		return $this->hasColumnValue("publicKey");
	}

	public function getPublicKey():string{
		$f = __METHOD__;
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

	public function setPublicKey(string $key):string{
		$f = __METHOD__;
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

	public function setPrivateKey(string $privateKey):string{
		return $this->setColumnValue('privateKey', $privateKey);
	}

	public function getPrivateKey():?string{
		$f = __METHOD__;
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

	public function hasPrivateKey():bool{
		return $this->hasColumnValue("privateKey");
	}
}
