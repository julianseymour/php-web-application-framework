<?php

namespace JulianSeymour\PHPWebApplicationFramework\validate;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Closure;

trait ValidationClosureTrait
{

	/**
	 * function for custom validation, fired in validate()
	 *
	 * @var Closure
	 */
	protected $validationClosure;

	public function hasValidationClosure()
	{
		return isset($this->validationClosure) && $this->validationClosure instanceof Closure;
	}

	public function setValidationClosure(?Closure $closure): ?Closure
	{
		$f = __METHOD__; //"ValidationClosureTrait(".static::getShortClass().")->setValidationClosure()";
		if($closure == null) {
			unset($this->validationClosure);
			return null;
		}elseif(!$closure instanceof Closure) {
			Debug::error("{$f} this function only accepts closures");
		}
		return $this->validationClosure = $closure;
	}

	public function getValidationClosure(): Closure
	{
		$f = __METHOD__; //"ValidationClosureTrait(".static::getShortClass().")->getValidationClosure()";
		if(!$this->hasValidationClosure()) {
			Debug::error("{$f} validation closure is undefined");
		}
		return $this->validationClosure;
	}

	public function withValidationClosure(?Closure $closure): object
	{
		$this->setValidationClosure($closure);
		return $this;
	}
}