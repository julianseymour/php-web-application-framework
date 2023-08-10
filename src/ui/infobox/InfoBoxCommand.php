<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui\infobox;

use JulianSeymour\PHPWebApplicationFramework\command\element\MultipleElementCommand;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\json\Json;

class InfoBoxCommand extends MultipleElementCommand
{

	// XXX constructor needs to evaluate whether element has predecessor/successor nodes -- if so, it needs to be wrapped, or invalid JSON will be generated
	public function __construct($element)
	{
		$f = __METHOD__; //InfoBoxCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		if ($element instanceof Element) {
			$element->setSubcommandCollector($this);
		}
		parent::__construct($element);
	}

	public static function getCommandId(): string
	{
		return "info";
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //ErrorMessage::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		ErrorMessage::unimplemented($f);
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		$f = __METHOD__; //InfoBoxCommand::getShortClass()."(".static::getShortClass().")->echoInnerJson()";
		// if($this->hasMultipleElements()){
		Json::echoKeyValuePair('elements', $this->getElements(), $destroy);
		/*
		 * }else{
		 * $element = $this->getElement();
		 * Json::echoKeyValuePair('tag', $element->getElementTag(), $destroy);
		 * Json::echoKeyValuePair('element', $this->getElement(), $destroy);
		 * }
		 */
		parent::echoInnerJson($destroy);
	}
}
