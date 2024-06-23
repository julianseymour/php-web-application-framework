<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class InitializeFormCommand extends ElementCommand{

	use SuccessAndErrorCallbacksTrait;
	
	public function setElement($element){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($element instanceof Element){
			if(!$element->hasIdAttribute()){
				Debug::error("{$f} element lacks an ID attribute");
			}elseif($print){
				$id = $element->getIdAttribute();
				Debug::print("{$f} element has ID \"{$id}\"");
			}
			if($element instanceof AjaxForm){
				$this->setSuccessCallback($element->getSuccessCallback());
				$this->setErrorCallback($element->getErrorCallback());
			}
		}elseif($print){
			Debug::print("{$f} element is not an element");
		}
		return parent::setElement($element);
	}

	public static function getCommandId(): string{
		return "initializeForm";
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		if(!$this->hasId()){
			Debug::error("{$f} this command cannot be executed without an ID");
		}
		Json::echoKeyValuePair("callback_success", $this->getSuccessCallback(), $destroy);
		Json::echoKeyValuePair("callback_error", $this->getErrorCallback(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function toJavaScript():string{//XXX add a counter to AjaxForm to specify form # for templates w/ > 1 form
		$f = __METHOD__;
		$string = "";
		$element = $this->getElement();
		$id = $element->getIdOverride();
		if($id instanceof JavaScriptInterface){
			$id = $id->toJavaScript();
		}
		$callback_success = $this->getSuccessCallback();
		if($callback_success instanceof JavaScriptInterface){
			$callback_success = $callback_success->toJavaScript();
		}
		$callback_error = $this->getErrorCallback();
		if($callback_error instanceof JavaScriptInterface){
			$callback_error = $callback_error->toJavaScript();
		}
		$string .= "AjaxForm.setFormSubmitHandler({$id}, {$callback_success}, {$callback_error})";
		return $string;
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->errorCallback, $deallocate);
		$this->release($this->successCallback, $deallocate);
	}
}
