<?php
namespace JulianSeymour\PHPWebApplicationFramework\app\generator;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\script\ClassReturningJsFunctionGenerator;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;

class GetValidatorClassJsFunctionGenerator extends ClassReturningJsFunctionGenerator
{

	public function generate($context): ?JavaScriptFunction
	{
		if ($context === null) {
			$context = mods();
		}
		return static::generateGetJavaScriptClassFunction("getValidatorClass", $context->getValidatorClasses());
	}
}
