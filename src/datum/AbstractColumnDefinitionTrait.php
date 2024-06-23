<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

trait AbstractColumnDefinitionTrait{
	
	/**
	 * specifies default value or expression to generate default value
	 *
	 * @var mixed
	 */
	protected $defaultValue;
	
	public function setDefaultValue($v){
		if($this->hasDefaultValue()){
			$this->release($this->defaultValue);
		}
		if($v === null){
			$this->setNullable(true);
		}
		return $this->defaultValue = $this->claim($v);
	}
	
	public function getDefaultValue(){
		if($this->hasDefaultValue()){
			return $this->defaultValue;
		}
		return null;
	}
	
	public function hasDefaultValue():bool{
		return $this->defaultValue !== null;
	}
	
	public function withDefaultValue($value){
		$this->setDefaultValue($value);
		return $this;
	}
	
	public function getDefaultValueString(){
		return $this->getDefaultValue();
	}
	
	public function setNullable(bool $value = true):bool{
		return $this->setFlag(COLUMN_FILTER_NULLABLE, $value);
	}
	
	public function isNullable():bool{
		return $this->getFlag(COLUMN_FILTER_NULLABLE);
	}
}