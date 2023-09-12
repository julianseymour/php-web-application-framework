<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\constraint;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\Constraint;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class AddConstraintOption extends AlterOption
{

	protected $constraint;

	public function __construct($constraint)
	{
		parent::__construct();
		$this->setConstraint($constraint);
	}

	public function setConstraint($constraint)
	{
		$f = __METHOD__; //AddConstraintOption::getShortClass()."(".static::getShortClass().")->setConstraint()";
		if(!$constraint instanceof Constraint) {
			Debug::error("{$f} input parameter must be a constraint");
		}
		return $this->constraint = $constraint;
	}

	public function hasConstraint()
	{
		return isset($this->constraint);
	}

	public function getConstraint()
	{
		$f = __METHOD__; //AddConstraintOption::getShortClass()."(".static::getShortClass().")->getConstraint()";
		if(!$this->hasConstraint()) {
			Debug::error("{$f} constraint is undefined");
		}
		return $this->constraint;
	}

	public function toSQL(): string
	{
		$constraint = $this->getConstraint();
		if($constraint instanceof SQLInterface) {
			$constraint = $constraint->toSQL();
		}
		return "add {$constraint}";
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->constraint);
	}
}
