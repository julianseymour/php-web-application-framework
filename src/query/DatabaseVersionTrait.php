<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DatabaseVersionTrait
{

	protected $requiredMySQLVersion;

	public function setRequiredMySQLVersion($v)
	{
		$f = __METHOD__; //"DatabaseVersionTrait(".static::getShortClass().")->setRequiredMySQLVersion()";
		if($v == null) {
			unset($this->requiredMySQLVersion);
			return null;
		}
		return $this->requiredMySQLVersion = $v;
	}

	public function hasRequiredMySQLVersion()
	{
		return isset($this->requiredMySQLVersion);
	}

	public function getRequiredMySQLVersion()
	{
		$f = __METHOD__; //"DatabaseVersionTrait(".static::getShortClass().")->getRequiredMySQLVersion()";
		if(!$this->hasRequiredMySQLVersion()) {
			Debug::error("{$f} required MySQL version is undefined");
		}
		return $this->requiredMySQLVersion;
	}

	public function withRequiredSQLVersion($v)
	{
		$this->setRequiredMySQLVersion($v);
		return $this;
	}
}