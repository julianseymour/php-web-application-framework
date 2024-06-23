<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

ErrorMessage::deprecated(__FILE__);

trait DeallocatingFlagTrait{
	
	use FlagBearingTrait;
	
	public function setDeallocatingFlag(bool $value=true):bool{
		return $this->setFlag("deallocating", $value);
	}
	
	public function getDeallocatingFlag():bool{
		return $this->getFlag("deallocating");
	}
	
	public function deallocating(bool $value=true){
		$this->setDeallocatingFlag($value);
		return $this;
	}
}