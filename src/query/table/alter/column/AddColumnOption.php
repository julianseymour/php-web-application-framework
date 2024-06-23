<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;
use Exception;

class AddColumnOption extends AlterOption{

	use MultipleColumnDefiningTrait;
	use ColumnPositionTrait;

	public function __construct($columnDefinition = null, $position = null, $afterColumnName = null){
		parent::__construct();
		if($columnDefinition !== null){
			$this->pushColumn($columnDefinition);
			if($position !== null){
				$this->setColumnPosition($position, $afterColumnName);
			}
		}
	}

	public static function addColumns(...$columnDefinitions): AddColumnOption{
		return AddColumnOption::create()->withColumns(...$columnDefinitions);
	}

	public function toSQL(): string{
		$f = __METHOD__;
		try{
			$string = "add column ";
			if($this->getColumnCount() === 1){
				$string .= $this->getColumns()[0]->__toString();
				if($this->hasColumnPositon()){
					$string .= $this->getColumnPositionString();
				}
			}else{
				$string .= implode(',', $this->getColumns());
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function dispose(bool $deallocate=false): void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->columnPosition, $deallocate);
		$this->release($this->afterColumnName, $deallocate);
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
	}
}
