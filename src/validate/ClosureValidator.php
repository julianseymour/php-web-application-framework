<?php

namespace JulianSeymour\PHPWebApplicationFramework\validate;

use function JulianSeymour\PHPWebApplicationFramework\release;
use Closure;

class ClosureValidator extends Validator{

	use ValidationClosureTrait;

	public function __construct(?Closure $closure = null){
		parent::__construct();
		if($closure instanceof Closure){
			$this->setValidationClosure($closure);
		}
	}

	public function evaluate(&$validate_me): int{
		$closure = $this->getValidationClosure();
		return $closure($validate_me);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->validationClosure, $deallocate);
	}
}
