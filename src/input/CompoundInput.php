<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\element\CompoundElement;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\NameAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ValueAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\form\FormTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;
use JulianSeymour\PHPWebApplicationFramework\validate\MultipleValidatorsTrait;

abstract class CompoundInput extends CompoundElement implements InputlikeInterface{

	use ColumnNameTrait;
	use FormTrait;
	use MultipleValidatorsTrait;
	use NameAttributeTrait;
	use ValueAttributeTrait;

	public function dispose(bool $deallocate=false): void{
		if($this->hasForm()){
			$this->releaseForm($deallocate);
		}
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		if($this->hasColumnName()){
			$this->release($this->columnName, $deallocate);
		}
		$this->release($this->propertyTypes, $deallocate);
	}
	
	public function configure(?AjaxForm $form=null): int{
		if(!$this->hasComponents()){
			$this->generateComponents();
		}
		$components = $this->getComponents();
		if(empty($components)){
			return SUCCESS;
		}
		foreach($components as $c){
			if(method_exists($c, "configure")){
				$c->configure($form);
			}
		}
		return SUCCESS;
	}

	public function subindexNameAttribute($super_index){
		$f = __METHOD__;
		$print = false;
		$components = $this->getComponents();
		if(!empty($components)){
			foreach($components as $component){
				if(!$component instanceof InputlikeInterface){
					continue;
				}
				$newname = $component->subindexNameAttribute($super_index);
				if($print){
					Debug::print("{$f} reindexed input as \"{$newname}\"");
				}
			}
		}else{ // if($print){
			Debug::error("{$f} no components");
		}
	}

	public function processArray(array $arr): int{
		$f = __METHOD__;
		$print = false;
		// Debug::printArray($arr);
		// ErrorMessage::unimplemented($f);
		$name = $this->getNameAttribute();
		if(array_key_exists($name, $arr)){
			$this->setValueAttribute($arr[$name]);
		}elseif($print){
			Debug::print("{$f} name \"{$name}\" is not allowed");
			Debug::printArray($arr);
		}
		return SUCCESS;
	}

	public function negotiateValue(Datum $column){
		return $this->getValueAttribute();
	}

	public function bindContext($context){
		$f = __METHOD__;
		$print = false;
		$ret = parent::bindContext($context);
		if($context instanceof Datum){
			$cn = $context->getName();
			if($print){
				Debug::print("{$f} setting column name to \"{$cn}\"");
			}
			$this->setColumnName($this->setNameAttribute($cn));
		}elseif($print){
			Debug::print("{$f} context is not a datum");
		}
		return $ret;
	}
}
