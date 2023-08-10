<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\ui;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\data\DataStructureCommand;

class NotificationIdSuffixCommand extends DataStructureCommand implements ValueReturningCommandInterface
{

	public static function getCommandId(): string
	{
		return "noteIdSuffix";
	}

	public function evaluate(?array $params = null)
	{
		$ds = $this->getDataStructure();
		return $ds->getColumnValue($ds->getTypedNotificationClass()::getElementClassStatic($ds)::getIdSuffixName());
	}

	public function toJavaScript(): string
	{
		return "context.getNotificationIdSuffix()";
	}
}
