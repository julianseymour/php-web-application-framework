<?php
namespace JulianSeymour\PHPWebApplicationFramework\validate;

class ImpossibleValidator extends Validator
{

	public function evaluate(&$validate_me): int
	{
		return $this->getSpecialFailureStatus();
	}
}
