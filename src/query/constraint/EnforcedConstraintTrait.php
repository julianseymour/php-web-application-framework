<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

trait EnforcedConstraintTrait
{

	protected $enforcement;

	public function setEnforcement($value)
	{
		if (! is_bool($value)) {
			$value = boolval($value);
		}
		return $this->enforcement = $value;
	}

	public function hasEnforcement()
	{
		return isset($this->enforcement) && ($this->enforcement === true | $this->enforcement === false);
	}

	public function getEnforcement()
	{
		if (! $this->hasEnforcement()) {
			return true;
		}
		return $this->enforcement;
	}
}
