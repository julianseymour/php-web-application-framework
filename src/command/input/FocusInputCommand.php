<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\input;

use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class FocusInputCommand extends ElementCommand{

	public static function getCommandId(): string{
		return "focus";
	}

	public function toJavaScript(): string{
		$idcs = $this->getIdCommandString();
		if($idcs instanceof JavaScriptInterface){
			$idcs = $idcs->toJavaScript();
		}
		return "{$idcs}.focus()";
	}
}
