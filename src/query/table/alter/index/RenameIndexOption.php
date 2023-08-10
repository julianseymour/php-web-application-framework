<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\index;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class RenameIndexOption extends IndexNameOption
{

	protected $newIndexName;

	public function __construct($oldName, $newName)
	{
		parent::__construct($oldName);
		$this->setNewIndexName($newName);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->newIndexName);
	}

	public function setNewIndexName($newIndexName)
	{
		return $this->newIndexName = $newIndexName;
	}

	public function hasNewIndexName()
	{
		return isset($this->newIndexName);
	}

	public function getNewIndexName()
	{
		$f = __METHOD__; //RenameIndexOption::getShortClass()."(".static::getShortClass().")->getNewIndexName()";
		if (! $this->hasNewIndexName()) {
			Debug::error("{$f} new index name is undefined");
		}
		return $this->newIndexName;
	}

	public function toSQL(): string
	{
		return "rename index " . $this->getIndexName() . " to " . $this->getNewIndexName();
	}
}
