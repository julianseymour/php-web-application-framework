<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class OrderByOption extends AlterOption{

	use MultipleColumnNamesTrait;

	public function __construct(...$columnNames){
		parent::__construct();
		$this->setColumnNames($columnNames);
	}

	public function toSQL(): string{
		return "order by " . $this->getColumnNameString();
	}

	public function dispose(bool $deallocate=false): void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->propertyTypes, $deallocate);
	}
}
