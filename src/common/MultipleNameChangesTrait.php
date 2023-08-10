<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait MultipleNameChangesTrait
{

	protected $nameChanges;

	public function rename($oldname, $newname)
	{
		if (! $this->hasNameChanges()) {
			$this->nameChanges = [];
		}
		return $this->nameChanges[$oldname] = $newname;
	}

	public function hasNameChanges()
	{
		return isset($this->nameChanges) && is_array($this->nameChanges) && ! empty($this->nameChanges);
	}

	public function changeNames($nameChanges)
	{
		$f = __METHOD__; //"MultipleNameChangesTrait(".static::getShortClass().")->changeNames()";
		foreach ($nameChanges as $oldname => $newname) {
			if (! is_string($oldname) || ! is_string($newname)) {
				Debug::error("{$f} both old and new names must be strings");
			}
		}
		return $this->nameChanges = $nameChanges;
	}

	public function getNameChanges()
	{
		$f = __METHOD__; //"MultipleNameChangesTrait(".static::getShortClass().")->getNameChanges()";
		if (! $this->hasNameChanges()) {
			Debug::error("{$f} name changes are undefined");
		}
		return $this->nameChanges;
	}
}
