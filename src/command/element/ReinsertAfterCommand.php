<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class ReinsertAfterCommand extends ReinsertElementCommand{

	public static function getCommandId(): string{
		return "reinsert";
	}

	public static function getInsertWhere(){
		return CONST_AFTER;
	}

	public function toJavaScript(): string{
		$idcs = $this->getIdCommandString();
		if($idcs instanceof JavaScriptInterface){
			$idcs = $idcs->toJavaScript();
		}
		$target = new GetElementByIdCommand($this->getReferenceElementId());
		$target = $target->toJavaScript();
		return "insertAfter({$idcs}, {$target})\n";
	}
}
