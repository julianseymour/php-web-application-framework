<?php

namespace JulianSeymour\PHPWebApplicationFramework\validate;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class CheckboxValidator extends Validator{

	use NamedTrait;

	public function __construct($name = null){
		parent::__construct();
		if(!empty($name)){
			$this->setName($name);
		}
	}

	public function evaluate(&$validate_me): int{
		$f = __METHOD__;
		$print = false;
		$checked = $validate_me[$this->getName()];
		if($checked === "on"){
			if($print){
				Debug::print("{$f} box is checked");
			}
			return SUCCESS;
		}
		Debug::warning("{$f} box is not checked");
		return $this->getSpecialFailureStatus();
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->name, $deallocate);
	}
}
