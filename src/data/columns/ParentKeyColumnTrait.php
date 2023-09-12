<?php
namespace JulianSeymour\PHPWebApplicationFramework\data\columns;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;
use Exception;
use mysqli;

/**
 * trait for DataStructures with a parentKey column referencing a parent data structure
 *
 * @author j
 *        
 */
trait ParentKeyColumnTrait
{

	use MultipleColumnDefiningTrait;

	public function hasParentDataType()
	{
		return $this->hasColumnValue("parentDataType");
	}

	public function getParentDataType()
	{
		$f = __METHOD__; //"ParentKeyColumnTrait(".static::getShortClass().")->getParentDataType()";
		if($this->hasParentDataType()) {
			return $this->getColumnValue('parentDataType');
		}elseif(!$this->hasParentObject()) {
			Debug::error("{$f} parent object is null");
			return DATATYPE_UNKNOWN;
		}
		$type = $this->getParentObject()->getDataType();
		return $this->hasColumn("parentDataType") ? $this->setParentDataType($type) : $type;
	}

	public function setParentDataType($t)
	{
		return $this->setColumnValue('parentDataType', $t);
	}

	/**
	 *
	 * @return ParentKeyColumnTrait
	 */
	public function getParentObject()
	{
		return $this->getForeignDataStructure('parentKey');
	}

	public function getParentObjectClassName()
	{
		$f = __METHOD__; //"ParentKeyColumnTrait(".static::getShortClass().")->getParentObjectClassName()";
		if(!$this->hasParentObject()) {
			Debug::error("{$f} parent object is undefined");
		}
		return $this->getParentObject()->getClass();
	}

	public function getParentKeyCommand()
	{
		return $this->getColumnValueCommand("parentKey");
	}

	public function hasParentKeyCommand()
	{
		return $this->hasColumnValueCommand("parentKey");
	}

	/**
	 * Returns the key in the assigned parentObject
	 *
	 * @return string
	 */
	public function getParentKey()
	{
		$f = __METHOD__; //"ParentKeyColumnTrait(".static::getShortClass().")::getParentKey()";
		if($this->hasColumn('parentKey')) {
			if($this->hasColumnValue('parentKey')) {
				return $this->getColumnValue('parentKey');
			}
		}
		if($this->hasParentObject()) {
			$parent = $this->getParentObject();
			if($parent->hasIdentifierValue()) {
				$key = $parent->getIdentifierValue();
				return $this->setParentKey($key);
			}
		}
		Debug::error("{$f} parent key is undefined");
	}

	public function setParentObject($parent)
	{
		return $this->setForeignDataStructure('parentKey', $parent);
	}

	public function isParentRequired()
	{
		if($this->hasParentObject()) {
			$parent = $this->getParentObject();
		}else{
			$parent = null;
		}
		return static::isParentRequiredStatic($parent);
	}

	public static function isParentRequiredStatic($parent = null)
	{
		return true;
	}

	public function getParentClass()
	{
		$f = __METHOD__; //"ParentKeyColumnTrait(".static::getShortClass().")->getParentClass()";
		$type = $this->getParentDataType();
		if(! isset($type)) {
			Debug::error("{$f} parent data type is undefined");
		}
		return mods()->getDataStructureClass($type, $this);
	}

	public function getParentPrettyClassName()
	{
		return $this->getParentObject()->getPrettyClassName();
	}

	public function getParentIterator()
	{
		return $this->getParentObject()->getIterator();
	}

	public function getParentNormalizedName()
	{
		return $this->getParentObject()->getNormalizedName();
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @param string $parent_class
	 * @return ParentKeyColumnTrait|NULL
	 */
	public function acquireParentObject($mysqli)
	{
		return $this->acquireForeignDataStructure($mysqli, 'parentKey');
	}

	public function hasParentKey()
	{
		if($this->hasParentObject()) {
			if($this->getParentObject()->hasIdentifierValue()) {
				return true;
			}
		}
		return $this->hasColumnValue('parentKey');
	}

	public function hasParentObject(){
		return $this->hasForeignDataStructure('parentKey');
	}

	public function setParentKey($key){
		$f = __METHOD__;
		if(empty($key)) {
			Debug::warning("{$f} passed a null parameter");
			return $this->setObjectStatus(ERROR_NULL_PARENT_KEY);
		}
		return $this->setColumnValue('parentKey', $key);
	}

	public function getSiblingKeys(){
		$f = __METHOD__;
		try{
			$parent = $this->getParentObject();
			$keys = [];
			$phylum = $this->getPhylumName();
			if(!$parent->hasForeignDataStructureList($phylum)) {
				return [];
			}
			$siblings = $parent->getForeignDataStructureList($phylum);
			if(empty($siblings)) {
				return $keys;
			}
			foreach($siblings as $sib) {
				if($sib->getIdentifierValue() === $this->getIdentifierValue()) {
					continue;
				}
				array_push($keys, $sib->getIdentifierValue());
			}
			return $keys;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getParentName(){
		$f = __METHOD__;
		if($this->hasParentObject()) {
			return $this->getParentObject()->getName();
		}
		Debug::warning("{$f} parent object is undefined");
		$this->setObjectStatus(ERROR_NULL_PARENT);
		return null;
	}

	public function getParentSerialNumber()
	{
		$f = __METHOD__; //"ParentKeyColumnTrait(".static::getShortClass().")::getParentSerialNumber()";
		if($this->getParentObject() == null) {
			Debug::warning("{$f} parent object is null");
			$this->setObjectStatus(ERROR_NULL_PARENT);
			return null;
		}
		return $this->getParentObject()->getSerialNumber();
	}
}