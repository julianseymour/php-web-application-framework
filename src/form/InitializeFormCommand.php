<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class InitializeFormCommand extends ElementCommand
{

	public function setElement($element)
	{
		$f = __METHOD__; //InitializeFormCommand::getShortClass()."(".static::getShortClass().")->setElement()";
		$print = false;
		if($element instanceof Element) {
			if(!$element->hasIdAttribute()) {
				Debug::error("{$f} element lacks an ID attribute");
			}elseif($print) {
				$id = $element->getIdAttribute();
				Debug::print("{$f} element has ID \"{$id}\"");
			}
		}elseif($print) {
			Debug::print("{$f} element is not an element");
		}
		return parent::setElement($element);
	}

	/*
	 * public function __construct($element){
	 * $f = __METHOD__; //InitializeFormCommand::getShortClass()."(".static::getShortClass().")->__construct()";
	 * parent::__construct($element);
	 * if($element->getClass() === "BugReportStateForm"){
	 * Debug::print("{$f} yes, this does get called");
	 * $id = $this->getId();
	 * if(starts_with($id, "bug_report_form-")){
	 * Debug::error("{$f} ID is \"{$id}\"");
	 * }
	 * Debug::print("{$f} ID is \"{$id}\"");
	 * }
	 * }
	 */
	public static function getCommandId(): string
	{
		return "initializeForm";
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		$f = __METHOD__; //InitializeFormCommand::getShortClass()."(".static::getShortClass().")->echoInnerJson()";
		if(!$this->hasId()) {
			Debug::error("{$f} this command cannot be executed without an ID");
		}
		$form = $this->getElement();
		Json::echoKeyValuePair("callback_success", $form->getSuccessCallback(), $destroy);
		Json::echoKeyValuePair("callback_error", $form->getErrorCallback(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function toJavaScript(): string
	{ // XXX add a counter to AjaxForm to specify form # for templates w/ > 1 form
		$f = __METHOD__; //InitializeFormCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		$string = "";
		$element = $this->getElement();
		$id = $element->getIdOverride();
		if($id instanceof JavaScriptInterface) {
			$id = $id->toJavaScript();
		}
		$callback_success = $element->getSuccessCallback();
		if($callback_success instanceof JavaScriptInterface) {
			$callback_success = $callback_success->toJavaScript();
		}
		$callback_error = $element->getErrorCallback();
		if($callback_error instanceof JavaScriptInterface) {
			$callback_error = $callback_error->toJavaScript();
		}
		$string .= "AjaxForm.setFormSubmitHandler({$id}, {$callback_success}, {$callback_error})";
		return $string;
	}
}
