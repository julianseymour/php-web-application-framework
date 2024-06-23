<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\style\selector\ElementSelector;

abstract class CheckedInput extends InputElement{

	use RequiredAttributeTrait;

	public abstract static function getTypeAttributeStatic(): string;

	public function getCheckedAttribute(){
		return $this->getAttribute("checked");
	}

	public static function checked(): ElementSelector{
		return ElementSelector::element("input")->attribute("type", static::getTypeAttributeStatic())->checked();
	}

	public function setCheckedAttribute($value = "checked"){
		return $this->setAttribute("checked", $value);
	}

	public function hasCheckedAttribute():bool{
		return $this->hasAttribute("checked");
	}

	public final function getTypeAttribute(): string{
		return static::getTypeAttributeStatic();
	}

	public function getAllowEmptyInnerHTML():bool{
		return true;
	}

	public function check():CheckedInput{
		$this->setCheckedAttribute("checked");
		return $this;
	}

	public function select(bool $value = true):CheckedInput{
		if($value){
			return $this->check();
		}
		return $this->uncheck();
	}

	public function deselect():CheckedInput{
		return $this->uncheck();
	}

	public function uncheck():CheckedInput{
		$this->ejectCheckedAttribute();
		return $this;
	}

	public function ejectCheckedAttribute(){
		return $this->ejectAttribute("checked");
	}

	public function processArray(array $arr): int{
		$f = __METHOD__;
		$print = false;
		$name = $this->getNameAttribute();
		if(!array_key_exists($name, $arr)){
			if($print){
				Debug::print("{$f} value not found; setting value attribute to false");
			}
			$this->setValueAttribute(false);
			return SUCCESS;
		}elseif($print){
			Debug::print("{$f} returning parent function");
		}
		return parent::processArray($arr);
	}

	public function removeCheckedAttribute(){
		return $this->removeAttribute("checked");
	}
}
