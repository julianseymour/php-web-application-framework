<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\script\ClassReturningJsFunctionGenerator;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;

class GetTypedNotificationClassJsFunctionGenerator extends ClassReturningJsFunctionGenerator{

	public function generate($context): ?JavaScriptFunction{
		if($context === null){
			$context = mods();
		}
		return static::generateGetJavaScriptClassFunction("getTypedNotificationClass", $context->getTypedNotificationClasses());
	}
}
