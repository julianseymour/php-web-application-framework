<?php

namespace JulianSeymour\PHPWebApplicationFramework\data\columns;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait SubtypeColumnTrait{
	
	public function getSubtype():string{
		$f = __METHOD__;
		if(!$this->hasSubtype()){
			$sc = $this->getShortClass();
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} subtype is undefined for {$sc} declared {$decl}");
		}
		return $this->getColumnValue("subtype");
	}
	
	public function hasSubtype():bool{
		return $this->hasColumnValue("subtype");
	}
	
	public function setSubtype(string $value):string{
		return $this->setColumnValue("subtype", $value);
	}
	
	public function ejectSubtype():?string{
		return $this->ejectColumnValue("subtype");
	}
}
