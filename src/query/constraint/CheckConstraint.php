<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionalTrait;

class CheckConstraint extends Constraint
{

	use EnforcedConstraintTrait, ExpressionalTrait;

	public function __construct($expression, $enforcement = null, $symbol = null)
	{
		parent::__construct();
		$this->setExpression($expression);
		if ($enforcement !== null) {
			$this->setEnforcement($enforcement);
		}
		if ($symbol !== null) {
			$this->setSymbol($symbol);
		}
	}

	public function toSQL(): string
	{
		$string = $this->hasSymbol() ? "constraint " . $this->getSymbol() . " " : "";
		$string .= "check (" . $this->getExpression() . ")";
		if ($this->hasEnforcement()) {
			if (! $this->getEnforcement()) {
				$string .= " not";
			}
			$string .= " enforced";
		}
		return $string;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->enforcement);
		unset($this->expression);
	}
}
