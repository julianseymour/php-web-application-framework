<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class SetInnerHTMLCommand extends ElementCommand implements ServerExecutableCommandInterface
{

	protected $innerHTML;

	public static function getCommandId(): string
	{
		return "setInnerHTML";
	}

	public function hasInnerHTML()
	{
		return isset($this->innerHTML);
	}

	public function getInnerHTML()
	{
		$f = __METHOD__; //SetInnerHTMLCommand::getShortClass()."(".static::getShortClass().")->getInnerHTML()";
		if(!$this->hasInnerHTML()) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} innerHTML is undefined. eclared {$decl}");
		}
		return $this->innerHTML;
	}

	public function setInnerHTML($innerHTML)
	{
		return $this->innerHTML = $innerHTML;
	}

	public function __construct($element, $innerHTML)
	{
		parent::__construct($element);
		if($innerHTML !== null) {
			$this->setInnerHTML($innerHTML);
		}
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		if($this->hasInnerHTML()) {
			$innerHTML = $this->getInnerHTML();
		}else{
			$element = $this->getElement();
			if($element->getAllocationMode() === ALLOCATION_MODE_ULTRA_LAZY && $element->hasSavedChildren()) {
				$innerHTML = $element->getSavedChildren($destroy); // appendSavedChildren($destroy);
			}else{
				Debug::error("{$f} innerHTML is undefined");
			}
		}
		Json::echoKeyValuePair('innerHTML', $innerHTML, $destroy);
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->innerHTML);
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //SetInnerHTMLCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		$print = false;
		$id = $this->getIdCommandString();
		if($id instanceof JavaScriptInterface) {
			$id = $id->toJavaScript();
		}
		$innerHTML = $this->getInnerHTML();
		/*
		 * if($this->hasParseType() && $this->getParseType() === "string"){
		 * $innerHTML = "'{$innerHTML}'";
		 * }
		 */
		if($innerHTML instanceof JavaScriptInterface) {
			$innerHTML = $innerHTML->toJavaScript();
			if($print) {
				Debug::print("{$f} after string conversion, innerHTML is \"{$innerHTML}\"");
			}
		}elseif(is_string($innerHTML) || $innerHTML instanceof StringifiableInterface) {
			$q = $this->getQuoteStyle();
			$innerHTML = "{$q}" . escape_quotes($innerHTML, $q) . "{$q}";
		}
		return "{$id}.innerHTML = {$innerHTML}";
	}

	public function resolve()
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$innerHTML = $this->getInnerHTML();
		while ($innerHTML instanceof ValueReturningCommandInterface) {
			$innerHTML = $innerHTML->evaluate();
		}
		$element->setInnerHTML($innerHTML);
	}
}
