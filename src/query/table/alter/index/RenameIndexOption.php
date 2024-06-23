<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\index;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\NewNameTrait;

class RenameIndexOption extends IndexNameOption{

	use NewNameTrait;

	public function __construct($oldName, $newName){
		parent::__construct($oldName);
		$this->setNewName($newName);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->newName, $deallocate);
	}

	public function toSQL(): string{
		return "rename index " . $this->getIndexName() . " to " . $this->getNewName();
	}
}
