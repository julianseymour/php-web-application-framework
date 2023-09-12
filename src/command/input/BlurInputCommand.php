<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\input;

use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class BlurInputCommand extends ElementCommand
{

	public static function getCommandId(): string
	{
		return "blur";
	}

	public function toJavaScript(): string
	{
		$idcs = $this->getIdCommandString();
		if($idcs instanceof JavaScriptInterface) {
			$idcs = $idcs->toJavaScript();
		}
		return "{$idcs}.blur()";
	}
}
