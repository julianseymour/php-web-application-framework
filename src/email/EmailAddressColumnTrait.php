<?php

namespace JulianSeymour\PHPWebApplicationFramework\email;

trait EmailAddressColumnTrait{

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
