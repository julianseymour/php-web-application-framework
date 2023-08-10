<?php
namespace JulianSeymour\PHPWebApplicationFramework\validate;

use Closure;

class ClosureValidator extends Validator
{

	use ValidationClosureTrait;

	public function __construct(?Closure $closure = null)
	{
		parent::__construct();
		if ($closure instanceof Closure) {
			$this->setValidationClosure($closure);
		}
	}

	public function evaluate(&$validate_me): int
	{
		$closure = $this->getValidationClosure();
		return $closure($validate_me);
	}

	/*
	 * public function setValidationClosure(?Closure $closure):?Closure{
	 * $f = __METHOD__; //ClosureValidator::getShortClass()."(".static::getShortClass().")->setValidationClosure()";
	 * if($closure === null){
	 * unset($this->validationClosure);
	 * return null;
	 * }
	 * $reflect = new ReflectionFunction($name)
	 * }
	 */
	public function dispose(): void
	{
		parent::dispose();
		unset($this->validationClosure);
	}
}
