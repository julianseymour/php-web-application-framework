<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;

class OrderByClause extends Basic implements ReplicableInterface, SQLInterface{

	use ColumnNameTrait;
	use DirectionalityTrait;
	use ReplicableTrait;

	public function __construct($column_name=null, $directionality = DIRECTION_ASCENDING){
		parent::__construct();
		if($column_name !== null){
			$this->setColumnName($column_name);
		}
		if($directionality !== null){
			$this->setDirectionality($directionality);
		}
	}

	public function toSQL(): string{
		return $this->getColumnName() . " " . $this->getDirectionality();
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->columnName, $deallocate);
		$this->release($this->directionality, $deallocate);
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasColumnName()){
			$this->setColumnName(replicate($that->getColumnName()));
		}
		if($that->hasDirectionality()){
			$this->setDirectionality(replicate($that->getDirectionality()));
		}
		return $ret;
	}
}
