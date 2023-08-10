<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\pin;

use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;

class UnpinNotificationForm extends SortNotificationForm
{

	public static function getActionAttributeStatic(): ?string
	{
		return '/unpin';
	}

	public static function getLabelInnerHTML()
	{
		return new ConcatenateCommand("📌", _("Remove pin"));
	}

	public function getDirectives(): ?array
	{
		return [
			"unset"
		];
	}

	protected static function getButtonValueAttributeStatic()
	{
		return "unpin";
	}
}
