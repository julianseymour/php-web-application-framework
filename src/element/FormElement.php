<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ActionAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\AutocompleteAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\NameAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\RelationshipAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\TargetAttributeTrait;

class FormElement extends Element
{

	use ActionAttributeTrait;
	use AutocompleteAttributeTrait;
	use NameAttributeTrait;
	use RelationshipAttributeTrait;
	use TargetAttributeTrait;

	public function setAcceptCharacterSetAttribute($value){
		return $this->setAttribute("accept-charset", $value);
	}

	public function hasAcceptCharactersetAttribute(){
		return $this->hasAttribute("accept-charset");
	}

	public function getAcceptCharactersetAttribute(){
		return $this->getAttribute("accept-charset");
	}

	public function novalidate(){
		return $this->setAttribute("novalidate", null);
	}

	public function setEncodingTypeAttribute($enctype){
		return $this->setAttribute("enctype", $enctype);
	}

	public function getEncodingTypeAttribute(){
		return $this->getAttribute("enctype");
	}

	public static function getElementTagStatic(): string{
		return "form";
	}

	public function hasMethodAttribute(){
		$f = __METHOD__;
		$debug_id = $this->getDebugId();
		if (! $this->hasAttribute("method")) {
			// Debug::print("{$f} method attribute is undefined for form with debug ID \"{$debug_id}\"");
			return false;
		}
		$method = $this->getAttribute("method");
		// Debug::print("{$f} method attribute is \"{$method}\"");
		return ! empty($method);
	}

	public function getMethodAttribute(){
		$f = __METHOD__;
		if (! $this->hasMethodAttribute()) {
			// Debug::print("{$f} method attribute is undefined -- returning default");
			return HTTP_REQUEST_METHOD_GET;
		}
		$method = $this->getAttribute("method");
		// Debug::print("{$f} returning \"{$method}\"");
		return $this->getAttribute("method");
	}

	public function setMethodAttribute($method){
		$f = __METHOD__;
		switch ($method) {
			case "post":
			case "POST":
			case "get":
			case "GET":
				break;
			default:
				Debug::error("{$f} invalid method attribute \"{$method}\"");
				break;
		}
		return $this->setAttribute("method", $method);
	}

	public function validate(array &$arr): int{
		$f = __METHOD__;
		Debug::warning("{$f} regular form elements do not support automatic form validation, sorry");
		return FAILURE;
	}
}
