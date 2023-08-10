<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class RenameColumnOption extends AlterColumnOption
{

	protected $newColumnName;

	public function __construct($oldName, $newName)
	{
		parent::__construct($oldName);
		$this->setNewColumnName($newName);
	}

	public function setNewColumnName($name)
	{
		return $this->newColumnName = $name;
	}

	public function hasNewColumnName()
	{
		return isset($this->newColumnName);
	}

	public function getNewColumnName()
	{
		$f = __METHOD__; //RenameColumnOption::getShortClass()."(".static::getShortClass().")->getNewColumnName()";
		if (! $this->hasNewColumnName()) {
			Debug::error("{$f} new column name is undefined");
		}
		return $this->newColumnName;
	}

	public function toSQL(): string
	{
		return "rename column " . $this->getColumnName() . " to " . $this->getNewColumnName();
	}
}
