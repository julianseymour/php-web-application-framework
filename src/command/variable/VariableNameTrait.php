<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait VariableNameTrait
{

	protected $variableName;

	public function setVariableName($vn)
	{
		if($vn == null) {
			unset($this->variableName);
			return null;
		}
		return $this->variableName = $vn;
	}

	public function hasVariableName()
	{
		return isset($this->variableName);
	}

	public function getVariableName()
	{
		$f = __METHOD__; //"VariableNameTrait(".static::getShortClass().")->getVariableName()";
		if(!$this->hasVariableName()) {
			Debug::error("{$f} variable name is undefined");
		}
		return $this->variableName;
	}
}
