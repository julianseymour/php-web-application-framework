<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\AlgorithmOptionTrait;

class AlgorithmOption extends AlterOption{

	use AlgorithmOptionTrait;

	public function __construct($algorithm = ALGORITHM_DEFAULT){
		parent::__construct();
		$this->setAlgorithm($algorithm);
	}

	public function setAlgorithm($algorithm){
		$f = __METHOD__;
		if(!is_string($algorithm)){
			Debug::error("{$f} algorithm name is not a string");
		}
		$algorithm = strtolower($algorithm);
		switch($algorithm){
			case ALGORITHM_COPY:
			case ALGORITHM_DEFAULT:
			case ALGORITHM_INPLACE:
			case ALGORITHM_INSTANT:
				break;
			default:
				Debug::error("{$f} invalid algorithm name \"{$algorithm}\"");
		}
		return $this->algorithmType = $algorithm;
	}

	public function getAlgorithm(){
		if(!$this->hasAlgorithm()){
			return ALGORITHM_DEFAULT;
		}
		return $this->algorithmType;
	}

	public function toSQL(): string{
		return "algorithm " . $this->getAlgorithm();
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->algorithmType, $deallocate);
	}
}
