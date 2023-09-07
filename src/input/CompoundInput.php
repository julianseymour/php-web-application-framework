<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\element\CompoundElement;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\NameAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ValueAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\form\FormTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;
use JulianSeymour\PHPWebApplicationFramework\validate\MultipleValidatorsTrait;

abstract class CompoundInput extends CompoundElement implements InputInterface{

	use ColumnNameTrait;
	use FormTrait;
	use MultipleValidatorsTrait;
	use NameAttributeTrait;
	use ValueAttributeTrait;

	public function configure(AjaxForm $form): int{
		return SUCCESS;
	}

	public function subindexNameAttribute($super_index){
		$f = __METHOD__;
		$print = false;
		$components = $this->getComponents();
		if (! empty($components)) {
			foreach ($components as $component) {
				if (! $component instanceof InputInterface) {
					continue;
				}
				$newname = $component->subindexNameAttribute($super_index);
				if ($print) {
					Debug::print("{$f} reindexed input as \"{$newname}\"");
				}
			}
		} else { // if($print){
			Debug::error("{$f} no components");
		}
	}

	public function processArray(array $arr): int{
		$f = __METHOD__;
		$print = false;
		// Debug::printArray($arr);
		// ErrorMessage::unimplemented($f);
		$name = $this->getNameAttribute();
		if (array_key_exists($name, $arr)) {
			$this->setValueAttribute($arr[$name]);
		} elseif ($print) {
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
		if ($context instanceof Datum) {
			$cn = $context->getName();
			if ($print) {
				Debug::print("{$f} setting column name to \"{$cn}\"");
			}
			$this->setColumnName($this->setNameAttribute($cn));
		} elseif ($print) {
			Debug::print("{$f} context is not a datum");
		}
		return $ret;
	}
}
