<?php

namespace JulianSeymour\PHPWebApplicationFramework\validate;

use JulianSeymour\PHPWebApplicationFramework\common\ParametricTrait;

abstract class ParametricValidator
{

	use ParametricTrait;

	public abstract function extractParameters(&$params);
}