<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\constraint;

use JulianSeymour\PHPWebApplicationFramework\query\constraint\Constraint;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\SymbolicTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

abstract class SymbolicConstraintOption extends AlterOption
{

	use SymbolicTrait;

	public function __construct($symbol)
	{
		parent::__construct();
		if($symbol instanceof Constraint) {
			$symbol = $symbol->getSymbol();
		}
		$this->setSymbol($symbol);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->symbol);
	}

	public function toSQL(): string
	{
		return " constraint " . $this->getSymbol();
	}
}
