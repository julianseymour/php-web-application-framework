<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ExpressionalTrait
{

	protected $expression;

	public function setExpression($expr)
	{
		$f = __METHOD__; //"ExpressionalTrait(".static::getShortClass().")->setExpression()";
		if ($expr == null) {
			unset($this->expression);
			return null;
		}
		return $this->expression = $expr;
	}

	public function hasExpression()
	{
		return isset($this->expression);
	}

	public function getExpression()
	{
		$f = __METHOD__; //"ExpressionalTrait(".static::getShortClass().")->getExpression()";
		if (! $this->hasExpression()) {
			Debug::error("{$f} expression is undefined");
		}
		return $this->expression;
	}

	public function withExpression($expression)
	{
		$this->setExpression($expression);
		return $this;
	}
}
