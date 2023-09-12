<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionTableResolver;

class NotificationSubjectClassResolver extends IntersectionTableResolver
{

	public static function getIntersections()
	{
		$f = __METHOD__; //NotificationSubjectClassResolver::getShortClass()."(".static::getShortClass().")::getIntersections()";
		$print = false;
		$note_classes = mods()->getTypedNotificationClasses();
		$subject_classes = [];
		foreach($note_classes as $note_class) {
			$subject_classes = array_merge($subject_classes, $note_class::getIntersections());
		}
		if($print) {
			Debug::print("{$f} returning the following classes");
			Debug::printArray($subject_classes);
		}
		return $subject_classes;
	}
}
