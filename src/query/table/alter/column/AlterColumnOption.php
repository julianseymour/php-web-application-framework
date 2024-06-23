<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

abstract class AlterColumnOption extends AlterOption{

	use ColumnNameTrait;

	public function __construct(string $columnName){
		parent::__construct();
		$this->setColumnName($columnName);
	}

	public function toSQL(): string{
		return "alter column " . $this->getColumnName() . " ";
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->columnName, $deallocate);
	}
}