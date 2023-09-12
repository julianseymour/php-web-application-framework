<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\grant;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\database\DatabaseNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\role\DatabaseRoleData;
use JulianSeymour\PHPWebApplicationFramework\query\table\TableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\user\MultipleDatabaseUserDefinitionsTrait;

abstract class PrivilegeStatement extends QueryStatement{

	use DatabaseNameTrait;
	use MultipleDatabaseUserDefinitionsTrait;
	use TableNameTrait;

	protected $objectType;

	// object_type: { TABLE | FUNCTION | PROCEDURE }
	protected $proxyUser;

	public function dispose(): void{
		parent::dispose();
		unset($this->objectType);
		unset($this->proxyUser);
	}

	public function setPrivileges($values){
		return $this->setArrayProperty("privileges", $values);
	}

	public function pushPrivileges(...$values){
		return $this->pushArrayProperty("privileges", ...$values);
	}

	public function mergePrivileges($values){
		return $this->mergeArrayProperty("privileges", $values);
	}

	public function hasPrivileges(){
		return $this->hasArrayProperty("privileges");
	}

	public function getPrivileges(){
		return $this->getProperty("privileges");
	}

	public function getPrivilegeCount(){
		return $this->getArrayPropertyCount("privileges");
	}

	public function withPrivileges(...$values): PrivilegeStatement{
		if(count($values) == 1 && is_array($values[0])) {
			$values = $values[0];
		}
		$this->setPrivileges([
			...$values
		]);
		return $this;
	}

	public function setObjectType($type){
		$f = __METHOD__;
		if($type == null) {
			unset($this->objectType);
			return null;
		}elseif(!is_string($type)) {
			Debug::error("{$f} object type must be a string");
		}
		$type = strtolower($type);
		switch ($type) {
			case DATABASE_OBJECT_TYPE_FUNCTION:
			case DATABASE_OBJECT_TYPE_PROCEDURE:
			case DATABASE_OBJECT_TYPE_TABLE:
				break;
			default:
				Debug::error("{$f} invalid database object type \"{$type}\"");
		}
		return $this->objectType = $type;
	}

	public function hasObjectType(){
		return isset($this->objectType);
	}

	public function getObjectType(){
		$f = __METHOD__;
		if(!$this->hasObjectType()) {
			Debug::error("{$f} object type is undefined");
		}
		return $this->objectType;
	}

	public function onTable($db, $table): PrivilegeStatement{
		$this->setObjectType(DATABASE_OBJECT_TYPE_TABLE);
		$this->setDatabaseName($db);
		$this->setTableName($table);
		return $this;
	}

	public function onFunction($name): PrivilegeStatement{
		$this->setObjectType(DATABASE_OBJECT_TYPE_FUNCTION);
		$this->setTableName($name);
		return $this;
	}

	public function onProcedure($name): PrivilegeStatement{
		$this->setObjectType(DATABASE_OBJECT_TYPE_PROCEDURE);
		$this->setTableName($name);
		return $this;
	}

	public function setProxyUser($user_or_role){
		$f = __METHOD__;
		if($user_or_role == null) {
			unset($this->proxyUser);
			return null;
		}elseif($user_or_role instanceof DatabaseRoleData) {
			$user_or_role = $user_or_role->getUsernameHostString();
		}elseif(!is_string($user_or_role)) {
			Debug::error("{$f} user or role must be a string in the format 'username'@'hostname'");
		}
		return $this->proxyUser = $user_or_role;
	}

	public function hasProxyUser(){
		return isset($this->proxyUser);
	}

	public function getProxyUser(){
		$f = __METHOD__;
		if(!$this->hasProxyUser()) {
			Debug::error("{$f} proxy user is undefined");
		}
		return $this->proxyUser;
	}

	public static function proxyOn($user_or_role): PrivilegeStatement{
		$class = static::class;
		$st = new $class();
		$st->setProxyUser($user_or_role);
		return $st;
	}
}
