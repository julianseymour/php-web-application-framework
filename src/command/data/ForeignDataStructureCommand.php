<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\data;

abstract class ForeignDataStructureCommand extends DataStructureCommand
{

	// protected $foreignKeyIndex;
	public function __construct($context, $index)
	{
		parent::__construct($context);
		$this->setColumnName($index);
	}

	/*
	 * public function setForeignKeyIndex($index){
	 * return $this->foreignKeyIndex = $index;
	 * }
	 *
	 * public function hasForeignKeyIndex(){
	 * return isset($this->foreignKeyIndex);
	 * }
	 *
	 * public function getForeignKeyIndex(){
	 * $f = __METHOD__; //ForeignDataStructureCommand::getShortClass()."(".static::getShortClass().")->getForeignKeyIndex()";
	 * if(!$this->hasForeignKeyIndex()){
	 * Debug::error("{$f} foreign key index is undefined");
	 * }
	 * return $this->foreignKeyIndex;
	 * }
	 */
}
