<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;

trait PublicKeyColumnTrait{

	use MultipleColumnDefiningTrait;

	public function setPublicKey(STRING $value):STRING{
		return $this->setColumnValue("publicKey", $value);
	}

	public function getPublicKey():string{
		return $this->getColumnValue("publicKey");
	}

	public function hasPublicKey():BOOL{
		return $this->hasColumnValue("publicKey");
	}
}
