<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\column;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ColumnPositionTrait{

	protected $columnPosition;

	protected $afterColumnName;

	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->afterColumnName, $deallocate);
		$this->release($this->columnPosition, $deallocate);
	}
	
	public function setColumnPosition($position, $afterColumnName = null){
		$f = __METHOD__;
		if(!is_string($position)){
			Debug::error("{$f} position must be a string");
		}
		$position = strtolower($position);
		switch($position){
			case COLUMN_POSITION_AFTER:
			case COLUMN_POSITION_FIRST:
				break;
			default:
				Debug::error("{$f} invalid column position \"{$position}\"");
		}
		if($this->hasColumnPositon()){
			$this->release($this->columnPosition);
		}
		$this->columnPosition = $this->claim($position);
		if($position === COLUMN_POSITION_AFTER && $afterColumnName !== null){
			$this->setAfterColumnName($afterColumnName);
		}
		return $position;
	}

	public function getColumnPositionString():string{
		$f = __METHOD__;
		if(!$this->hasColumnPositon()){
			Debug::error("{$f} position is undefined");
		}
		$string = $this->getColumnPosition();
		if($string === COLUMN_POSITION_AFTER){
			$string .= $this->getAfterColumnName();
		}
		return $string;
	}

	public function hasColumnPositon():bool{
		return isset($this->columnPosition) && !empty($this->columnPosition);
	}

	public function getColumnPosition(){
		$f = __METHOD__;
		if(!$this->hasColumnPositon()){
			Debug::error("{$f} position is undefined");
		}
		return $this->columnPosition;
	}

	public function setAfterColumnName($afterColumnName){
		if($this->hasAfterColumnName()){
			$this->release($this->afterColumnName);
		}
		return $this->afterColumnName = $this->claim($afterColumnName);
	}

	public function hasAfterColumnName():bool{
		return isset($this->afterColumnName) && !empty($this->afterColumnName);
	}

	public function getAfterColumnName(){
		$f = __METHOD__;
		if(!$this->hasAfterColumnName()){
			Debug::error("{$f} after column name is undefined");
		}
		return $this->afterColumnName;
	}
}
