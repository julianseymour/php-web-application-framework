<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;

trait LockOptionTrait
{

	protected $lockOption;

	public function setLockOption(bool $lock = true): bool
	{
		$f = __METHOD__; //"LockOptionTrait(".static::getShortClass().")->setLockOption()";
		if($lock == null) {
			unset($this->lockOption);
			return null;
		}elseif(!is_string($lock)) {
			Debug::error("{$f} lock option must be a string");
		}
		$lock = strtolower($lock);
		return $this->lockOption = $lock;
	}

	public function hasLockOption()
	{
		return isset($this->lockOption);
	}

	public function getLockOption(): bool
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->getLockOption()";
		if(!$this->hasLockOption()) {
			Debug::error("{$f} lock option is undefined");
		}
		return $this->lockOption;
	}

	public function withLockOption(bool $lock = true)
	{
		$this->setLockOption($lock);
		return $this;
	}
}
