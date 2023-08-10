<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use JulianSeymour\PHPWebApplicationFramework\common\arr\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait MultipleExpressionsTrait
{

	use ArrayPropertyTrait;

	public function setExpressions($values)
	{
		$f = __METHOD__; //"MultipleExpressionsTrait(".static::getShortClass().")->setExpressions()";
		return $this->setArrayProperty('expressions', $values);
	}

	public function pushExpressions(...$values)
	{
		$f = __METHOD__; //"MultipleExpressionsTrait(".static::getShortClass().")->pushExpressions()";
		return $this->pushArrayProperty('expressions', ...$values);
	}

	public function mergeExpressions($values)
	{
		$f = __METHOD__; //"MultipleExpressionsTrait(".static::getShortClass().")->mergeExpressions()";
		return $this->mergeArrayProperty('expressions', $values);
	}

	public function hasExpressions()
	{
		return $this->hasArrayProperty("expressions");
	}

	public function getExpressions()
	{
		return $this->getProperty("expressions");
	}

	public function getExpressionCount()
	{
		return $this->getArrayPropertyCount("expressions");
	}

	public function getExpression($i)
	{
		return $this->getArrayPropertyValue("expressions", $i);
	}
}
