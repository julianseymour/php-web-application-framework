<?php

namespace JulianSeymour\PHPWebApplicationFramework\style\selector;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;

class PseudoclassSelector extends Selector{

	private $pseudoclass;

	public function setPseudoclass($pseudoclass){
		if($this->hasPseudoclass()){
			$this->release($this->pseudoclass);
		}
		return $this->pseudoclass = $this->claim($pseudoclass);
	}

	public function getPseudoclass(){
		return $this->pseudoclass;
	}

	public function hasPseudoclass():bool{
		return isset($this->pseudoclass);
	}
	
	public function echo(bool $destroy = false): void{
		echo ":{$this->pseudoclass}";
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->pseudoclass, $deallocate);
	}

	public function __construct($pseudoclass = null){
		parent::__construct();
		if(isset($pseudoclass)){
			$this->setPseudoclass($pseudoclass);
		}
	}
}