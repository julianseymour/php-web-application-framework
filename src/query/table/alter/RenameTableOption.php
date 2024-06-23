<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\NewNameTrait;

class RenameTableOption extends AlterOption{

	use NewNameTrait;

	public function __construct($new_table){
		parent::__construct();
		$this->setNewName($new_table);
	}

	public function toSQL(): string{
		return "rename " . $this->getNewName();
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->newName, $deallocate);
	}
}
