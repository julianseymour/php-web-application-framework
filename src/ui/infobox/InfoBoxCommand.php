<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui\infobox;

use JulianSeymour\PHPWebApplicationFramework\command\element\MultipleElementCommand;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\json\Json;

class InfoBoxCommand extends MultipleElementCommand{

	// XXX constructor needs to evaluate whether element has predecessor/successor nodes -- if so, it needs to be wrapped, or invalid JSON will be generated
	public function __construct(...$elements){
		$f = __METHOD__;
		if(count($elements) === 1 && $elements[0] instanceof Element){
			$elements[0]->setSubcommandCollector($this);
		}
		parent::__construct(...$elements);
	}

	public static function getCommandId(): string{
		return "info";
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		Json::echoKeyValuePair('elements', $this->getElements(), $destroy);
		parent::echoInnerJson($destroy);
	}
}
