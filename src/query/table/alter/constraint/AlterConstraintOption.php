<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\constraint;

use JulianSeymour\PHPWebApplicationFramework\query\constraint\EnforcedConstraintTrait;

class AlterConstraintOption extends SymbolicConstraintOption
{

	use EnforcedConstraintTrait;

	public function __construct($symbol, $enforcement)
	{
		parent::__construct($symbol);
		$this->setEnforcement($enforcement);
	}

	public function toSQL(): string
	{
		return "alter" . parent::toSQL() . ($this->getEnforcement() ? " not" : "") . " enforced";
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->enforcement);
	}
}
