<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\constraint;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\SymbolicTrait;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\Constraint;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

abstract class SymbolicConstraintOption extends AlterOption{

	use SymbolicTrait;

	public function __construct($symbol){
		parent::__construct();
		if($symbol instanceof Constraint){
			$symbol = $symbol->getSymbol();
		}
		$this->setSymbol($symbol);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->symbol, $deallocate);
	}

	public function toSQL(): string{
		return " constraint " . $this->getSymbol();
	}
}
