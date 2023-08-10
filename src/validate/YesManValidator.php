<?php

namespace JulianSeymour\PHPWebApplicationFramework\validate;

class YesManValidator extends Validator
{

	public function evaluate(&$validate_me): int
	{
		return SUCCESS;
	}
}
