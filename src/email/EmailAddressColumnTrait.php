<?php

namespace JulianSeymour\PHPWebApplicationFramework\email;

use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;

trait EmailAddressColumnTrait{

	use MultipleColumnDefiningTrait;

	public function hasEmailAddress():bool{
		return $this->hasColumnValue("emailAddress");
	}

	public function getEmailAddress():string{
		return $this->getColumnValue("emailAddress");
	}

	public function setEmailAddress(string $value):string{
		return $this->setColumnValue("emailAddress", $value);
	}
}
