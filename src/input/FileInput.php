<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class FileInput extends InputElement{

	use MultipleAttributeTrait;
	use RequiredAttributeTrait;

	public function getTypeAttribute(): string{
		return "file";
	}

	public function hasAcceptAttribute():bool{
		return $this->hasAttribute("accept");
	}

	public function getAcceptAttribute(){
		return $this->getAttribute("accept");
	}

	public function setAcceptAttribute($accept){
		return $this->setAttribute("accept", $accept);
	}

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_FILE;
	}

	public function getAllowEmptyInnerHTML():bool{
		return true;
	}

	public function hasCaptureAttribute():bool{
		return $this->hasAttribute("capture");
	}

	public function setCaptureAttribute($value){
		return $this->setAttribute("capture", $value);
	}

	public function getCaptureAttribute(){
		$f = __METHOD__;
		if(!$this->hasCaptureAttribute()){
			Debug::error("{$f} capture attribute is undefined");
		}
		return $this->getAttribute("capture");
	}
}
