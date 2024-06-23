<?php

namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

trait ActionAttributeTrait{

	public function hasActionAttribute():bool{
		return $this->hasAttribute("action");
	}

	public function getActionAttribute(){
		$f = __METHOD__;
		try{
			if(!$this->hasActionAttribute()){
				$decl = $this->getDeclarationLine();
				Debug::error("{$f} action attribute is undefined; declared {$decl}");
			}
			return $this->getAttribute("action");
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setActionAttribute($action){
		return $this->setAttribute("action", $action);
	}
}
