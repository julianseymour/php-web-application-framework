<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnDefiningTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class ModifyColumnOption extends AlterOption{

	use ColumnPositionTrait;
	use ColumnDefiningTrait;

	public function __construct($columnDefinition = null, $position = null, $afterColumnName = null){
		parent::__construct();
		if($columnDefinition !== null){
			$this->setColumnDefinition($columnDefinition);
		}
		if($position !== null){
			$this->setColumnPosition($position, $afterColumnName);
		}
	}

	public function toSQL(): string{
		$definition = $this->getColumnDefinition();
		$string = "modify {$definition}";
		if($this->hasColumnPositon()){
			$string .= $this->getColumnPositionString();
		}
		return $string;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->columnDefinition, $deallocate);
		$this->release($this->columnPosition, $deallocate);
		$this->release($this->afterColumnName, $deallocate);
	}
}
