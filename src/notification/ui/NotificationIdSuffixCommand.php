<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification\ui;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\data\DataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class NotificationIdSuffixCommand extends DataStructureCommand implements ValueReturningCommandInterface{

	public static function getCommandId(): string{
		return "noteIdSuffix";
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		$ds = $this->getDataStructure();
		if($ds->getNotificationType() === NOTIFICATION_TYPE_TEMPLATE){
			$decl = $this->getDeclarationLine();
			$did = $this->getDebugId();
			Debug::error("{$f} error, invalid notification type. Declared {$decl} with debug ID {$did}");
		}
		return $ds->getColumnValue(
			$ds->getTypedNotificationClass()::getElementClassStatic($ds)::getIdSuffixName()
		);
	}

	public function toJavaScript(): string{
		return "context.getNotificationIdSuffix()";
	}
}
