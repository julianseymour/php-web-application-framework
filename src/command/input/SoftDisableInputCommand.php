<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\input;

use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class SoftDisableInputCommand extends ElementCommand
{

	public static function getCommandId(): string
	{
		return "disable";
	}

	public function toJavaScript(): string
	{
		$idcs = $this->getIdCommandString();
		if ($idcs instanceof JavaScriptInterface) {
			$idcs = $idcs->toJavaScript();
		}
		return "{$idcs}.disabled = true;";
	}
}
