<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;

/**
 * For this class, $columnName refers to the old column name.
 * The new one can be retrieved with $columnDefinition->getName()
 *
 * @author j
 */
class ChangeColumnOption extends ModifyColumnOption{

	use ColumnNameTrait;

	public function __construct($oldColumnName=null, $newColumnDefinition=null, $position = null, $afterColumnName = null){
		parent::__construct($newColumnDefinition, $position, $afterColumnName);
		if($oldColumnName !== null){
			$this->setColumnName($oldColumnName);
		}
	}

	public function toSQL(): string{
		$oldName = $this->getColumnName();
		$newDefinition = $this->getColumnDefinition()->toSQL();
		$string = "change {$oldName} {$newDefinition}";
		if($this->hasColumnPositon()){
			$string .= $this->getColumnPositionString();
		}
		return $string;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->columnName, $deallocate);
	}
}
