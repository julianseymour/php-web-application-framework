<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait SQLSecurityTrait{

	protected $sqlSecurityType;

	public function setSQLSecurity(?string $type): ?string{
		$f = __METHOD__;
		if(!is_string($type)){
			Debug::error("{$f} SQL security must be a string");
		}
		$type = strtolower($type);
		switch($type){
			case SQL_SECURITY_DEFINER:
			case SQL_SECURITY_INVOKER:
				break;
			default:
				Debug::error("{$f} invalid SQL security \"{$type}\"");
		}
		if($this->hasSQLSecurity()){
			$this->release($this->sqlSecurityType);
		}
		return $this->sqlSecurityType = $this->claim($type);
	}

	public function hasSQLSecurity(): bool{
		return isset($this->sqlSecurityType);
	}

	public function getSQLSecurity(): string{
		$f = __METHOD__;
		if(!$this->hasSQLSecurity()){
			Debug::error("{$f} SQL security is undefined");
		}
		return $this->sqlSecurityType;
	}

	public function sqlSecurity($type): QueryStatement{
		$this->setSQLSecurity($type);
		return $this;
	}
}
