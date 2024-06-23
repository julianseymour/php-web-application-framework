<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class SubmitInput extends ButtonlikeInput
{

	public function getTypeAttribute(): string
	{
		return INPUT_TYPE_SUBMIT;
	}

	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_SUBMIT;
	}

	public function setFormActionAttribute($value)
	{
		return $this->setAttribute("formaction", $value);
	}

	public function hasFormActionAttribute()
	{
		return $this->hasAttribute("formaction");
	}

	public function getFormActionAttribute()
	{
		$f = __METHOD__; //SubmitInput::getShortClass()."(".static::getShortClass().")->getFormActionAttribute()";
		if(!$this->hasFormActionAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute("formaction");
	}

	public function setBypassFormValidationAttribute($value)
	{
		return $this->setAttribute("formnovalidate", $value);
	}

	public function hasBypassFormValidationAttribute()
	{
		return $this->hasAttribute("formnovalidate");
	}

	public function getBypassFormValidationAttribute()
	{
		$f = __METHOD__; //SubmitInput::getShortClass()."(".static::getShortClass().")->getBypassFormValidationAttribute()";
		if(!$this->hasBypassFormValidationAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute("formnovalidate");
	}

	public function setFormEncodingTypeAttribute($value)
	{
		return $this->setAttribute("formenctype", $value);
	}

	public function hasFormEncodingTypeAttribute()
	{
		return $this->hasAttribute("formenctype");
	}

	public function getFormEncodingTypeAttribute()
	{
		$f = __METHOD__; //SubmitInput::getShortClass()."(".static::getShortClass().")->getFormEncodingTypeAttribute()";
		if(!$this->hasFormEncodingTypeAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute("formenctype");
	}

	public function setFormMethodAttribute($value)
	{
		return $this->setAttribute("formmethod", $value);
	}

	public function hasFormMethodAttribute()
	{
		return $this->hasAttribute("formmethod");
	}

	public function getFormMethodAttribute()
	{
		$f = __METHOD__; //SubmitInput::getShortClass()."(".static::getShortClass().")->getFormMethodAttribute()";
		if(!$this->hasFormMethodAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute("formmethod");
	}

	public function setFormTargetAttribute($value)
	{
		return $this->setAttribute("formtarget", $value);
	}

	public function hasFormTargetAttribute()
	{
		return $this->hasAttribute("formtarget");
	}

	public function getFormTargetAttribute()
	{
		$f = __METHOD__; //SubmitInput::getShortClass()."(".static::getShortClass().")->getFormTargetAttribute()";
		if(!$this->hasFormTargetAttribute()){
			Debug::error("{$f} attribute is undefined");
		}
		return $this->getAttribute("formtarget");
	}
}
