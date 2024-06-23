<?php

namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

use JulianSeymour\PHPWebApplicationFramework\command\input\SetInputValueCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ValueAttributeTrait{

	public abstract function removeAttribute(string $name, bool $deallocate=false);

	public function getValueAttribute(){
		return $this->getAttribute("value");
	}

	public function setValueAttribute($value){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} assigning value \"{$value}\"");
		}
		return $this->setAttribute("value", $value);
	}

	public function hasValueAttribute():bool{
		return $this->hasAttribute("value");
	}

	public function withValueAttribute($value){
		$this->setValueAttribute($value);
		return $this;
	}

	public function setValueAttributeCommand($value): SetInputValueCommand{
		return new SetInputValueCommand($this, $value);
	}

	public function removeValueAttribute(){
		return $this->removeAttribute("value");
	}
}
