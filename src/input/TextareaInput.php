<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class TextareaInput extends KeypadInput{

	use ReadOnlyAttributeTrait;
	use RequiredAttributeTrait;

	public static function isEmptyElement(): bool{
		return false;
	}

	public function getTypeAttribute(): string{
		return INPUT_TYPE_TEXTAREA;
	}

	public function echoInnerHTML(bool $destroy = false): void{
		echo $this->getValueAttribute();
	}

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_TEXTAREA;
	}

	public static function getElementTagStatic(): string{
		return INPUT_TYPE_TEXTAREA;
	}
	
	/*public function setAttribute(string $key, $value=null){
		$f = __METHOD__;
		if($key === "value" && !$this->getTemplateFlag() && (is_string($value) || $value instanceof ValueReturningCommandInterface)){
			$v = $value;
			while($v instanceof ValueReturningCommandInterface){
				$v = $v->evaluate();
			}
			if($v === "What is this used for?"){
				if($this->hasValueAttribute()){
					Debug::error("{$f} value attribute was already defined");
				}
				Debug::printStackTraceNoExit("{$f} entered. No, this does not already have a value attribute");
			}
		}
		return parent::setAttribute($key, $value);
	}*/
}
