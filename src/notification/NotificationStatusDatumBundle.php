<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\DatumBundle;

class NotificationStatusDatumBundle extends DatumBundle{

	public function generateComponents(?DataStructure $ds = null): array{
		$name = $this->getName();
		$classes = mods()->getTypedNotificationClasses();
		$components = [];
		foreach($classes as $class){
			if($class::getNotificationTypeStatic() === NOTIFICATION_TYPE_TEST || ! $class::canDisable()){
				continue;
			}
			switch($name){
				case "push":
					$t = $class::getPushStatusVariableName();
					break;
				case "email":
					$t = $class::getEmailStatusVariableName();
					break;
				default:
					Debug::error("{$f} invalid notification type \"{$name}\"");
			}
			$datum = new BooleanDatum($t);
			$datum->setDefaultValue(true);
			$datum->setUserWritableFlag(true);
			$datum->setSensitiveFlag(true);
			$datum->setHumanReadableName($class::getNotificationTypeString(null));
			array_push($components, $datum);
		}
		return $components;
	}
}
