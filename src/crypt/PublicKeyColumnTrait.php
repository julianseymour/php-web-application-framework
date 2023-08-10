<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;

trait PublicKeyColumnTrait
{

	use MultipleColumnDefiningTrait;

	public function setPublicKey($value)
	{
		return $this->setColumnValue("publicKey", $value);
	}

	public function getPublicKey()
	{
		return $this->getColumnValue("publicKey");
	}

	public function hasPublicKey()
	{
		return $this->hasColumnValue("publicKey");
	}
}
