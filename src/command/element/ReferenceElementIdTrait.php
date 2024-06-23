<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ReferenceElementIdTrait{

	protected $referenceElementId;

	public function setReferenceElementId($id){
		$f = __METHOD__;
		if($this->hasReferenceElementId()){
			$this->release($this->referenceElementId);
		}
		return $this->referenceElementId = $this->claim($id);
	}

	public function hasReferenceElementId():bool{
		return isset($this->referenceElementId);
	}

	public function getReferenceElementId(){
		$f = __METHOD__;
		if(!$this->hasReferenceElementId()){
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} insertion target ID is undefined; declared {$decl}");
		}
		return $this->referenceElementId;
	}
}
