<?php
namespace JulianSeymour\PHPWebApplicationFramework\style;

use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\json\Json;

class InsertStyleSheetCommand extends ElementCommand
{

	// protected $style;
	public static function getCommandId(): string
	{
		return "styleSheet";
	}

	/*
	 * public function setStyleElement($style){
	 * return $this->style = $style;
	 * }
	 *
	 * public function hasStyleElement(){
	 * return isset($this->style) && $this->style instanceof StyleElement;
	 * }
	 *
	 * public function getStyleElement(){
	 * $f = __METHOD__; //InsertStyleSheetCommand::getShortClass()."(".static::getShortClass().")->getStyleElement()";
	 * if(!$this->hasStyleElement()){
	 * Debug::error("{$f} style element is undefined");
	 * }
	 * }
	 *
	 * public function __construct($style=null){
	 * parent::__construct();
	 * if(isset($style)){
	 * $this->setStyleElement($style);
	 * }
	 * }
	 */
	public function echoInnerJson(bool $destroy = false): void
	{
		Json::echoKeyValuePair('innerHTML', $this->getElement()->getInnerHTML(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //InsertStyleSheetCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		ErrorMessage::unimplemented($f);
	}
}
