<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt;


trait PublicKeyColumnTrait{

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
