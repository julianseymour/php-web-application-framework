<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use function JulianSeymour\PHPWebApplicationFramework\ends_with;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;

trait MultipleAttributeTrait
{

	public function setMultipleAttribute($value = null)
	{
		$f = __METHOD__; //"MultipleAttributeTrait(".static::getShortClass().")->setMultipleAttribute()";
		if($this instanceof FileInput && $this->hasNameAttribute()) {
			$name = $this->getNameAttribute();
			if($name instanceof ConcatenateCommand && $name->ends_with("[]")) {
				// Debug::print("{$f} name is a concatenate media command, and ends with []");
			}elseif(is_string($name) && ends_with($name, "[]")) {
				// Debug::print("{$f} name is a string, and ends with []");
			}else{
				$this->setNameAttribute(new ConcatenateCommand($this->getNameAttribute(), "[]"));
			}
		}
		return $this->setAttribute("multiple", $value);
	}

	public function getMultipleAttribute()
	{
		return $this->getAttribute("multiple");
	}

	public function hasMultipleAttribute()
	{
		return $this->hasAttribute("multiple");
	}
}
