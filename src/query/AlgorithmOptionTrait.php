<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait AlgorithmOptionTrait{

	protected $algorithmType;

	public function setAlgorithm($alg){
		$f = __METHOD__;
		if(!is_string($alg)){
			Debug::error("{$f} algorithm must be a string");
		}elseif($this->hasAlgorithm()){
			$this->release($this->algorithmType);
		}
		return $this->algorithmType = $this->claim($alg);
	}

	public function hasAlgorithm():bool{
		return isset($this->algorithmType);
	}

	public function getAlgorithm(){
		$f = __METHOD__;
		if(!$this->hasAlgorithm()){
			Debug::error("{$f} algorithm is undefined");
		}
		return $this->algorithmType;
	}
}