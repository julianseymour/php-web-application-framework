<?php

namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ContextualTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ValuedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\ui\LabelGeneratorTrait;

class Choice extends Basic{

	use AllFlagTrait;
	use ArrayPropertyTrait;
	use ContextualTrait;
	use LabelGeneratorTrait;
	use ValuedTrait;

	protected $context;
	
	protected $labelString;

	protected $wrapperClass;

	public function __construct($value = null, $labelString = null, $select = false){
		$f = __METHOD__;
		$print = false;
		parent::__construct();
		if($value !== null){
			$this->setValue($value);
		}
		if($labelString !== null){
			$this->setLabelString($labelString);
		}
		if($select !== false){
			if(is_bool($select) && $select){
				$this->select();
			}elseif(gettype($select) === gettype($value) && $select === $value){
				$this->select();
			}
		}
		if($print){
			Debug::print("{$f} new choice {$value} => {$labelString}");
		}
	}

	public function dispose(bool $deallocate=false):void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		if($this->hasContext()){
			$this->releaseContext(false);
		}
		if($this->hasLabelString()){
			$this->release($this->labelString, $deallocate);
		}
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
		if($this->hasWrapperClass()){
			$this->release($this->wrapperClass, $deallocate);
		}
	}
	
	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"all",
			"selected"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"all",
			"selected"
		]);
	}
	
	public function setAttribute(string $key, $value){
		return $this->setArrayPropertyValue('attributes', $key, $value);
	}
	
	public function setAttributes(array $attr):array{
		foreach($attr as $key => $value){
			$this->setAttribute($key, $value);
		}
		return $attr;
	}
	
	public function hasAttributes():bool{
		return $this->hasArrayProperty('attributes');
	}
	
	public function getAttributes():array{
		$f = __METHOD__;
		if(!$this->hasAttributes()){
			Debug::error("{$f} no attributes");
		}
		return $this->getProperty('attributes');
	}
	
	public function setSelectedFlag(bool $value = true): bool{
		return $this->setFlag("selected", $value);
	}

	public function getSelectedFlag(): bool{
		return $this->getFlag("selected");
	}

	public function select(bool $value = true): Choice{
		$this->setSelectedFlag($value);
		return $this;
	}

	public function deselect(): Choice{
		$this->setSelectedFlag(false);
		return $this;
	}

	public function hasWrapperClass():bool{
		return !empty($this->wrapperClass);
	}

	public function getWrapperClass(){
		$f = __METHOD__;
		if(!$this->hasWrapperClass()){
			Debug::error("{$f} wrapper class is undefined");
		}
		return $this->wrapperClass;
	}

	public function setWrapperClass($class){
		if($this->hasWrapperClass()){
			$this->release($this->wrapperClass);
		}
		return $this->wrapperClass = $this->claim($class);
	}

	public function setLabelString($ls){
		$f = __METHOD__;
		if($this->hasLabelString()){
			$this->release($this->labelString);
		}
		return $this->labelString = $this->claim($ls);
	}

	public function hasLabelString(): bool{
		return isset($this->labelString);
	}

	public function getLabelString(){
		$f = __METHOD__;
		if(!$this->hasLabelString()){
			Debug::error("{$f} label string is undefined");
		}
		return $this->labelString;
	}
}
