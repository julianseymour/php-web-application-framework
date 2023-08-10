<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\pin;

use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;

class RepinNotificationForm extends PinNotificationForm
{

	public static function getLabelInnerHTML()
	{
		return new ConcatenateCommand("⬆️", _("Move to top"));
	}

	protected static function getButtonValueAttributeStatic()
	{
		return "repin";
	}
}
