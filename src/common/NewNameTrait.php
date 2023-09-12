<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait NewNameTrait
{

	protected $newName;

	public function setNewName($n): string
	{
		$f = __METHOD__; //"NewNameTrait(".static::getShortClass().")->setNewName()";
		if($n == null) {
			unset($this->newName);
			return null;
		}elseif(!is_string($n)) {
			Debug::error("{$f} new name must be a string");
		}
		return $this->newName = $n;
	}

	public function hasNewName(): bool
	{
		return ! empty($this->newName);
	}

	public function getNewName(): string
	{
		$f = __METHOD__; //"NewNameTrait(".static::getShortClass().")->getNewName()";
		if(!$this->hasNewName()) {
			Debug::error("{$f} new table name is undefined");
		}
		return $this->newName;
	}
}