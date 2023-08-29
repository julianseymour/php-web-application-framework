<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

/**
 * trait for queries that are compatible with constraints (create and alter table) and DataStructure
 *
 * @author j
 *        
 */
trait ConstrainableTrait
{

	use ArrayPropertyTrait;

	public function hasConstraints()
	{
		return $this->hasArrayProperty("constraints");
	}

	public function setConstraints($constraints)
	{
		$f = __METHOD__; //"ConstrainableTrait(".static::getShortClass().")->setConstraints()";
		foreach ($constraints as $c) {
			if (! $c instanceof Constraint) {
				Debug::error("{$f} one of the input parameters is not a constraint");
			}
		}
		return $this->setArrayProperty("constraints", $constraints);
	}

	public function pushConstraints(...$constraints)
	{
		$f = __METHOD__; //"ConstrainableTrait(".static::getShortClass().")->pushConstraints()";
		foreach ($constraints as $c) {
			if (! $c instanceof Constraint) {
				Debug::error("{$f} one of the input parameters is not a constraint");
			}
		}
		return $this->pushArrayProperty("constraints", ...$constraints);
	}

	public function getConstraints()
	{
		return $this->getProperty("constraints");
	}

	public function getConstraintCount()
	{
		return $this->getArrayPropertyCount("constraints");
	}

	public function withConstraint($constraint)
	{
		$this->pushConstraints($constraint);
		return $this;
	}

	public function mergeConstraints($constraints)
	{
		$f = __METHOD__; //"ConstrainableTrait(".static::getShortClass().")->mergeConstraints()";
		foreach ($constraints as $c) {
			if (! $c instanceof Constraint) {
				Debug::error("{$f} one of the input parameters is not a constraint");
			}
		}
		return $this->mergeArrayProperty("constraints", $constraints);
	}
}
