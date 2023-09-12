<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait SQLSecurityTrait
{

	protected $sqlSecurityType;

	public function setSQLSecurity(?string $type): ?string
	{
		$f = __METHOD__; //"SQLSecurityTrait(".static::getShortClass().")->setSQLSecurity()";
		if($type == null) {
			unset($this->sqlSecurityType);
			return null;
		}elseif(!is_string($type)) {
			Debug::error("{$f} SQL security must be a string");
		}
		$type = strtolower($type);
		switch ($type) {
			case SQL_SECURITY_DEFINER:
			case SQL_SECURITY_INVOKER:
				return $this->sqlSecurityType = $type;
			default:
				Debug::error("{$f} invalid SQL security \"{$type}\"");
		}
	}

	public function hasSQLSecurity(): bool
	{
		return isset($this->sqlSecurityType);
	}

	public function getSQLSecurity(): string
	{
		$f = __METHOD__; //"SQLSecurityTrait(".static::getShortClass().")->getSQLSecurity()";
		if(!$this->hasSQLSecurity()) {
			Debug::error("{$f} SQL security is undefined");
		}
		return $this->sqlSecurityType;
	}

	public function sqlSecurity($type): QueryStatement
	{
		$this->setSQLSecurity($type);
		return $this;
	}
}
