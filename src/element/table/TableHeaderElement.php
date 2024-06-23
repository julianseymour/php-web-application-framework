<?php

namespace JulianSeymour\PHPWebApplicationFramework\element\table;

class TableHeaderElement extends TableDataElement{

	public static function getElementTagStatic(): string{
		return "th";
	}

	public function setScopeAttribute($value){
		return $this->setAttribute("scope", $value);
	}

	public function hasScopeAttribute():bool{
		return $this->hasAttribute("scope");
	}

	public function getScopeAttribute(){
		return $this->getAttribute("scope");
	}

	public function removeScopeAttribute(){
		return $this->removeAttribute("scope");
	}
}
