<?php

namespace JulianSeymour\PHPWebApplicationFramework\style;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayKeyProviderInterface;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\ValuedElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class CssProperty extends ValuedElement implements ArrayKeyProviderInterface{

	use NamedTrait;

	protected $propertyValue;

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"important"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"important"
		]);
	}
	
	public function getArrayKey(int $count){
		return $this->getName();
	}

	public function __construct($name = null, $value = null){
		$this->setPropertyValue(null);
		parent::__construct();
		if(isset($name) && $name != ""){
			$this->setName($name);
		}
		if(isset($value) && $value != ""){
			$this->setPropertyValue($value);
		}
	}

	public function setPropertyValue($value){
		if($this->hasPropertyValue()){
			$this->release($this->propertyValue);
		}
		return $this->propertyValue = $this->claim($value);
	}

	public function hasPropertyValue():bool{
		return isset($this->propertyValue);
	}

	public function getPropertyValue(){
		$f = __METHOD__;
		if(!$this->hasPropertyValue()){
			return null;
			Debug::error("{$f} property value is undefined");
		}
		return $this->propertyValue;
	}

	public function setValueAttribute($value){
		$f = __METHOD__;
		Debug::error("{$f} because of templates, this function can no longer be used; call setPropertyValue instead");
	}

	public function getPropertyName(){
		Debug::printStackTraceNoExit(ErrorMessage::getResultMessage(ERROR_DEPRECATED));
		return $this->getName();
	}

	public static function getElementTagStatic(): string{
		ErrorMessage::unimplemented(__METHOD__);
	}

	public function getValueAttribute(){
		$f = __METHOD__;
		Debug::error("{$f} because of templates, this function has been deprecated; call getPropertyValue instead");
	}

	public function setImportant(bool $important = true):bool{
		return $this->setFlag("important", $important);
	}

	public function isImportant():bool{
		return $this->getFlag("important");
	}

	public function getValueString():string{
		$string = $this->getPropertyValue();
		if($this->isImportant()){
			$string .= " !important";
		}
		return $string;
	}

	public function echo(bool $destroy = false):void{
		echo $this->getName().":".$this->getValueString().";";
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->name, $deallocate);
		$this->release($this->propertyValue, $deallocate);
	}
}
