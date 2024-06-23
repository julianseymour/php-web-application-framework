<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

abstract class EditForeignDataStructuresEvent extends Event{

	public function __construct(string $name, string $when, ?array $properties = null){
		$f = __METHOD__;
		if(!isset($when)){
			Debug::error("{$f} when value is undefined");
		}elseif(!is_string($when)){
			Debug::error("{$f} when value is not a string");
		}
		$when = strtolower($when);
		switch($when){
			case CONST_BEFORE:
			case CONST_AFTER:
				break;
			default:
				Debug::error("{$f} invalid before/after property");
		}
		if(!isset($properties) || !is_array($properties) || empty($properties)){
			$properties = [
				'when' => $when
			];
		}else{
			$properties['when'] = $when;
		}
		parent::__construct($name, $properties);
	}
}
