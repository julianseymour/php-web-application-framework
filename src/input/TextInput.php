<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class TextInput extends StringInput{

	use ListAttributeTrait;

	public function getTypeAttribute(): string{
		return "text";
	}

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_TEXT;
	}

	public function setDirectionalityNameAttribute($value){
		return $this->setAttribute("dirname", $value);
	}

	public function hasDirectionalityNameAttribute(){
		return $this->hasAttribute("dirname");
	}

	public function getDirectionalityNameAttribute(){
		$f = __METHOD__; //TextInput::getShortClass()."(".static::getShortClass().")->getDirectionalityNameAttribute()";
		if(!$this->hasDirectionalityNameAttribute()) {
			Debug::error("{$f} dirname attribute is undefined");
		}
		return $this->getAttribute("dirname");
	}
}
