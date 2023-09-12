<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait AlgorithmOptionTrait
{

	protected $algorithmType;

	public function setAlgorithm($alg)
	{
		$f = __METHOD__; //"AlgorithmOptionTrait(".static::getShortClass().")->setAlgorithm()";
		if($alg == null) {
			unset($this->algorithmType);
			return null;
		}elseif(!is_string($alg)) {
			Debug::error("{$f} algorithm must be a string");
		}
		return $this->algorithmType = $alg;
	}

	public function hasAlgorithm()
	{
		return isset($this->algorithmType);
	}

	public function getAlgorithm()
	{
		$f = __METHOD__; //"AlgorithmOptionTrait(".static::getShortClass().")->getAlgorithm()";
		if(!$this->hasAlgorithm()) {
			Debug::error("{$f} algorithm is undefined");
		}
		return $this->algorithmType;
	}
}