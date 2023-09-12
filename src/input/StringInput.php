<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\StringDatum;

abstract class StringInput extends KeypadInput{

	use ReadOnlyAttributeTrait;
	use RequiredAttributeTrait;

	public function getMaximumLengthAttribute(){
		$f = __METHOD__;
		if(!$this->hasMaximumLengthAttribute()) {
			Debug::error("{$f} max length attribute is undefined");
		}
		return $this->getAttribute("maxlength");
	}

	public function hasMaximumLengthAttribute():bool{
		return $this->hasAttribute("maxlength");
	}

	public function setMaximumLengthAttribute($maxlength){
		return $this->setAttribute("maxlength", $maxlength);
	}

	public function getMinimumLengthAttribute(){
		$f = __METHOD__;
		if(!$this->hasMinimumLengthAttribute()) {
			$decl = $this->getDeclarationLine();
			$name = $this->hasNameAttribute() ? $this->getNameAttribute() : "unnamed";
			Debug::error("{$f} minimum length attribute is undefined for input \"{$name}\", declared {$decl}");
		}
		return $this->getAttribute("minlength");
	}

	public function hasMinimumLengthAttribute():bool{
		return $this->hasAttribute("minlength");
	}

	public function setMinimumLengthAttribute($minlength){
		return $this->setAttribute("minlength", $minlength);
	}

	public function setPatternAttribute($value){
		return $this->setAttribute("pattern", $value);
	}

	public function hasPatternAttribute():bool{
		return $this->hasAttribute("pattern");
	}

	public function getPatternAttribute(){
		$f = __METHOD__;
		if(!$this->hasPatternAttribute()) {
			Debug::error("{$f} pattern attribute is undefined");
		}
		return $this->getAttribute("pattern");
	}

	public function bindContext($context){
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if($context instanceof StringDatum) {
			if($context->hasMinimumLength()) {
				$this->setMinimumLengthAttribute($context->getMinimumLength());
			}
			if($context->hasMaximumLength()) {
				$this->setMaximumLengthAttribute($context->getMaximumLength());
			}
			if($context->hasRegularExpression()) {
				$regex = $context->getJavaScriptRegularExpression();
				if($print) {
					Debug::print("{$f} setting pattern attribute to \"{$regex}\"");
				}
				$this->setPatternAttribute($regex);
			}elseif($print) {
				Debug::print("{$f} string datum lacks a regular expression");
			}
		}elseif($print) {
			Debug::print("{$f} constext is not a StringDatum");
		}
		return parent::bindContext($context);
	}

	public function setSizeAttribute($value){
		return $this->setAttribute("size", $value);
	}

	public function hasSizeAttribute():bool{
		return $this->hasAttribute("size");
	}

	public function getSizeAttribute(){
		$f = __METHOD__;
		if(!$this->hasSizeAttribute()) {
			Debug::error("{$f} size attribute is undefined");
		}
		return $this->getAttribute("size");
	}
}
