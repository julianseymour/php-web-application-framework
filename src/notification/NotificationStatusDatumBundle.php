<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\DatumBundle;

class NotificationStatusDatumBundle extends DatumBundle
{

	public function generateComponents(?DataStructure $ds = null): array
	{
		$name = $this->getName();
		$classes = mods()->getTypedNotificationClasses();
		$components = [];
		foreach ($classes as $class) {
			if ($class::getNotificationTypeStatic() === NOTIFICATION_TYPE_TEST || ! $class::canDisable()) {
				continue;
			}
			$t = $class::getNotificationTypeString(LANGUAGE_DEFAULT);
			while ($t instanceof ValueReturningCommandInterface) {
				$t = $t->evaluate();
			}
			$datum = new BooleanDatum("{$name}{$t}Notifications");
			$datum->setDefaultValue(true);
			$datum->setUserWritableFlag(true);
			$datum->setSensitiveFlag(true);
			$datum->setHumanReadableName($class::getNotificationTypeString(null));
			array_push($components, $datum);
		}
		return $components;
	}
}
