<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait BinaryExpressionTrait{
	
	protected $leftHandSide;
	
	protected $rightHandSide;
	
	public function hasLeftHandSide():bool{
		return isset($this->leftHandSide);
	}
	
	public function setLeftHandSide($lhs){
		if($this->hasLeftHandSide()){
			$this->release($this->leftHandSide);
		}
		return $this->leftHandSide = $this->claim($lhs);
	}
	
	public function getLeftHandSide(){
		$f = __METHOD__;
		if(!$this->hasLeftHandSide()){
			$decl = $this->getDeclarationLine();
			$did = $this->getDebugId();
			Debug::error("{$f} left hand side is undefined, declared {$decl} with debug ID {$did}");
		}
		return $this->leftHandSide;
	}
	
	public function hasRightHandSide():bool{
		return $this->rightHandSide !== null;
	}
	
	public function getRightHandSide(){
		$f = __METHOD__;
		if(!$this->hasLeftHandSide()){
			$decl = $this->getDeclarationLine();
			$did = $this->getDebugId();
			Debug::error("{$f} right hand side is undefined, declared {$decl} with debug ID {$did}");
		}
		return $this->rightHandSide;
	}
	
	public function setRightHandSide($rhs){
		if($this->hasRightHandSide()){
			$this->release($this->rightHandSide);
		}
		return $this->rightHandSide = $this->claim($rhs);
	}
}
