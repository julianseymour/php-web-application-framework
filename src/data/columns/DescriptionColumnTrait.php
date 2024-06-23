<?php

namespace JulianSeymour\PHPWebApplicationFramework\data\columns;

trait DescriptionColumnTrait{

	public function setDescription($value){
		return $this->setColumnValue("description", $value);
	}

	public function getDescription(){
		return $this->getColumnValue("description");
	}

	public function hasDescription():bool{
		return $this->hasColumnValue("description");
	}
}
