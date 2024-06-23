<?php

namespace JulianSeymour\PHPWebApplicationFramework\style;

use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\json\Json;

class InsertStyleSheetCommand extends ElementCommand{

	public static function getCommandId(): string{
		return "styleSheet";
	}

	public function echoInnerJson(bool $destroy = false): void{
		Json::echoKeyValuePair('innerHTML', $this->getElement()->getInnerHTML(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}
}
