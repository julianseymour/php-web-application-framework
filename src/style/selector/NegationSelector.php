<?php

namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class NegationSelector extends Selector{

	private $negatedSelector;

	public function setNegatedSelector($negate_me){
		if($this->hasNegatedSelector()){
			$this->release($this->negatedSelector);
		}
		return $this->negatedSelector = $this->claim($negate_me);
	}

	public function __construct($negate_me){
		parent::__construct();
		if($negate_me !== null){
			$this->setNegatedSelector($negate_me);
		}
	}

	public function hasNegatedSelector():bool{
		return isset($this->negatedSelector);
	}

	public function getNegatedSelector(){
		$f = __METHOD__;
		try{
			if(!$this->hasNegatedSelector()){
				Debug::error("{$f} negated selector is undefined");
			}
			return $this->negatedSelector;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function echo(bool $destroy = false): void{
		echo ":not(";
		echo $this->getNegatedSelector();
		echo ")";
	}
	
	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->negatedSelector, $deallocate);
	}
}
