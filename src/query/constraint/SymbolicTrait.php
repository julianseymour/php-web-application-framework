<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait SymbolicTrait
{

	protected $symbol;

	public function setSymbol($symbol)
	{
		return $this->symbol = $symbol;
	}

	public function hasSymbol()
	{
		return isset($this->symbol);
	}

	public function getSymbol()
	{
		$f = __METHOD__; //"SymbolicTrait(".static::getShortClass().")->getSymbol()";
		if(!$this->hasSymbol()) {
			Debug::error("{$f} symbol is undefined");
		}
		return $this->symbol;
	}
}