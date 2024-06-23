<?php

namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionTrait;

abstract class BinarySelector extends Selector{

	use BinaryExpressionTrait;

	public abstract static function echoOperator():void;

	public function echo(bool $destroy = false): void{
		$this->getLeftHandSide()->echo($destroy);
		echo " ";
		$this->echoOperator();
		$this->getRightHandSide()->echo($destroy);
	}

	public function __construct($lhs = null, $rhs = null){
		parent::__construct();
		if(isset($lhs)){
			$this->setLeftHandSide($lhs);
			if(isset($rhs)){
				$this->setRightHandSide($rhs);
			}
		}
	}

	public function child($chile):Selector{
		return new ChildSelector($this, $chile);
	}
	
	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->leftHandSide, $deallocate);
		$this->release($this->rightHandSide, $deallocate);
	}
}
