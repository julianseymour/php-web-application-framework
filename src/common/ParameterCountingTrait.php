<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ParameterCountingTrait{

	public abstract function inferParameterCount();

	protected $parameterCount;

	public function getParameterCount(): ?int{
		$f = __METHOD__;
		if(!$this->hasParameterCount()){
			Debug::error("{$f} parameter count is undefined");
		}
		return $this->parameterCount;
	}

	public function setParameterCount($count): ?int{
		$f = __METHOD__;
		if(!is_int($count)){
			Debug::error("{$f} value count must be a non-negative integer");
		}elseif($count < 0){
			Debug::error("{$f} value count must be non-negative");
		}elseif($this->hasParameterCount()){
			$this->release($this->parameterCount);
		}
		return $this->parameterCount = $this->claim($count);
	}

	public function hasParameterCount(): bool{
		return isset($this->parameterCount);
	}

	public function withParameterCount($count){
		$this->setParameterCount($count);
		return $this;
	}
}
