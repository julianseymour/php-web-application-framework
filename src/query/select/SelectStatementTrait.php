<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\select;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait SelectStatementTrait{

	protected $selectStatement;

	public function setSelectStatement(?SelectStatement $obj): ?SelectStatement{
		$f = __METHOD__;
		if(!$obj instanceof SelectStatement){
			Debug::error("{$f} input parameter must be SelectStatement or null");
		}elseif($this->hasSelectStatement()){
			$this->release($this->selectStatement);
		}
		return $this->selectStatement = $this->claim($obj);
	}

	public function hasSelectStatement(): bool{
		return isset($this->selectStatement);
	}

	public function getSelectStatement():SelectStatement{
		$f = __METHOD__;
		if(!$this->hasSelectStatement()){
			Debug::error("{$f} select statement is undefined");
		}
		return $this->selectStatement;
	}
}