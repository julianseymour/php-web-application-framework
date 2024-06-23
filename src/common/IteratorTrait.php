<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait IteratorTrait{

	public abstract function getDeclarationLine();

	protected $iterator;

	public function hasIterator():bool{
		return isset($this->iterator) && $this->iterator !== null;
	}

	public function getIterator(){
		$f = __METHOD__;
		if(!$this->hasIterator()){
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} iterator is undefined. This object was declared {$decl}");
		}
		return $this->iterator;
	}

	public function setIterator($i){
		$f = __METHOD__;
		if($this->hasIterator()){
			$this->release($this->iterator);
		}
		return $this->iterator = $this->claim($i);
	}
}
