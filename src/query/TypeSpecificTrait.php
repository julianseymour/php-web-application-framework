<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait TypeSpecificTrait{

	protected $typeSpecifier;

	public function setTypeSpecifier(?string $typedef): ?string{
		$f = __METHOD__;
		if(! preg_match('/[dis]+/', $typedef)){
			Debug::error("{$f} invalid type definition string \"{$typedef}\"");
		}elseif($this->hasTypeSpecifier()){
			$this->release($this->typeSpecifier);
		}
		return $this->typeSpecifier = $this->claim($typedef);
	}

	public function hasTypeSpecifier():bool{
		return isset($this->typeSpecifier) && is_string($this->typeSpecifier) && !empty($this->typeSpecifier);
	}

	public function getTypeSpecifier(){
		$f = __METHOD__;
		if(!$this->hasTypeSpecifier()){
			Debug::error("{$f} type definition string is undefined");
		}
		return $this->typeSpecifier;
	}

	public function withTypeSpecifier(?string $ts){
		$this->setTypeSpecifier($ts);
		return $this;
	}

	public function appendTypeSpecifier(string $s){
		return $this->setTypeSpecifier($this->getTypeSpecifier().$s);
	}
}
