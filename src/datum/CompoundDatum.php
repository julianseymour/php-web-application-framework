<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

abstract class CompoundDatum extends Datum{

	protected $components;

	public abstract function generateComponents();

	public function __construct(string $name){
		parent::__construct($name);
		$this->components = $this->generateComponents();
	}

	public function getComponents(){
		return $this->components;
	}

	public function hasComponent(string $component_name):bool{
		return is_array($this->components) && array_key_exists($component_name, $this->components) && is_object($this->components[$component_name]);
	}

	public function getComponent($component_name){
		$f = __METHOD__;
		if(!$this->hasComponent($component_name)){
			Debug::error("{$f} component \"{$component_name}\" is undefined");
		}
		return $this->components[$component_name];
	}

	public function parseValueFromSuperglobalArray($value){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function processInput($input){
		$f = __METHOD__;
		$vn = $this->getName();
		foreach($this->getComponents() as $component_name => $component){
			$component_input = $input->getComponent($component_name);
			$status = $component->processInput($component_input);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				$cvn = $component->getName();
				Debug::print("{$f} processInput for index \"{$cvn}\" returned error status \"{$err}\"");
			}
		}
		return SUCCESS;
	}

	public static function getTypeSpecifier():string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function getConstructorParams(): ?array{
		return [
			$this->getName()
		];
	}

	public function parseValueFromQueryResult($raw){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function getComponentValue($index){
		$f = __METHOD__;
		if(!$this->hasComponent($index)){
			Debug::error("{$f} component \"{$index}\" does not exist");
		}
		return $this->getComponent($index)->getValue();
	}

	public function setComponentValue(string $index, $value){
		$f = __METHOD__;
		if(!$this->hasComponent($index)){
			Debug::error("{$f} component at index \"{$index}\" does not exist");
		}
		return $this->getComponent($index)->setValue($value);
	}

	public function hasComponentValue(string $index):bool{
		return $this->hasComponent($index) && $this->getComponent($index)->hasValue();
	}
}
