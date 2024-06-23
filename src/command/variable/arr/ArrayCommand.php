<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\variable\arr;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class ArrayCommand extends Command implements JavaScriptInterface{

	protected $array;

	public function __construct($array=null){
		parent::__construct();
		if($array !== null){
			$this->setArray($array);
		}
	}

	public function setArray($array){
		if($this->hasArray()){
			$this->release($this->array);
		}
		
		return $this->array = $this->claim($array);
	}

	public function hasArray():bool{
		return isset($this->array);
	}

	public function getArray(){
		$f = __METHOD__;
		if(!$this->hasArray()){
			Debug::error("{$f} array is undefined");
		}
		return $this->array;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->array, $deallocate);
	}
}
