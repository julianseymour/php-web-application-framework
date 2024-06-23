<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter;

use function JulianSeymour\PHPWebApplicationFramework\release;

class SetValidationOption extends AlterOption{

	use ValidationTrait;

	public function __construct($validate){
		parent::__construct();
		$this->setValidation($validate);
	}

	public function toSQL(): string{
		return "with".(!$this->getValidateFlag() ? "out" : "")."validation";
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->validate, $deallocate);
	}
}
