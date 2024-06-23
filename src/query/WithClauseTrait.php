<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use mysqli;

trait WithClauseTrait{

	protected ?WithClause $withClause;

	public function setWithClause($with): ?WithClause{
		$f = __METHOD__;
		if(!$with instanceof WithClause){
			Debug::error("{$f} invalid datatype");
		}elseif($this->hasWithClause()){
			$this->release($this->withClause);
		}
		return $this->withClause = $this->claim($with);
	}

	public function hasWithClause(): bool{
		return isset($this->withClause) && $this->withClause instanceof WithClause;
	}

	public function getWithClause(): WithClause{
		$f = __METHOD__;
		if(!$this->hasWithClause()){
			Debug::error("{$f} with clause is undefined");
		}
		return $this->withClause;
	}

	public function with($withClause){
		$this->setWithClause($withClause);
		return $this;
	}

	public function hasRecursiveCommonTableExpression(?mysqli $mysqli = null): bool{
		return $this->hasWithClause() && $this->getWithClause()->getRecursiveFlag() && CommonTableExpression::isSupported($mysqli);
	}
}
