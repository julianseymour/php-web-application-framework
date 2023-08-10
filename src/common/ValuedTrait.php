<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ValuedTrait
{

	protected $value;

	public function getValue()
	{
		$f = __METHOD__; //"ValuedTrait(".static::getShortClass().")->getValue()";
		if (! $this->hasValue()) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} value is undefined; declared {$decl}");
		}
		return $this->value;
	}

	public function setValue($value)
	{
		$f = __METHOD__; //"ValuedTrait(".static::getShortClass().")->setValue()";
		$print = false; // $this instanceof SetInputValueCommand;
		if ($print) {
			if (is_string($value)) {
				Debug::print("{$f} value is \"{$value}\"");
			} else {
				$gottype = gettype($value);
				Debug::print("{$f} value is a {$gottype}");
			}
		}
		return $this->value = $value;
	}

	public function hasValue()
	{
		return isset($this->value);
	}
}
