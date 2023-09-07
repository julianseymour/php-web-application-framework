<?php

namespace JulianSeymour\PHPWebApplicationFramework\data\columns;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use Exception;
use mysqli;

trait CounterpartKeyColumnTrait{

	protected $roleAsCounterpart;

	public abstract function generateCounterpartObject(mysqli $mysqli);

	public abstract function getRoleAsCounterpart();

	public abstract function getSubtype():string;

	public function setRoleAsCounterpart($role){
		return $this->roleAsCounterpart = $role;
	}

	public function acquireCounterpartObject($mysqli){
		return $this->loadForeignDataStructure($mysqli, 'counterpartKey', false, 3);
	}

	public static function getOppositeRoleStatic($role){
		$f = __METHOD__;
		if (! isset($role)) {
			Debug::error("{$f} role is undefined");
		}
		switch ($role) {
			case USER_ROLE_BUYER:
				return USER_ROLE_SELLER;
			case USER_ROLE_RECIPIENT:
				return USER_ROLE_SENDER;
			case USER_ROLE_SELLER:
				return USER_ROLE_BUYER;
			case USER_ROLE_SENDER:
				return USER_ROLE_RECIPIENT;
			case USER_ROLE_HOST:
				return USER_ROLE_VISITOR;
			case USER_ROLE_VISITOR:
				return USER_ROLE_HOST;
			default:
				Debug::error("{$f} invalid role \"{$role}\"");
				return null;
		}
	}

	public function ejectCounterpartKey(){
		return $this->ejectColumnValue("counterpartKey");
	}

	public function getCounterpartSerialNumber()
	{
		return $this->getCounterpartObject()->getSerialNumber();
	}

	public function hasCounterpartSerialNumber()
	{
		return $this->hasCounterpartObject() && $this->getCounterpartObject()->hasSerialNumber();
	}

	public function setCounterpartKey($key)
	{
		return $this->setColumnValue('counterpartKey', $key);
	}

	public function hasCounterpartDataType()
	{
		return $this->hasColumnValue("counterpartDataType");
	}

	public function getCounterpartDataType()
	{
		return $this->getColumnValue("counterpartDataType");
	}

	public function setCounterpartDataType($value)
	{
		return $this->setColumnValue("counterpartDataType", $value);
	}

	public function hasCounterpartSubtype()
	{
		return $this->hasColumnValue("counterpartSubtype");
	}

	public function getCounterpartSubtype()
	{
		return $this->getColumnValue("counterpartSubtype");
	}

	public function setCounterpartSubtype($value)
	{
		return $this->setColumnValue("counterpartSubtype", $value);
	}
	
	public function setCounterpartObject($obj)
	{
		$f = __METHOD__; //f(CounterpartKeyColumnTrait::class);
		if (! $obj->hasIdentifierValue()) {
			Debug::error("{$f} counterpart does not have a key");
		}
		return $this->setForeignDataStructure('counterpartKey', $obj);
	}

	public function hasCounterpartKey()
	{
		return $this->hasColumnValue('counterpartKey');
	}

	public function getCounterpartKey()
	{
		return $this->getColumnValue('counterpartKey');
	}

	public function getCounterpartObject()
	{
		return $this->getForeignDataStructure('counterpartKey');
	}

	public function hasCounterpartObject()
	{
		return $this->hasForeignDataStructure('counterpartKey');
	}

	protected static function declareCounterpartKeyColumn(?string $name = null): ForeignKeyDatum
	{
		if ($name === null) {
			$name = "counterpartKey";
		}
		$counterpartKey = new ForeignKeyDatum($name);
		$counterpartKey->setForeignDataStructureClass(static::class);
		$counterpartKey->constrain();
		$counterpartKey->setConverseRelationshipKeyName($name);
		$counterpartKey->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);
		$counterpartKey->setNullable(true);
		$counterpartKey->setOnDelete(REFERENCE_OPTION_SET_NULL);
		$counterpartKey->setOnUpdate(REFERENCE_OPTION_CASCADE);
		return $counterpartKey;
	}

	protected function generateCounterpartIfInstigator(mysqli $mysqli): int
	{
		$f = __METHOD__;
		try {
			$print = false;
			$role = $this->getRoleAsCounterpart();
			if ($role !== COUNTERPART_ROLE_INSTIGATOR) {
				if (! $this->hasCounterpartKey()) {
					Debug::error("{$f} this object lacks a counterpart key");
				} elseif ($print) {
					Debug::print("{$f} this object is not the instigator relative to its counterpart");
				}
				return SUCCESS;
			} elseif (! $this->hasCorrespondentObject()) {
				if (! $this->hasCorrespondentKey()) {
					Debug::error("{$f} correspondent key is undefined");
				} elseif ($print) {
					Debug::print("{$f} correspondent object is undefined -- about to acquire it");
				}
				$this->acquireCorrespondentObject($mysqli);
			} elseif ($print) {
				Debug::print("{$f} already have correspondent object");
			}
			if (! $this->hasIdentifierValue()) {
				$this->generateKey();
			}
			$counterpart = $this->generateCounterpartObject($mysqli);
			if (! $counterpart->hasIdentifierValue()) {
				$counterpart->generateKey();
			}
			$counterpart->setInsertFlag(true);
			$this->setCounterpartObject($counterpart);
			$counterpart->setCounterpartObject($this);
			if (! $counterpart->hasCounterpartKey()) {
				Debug::error("{$f} counterpart lacks a reference to this object");
			} elseif (! $this->hasCounterpartKey()) {
				Debug::error("{$f} this object lacks a counterpart key");
			} elseif ($print) {
				Debug::print("{$f} both counterpart keys are defined");
			}
			$this->setPostInsertForeignDataStructuresFlag(true);
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
