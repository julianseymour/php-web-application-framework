<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter;

use JulianSeymour\PHPWebApplicationFramework\common\NewNameTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class RenameTableOption extends AlterOption
{

	use NewNameTrait;

	public function __construct($new_table)
	{
		parent::__construct();
		$this->setNewName($new_table);
	}

	public function toSQL(): string
	{
		return "rename " . $this->getNewName();
	}

	public function setNewName($n): string
	{
		$f = __METHOD__; //RenameTableOption::getShortClass()."(".static::getShortClass().")->setNewName()";
		if ($n == null) {
			unset($this->newName);
			return null;
		} elseif (! is_string($n)) {
			Debug::error("{$f} new name must be a string");
		} elseif (! validateName($n)) {
			Debug::error("{$f} invalid table name \"{$n}\"");
			return $this->setObjectStatus(ERROR_INVALID_TABLE_NAME);
		}
		return $this->newName = $n;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->newName);
	}
}
