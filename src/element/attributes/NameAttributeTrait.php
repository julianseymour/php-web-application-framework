<?php

namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

use JulianSeymour\PHPWebApplicationFramework\command\input\SetInputNameCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait NameAttributeTrait{

	public function hasNameAttribute(): bool{
		return $this->hasAttribute("name");
	}

	public function getNameAttribute(){
		$f = __METHOD__;
		if(!$this->hasNameAttribute()){
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} name attribute is undefined. Declared {$decl}");
		}
		return $this->getAttribute("name");
	}

	public function setNameAttribute($name){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} setting name attribute to \"{$name}\"");
		}
		return $this->setAttribute("name", $name);
	}

	public function withNameAttribute($name){
		$this->setNameAttribute($name);
		return $this;
	}

	public function setNameAttributeCommand($name): SetInputNameCommand{
		return new SetInputNameCommand($this, $name);
	}
}
