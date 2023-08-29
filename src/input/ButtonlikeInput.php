<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use function JulianSeymour\PHPWebApplicationFramework\directive;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

abstract class ButtonlikeInput extends InputElement{

	public function processArray(array $arr): int{
		$f = __METHOD__;
		$print = false;
		$directive = directive();
		$name = $this->getColumnName();
		if (! is_array($arr)) {
			$gottype = is_object($arr) ? $arr->getClass() : gettype($arr);
			Debug::error("{$f} array is a {$gottype}");
		} elseif (! array_key_exists("directive", $arr)) {
			Debug::printArray($arr);
			Debug::error("{$f} directive was not posted");
		} elseif (! is_array($arr['directive'])) {
			if(is_string($directive)){
				if($print){
					Debug::print("{$f} directive is a string");
				}
				return STATUS_UNCHANGED;
			}else{
				Debug::print($arr['directive']);
				Debug::error("{$f} directive must be an array to infer value from submit button");
			}
		} elseif (! array_key_exists($directive, $arr['directive'])) {
			Debug::print($arr['directive']);
			Debug::error("{$f} key \"{$directive}\" is not part of the directive array");
		} elseif (! is_array($arr['directive'][$directive])) {
			Debug::printArray($arr);
			$offender = $arr['directive'][$directive];
			$gottype = is_object($offender) ? $offender->getClass() : gettype($offender);
			Debug::error("{$f} array[directive][{$directive}] is a {$gottype}");
		} elseif (! array_key_exists($name, $arr['directive'][$directive])) {
			Debug::printArray($arr['directive'][$directive]);
			Debug::error("{$f} this button's name attribute was not posted");
		}elseif($print){
			Debug::print("{$f} input parameter is an array. Directive is present in the array. The value at directive is a nested array. The value \"{$directive}\" is present in the array at the index 'directive'. The value at arr[directive][{$directive}] is an array. The index \"{$name}\" is present at arr[directive][{$directive}], and is \"{$arr['directive'][$directive][$name]}\".");
		}
		$key = $arr["directive"][$directive][$name];
		if (empty($key)) {
			Debug::error("{$f} nothing to process");
		} elseif ($print) {
			Debug::print("{$f} setting value to \"{$key}\"");
		}
		$this->setValueAttribute($key);
		return SUCCESS;
	}

	public function getDefaultOnClickAttribute(){
		return "AjaxForm.appendSubmitterName(event, this)";
	}
}
