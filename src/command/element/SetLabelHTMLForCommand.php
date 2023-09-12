<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\input\InputInterface;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class SetLabelHTMLForCommand extends ElementCommand implements ServerExecutableCommandInterface
{

	protected $htmlFor;

	public static function getCommandId(): string
	{
		return "htmlFor";
	}

	public function __construct($element = null, $for = null)
	{
		parent::__construct($element);
		if(isset($for)) {
			if($for instanceof InputInterface) {
				$for = $for->getIdAttribute();
			}
			$this->setHTMLFor($for);
		}
	}

	public function setHTMLFor($for)
	{
		return $this->htmlFor = $for;
	}

	public function hasHTMLFor()
	{
		return isset($this->htmlFor);
	}

	public function getHTMLFor()
	{
		$f = __METHOD__; //SetLabelHTMLForCommand::getShortClass()."(".static::getShortClass().")->getHTMLFor()";
		if(!$this->hasHTMLFor()) {
			Debug::error("{$f} HTML for attribute is undefined");
		}
		return $this->htmlFor;
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		Json::echoKeyValuePair('htmlFor', $this->getHTMLFor(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->htmlFor);
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //SetLabelHTMLForCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		try{
			$id = $this->getIdCommandString();
			if($id instanceof JavaScriptInterface) {
				$id = $id->toJavaScript();
			}
			$htmlFor = $this->getHTMLFor();
			if($htmlFor instanceof JavaScriptInterface) {
				$htmlFor = $htmlFor->toJavaScript();
			}else{
				$htmlFor = single_quote($htmlFor);
			}
			return "{$id}.htmlFor = {$htmlFor}";
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function resolve()
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$for = $this->getHTMLFor();
		while ($for instanceof ValueReturningCommandInterface) {
			$for = $for->evaluate();
		}
		$element->setForAttribute($for);
	}
}
