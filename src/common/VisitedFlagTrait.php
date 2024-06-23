<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

trait VisitedFlagTrait{
	
	public function setVisitedFlag(bool $value=true):bool{
		return $this->setFlag("visited", $value);
	}
	
	public function getVisitedFlag():bool{
		return $this->getFlag("visited");
	}
	
	public function visit(bool $value=true){
		$this->setVisitedFlag($value);
		return $this;
	}
}
