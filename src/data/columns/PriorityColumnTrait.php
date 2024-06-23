<?php

namespace JulianSeymour\PHPWebApplicationFramework\data\columns;

trait PriorityColumnTrait{

	public function getPriority(){
		return $this->getColumnValue("priority");
	}

	public function hasPriority(): bool{
		return $this->hasColumnValue("priority");
	}

	public function setPriority($value){
		return $this->setColumnValue("priority", $value);
	}

	public function ejectPriority(){
		return $this->ejectColumnValue("priority");
	}
}
