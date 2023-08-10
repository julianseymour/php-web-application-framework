<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait WithClauseTrait
{

	protected WithClause $withClause;

	public function setWithClause($with): ?WithClause
	{
		$f = __METHOD__; //"WithClauseTrait(".static::getShortClass().")->setWithClause()";
		if ($with == null) {
			unset($this->withClause);
			return null;
		} elseif (! $with instanceof WithClause) {
			Debug::error("{$f} invalid datatype");
		}
		return $this->withClause = $with;
	}

	public function hasWithClause(): bool
	{
		return isset($this->withClause) && $this->withClause instanceof WithClause;
	}

	public function getWithClause(): WithClause
	{
		$f = __METHOD__; //"WithClauseTrait(".static::getShortClass().")->getWithClause()";
		if (! $this->hasWithClause()) {
			Debug::error("{$f} with clause is undefined");
		}
		return $this->withClause;
	}

	public function with($withClause)
	{
		$this->setWithClause($withClause);
		return $this;
	}

	public function hasRecursiveCommonTableExpression($mysqli = null): bool
	{
		return $this->hasWithClause() && $this->getWithClause()->getRecursiveFlag() && CommonTableExpression::isSupported($mysqli);
	}
}
