<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class RenameColumnOption extends AlterColumnOption{

	protected $newColumnName;

	public function __construct(string $oldName, string $newName){
		parent::__construct($oldName);
		$this->setNewColumnName($newName);
	}

	public function setNewColumnName(?string $name):?string{
		return $this->newColumnName = $name;
	}

	public function hasNewColumnName():bool{
		return isset($this->newColumnName);
	}

	public function getNewColumnName(){
		$f = __METHOD__;
		if (! $this->hasNewColumnName()) {
			Debug::error("{$f} new column name is undefined");
		}
		return $this->newColumnName;
	}

	public function toSQL(): string{
		return "rename column " . $this->getColumnName() . " to " . $this->getNewColumnName();
	}
}
