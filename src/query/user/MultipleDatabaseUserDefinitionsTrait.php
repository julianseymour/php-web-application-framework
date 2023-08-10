<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\user;

use JulianSeymour\PHPWebApplicationFramework\common\arr\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\query\role\DatabaseRoleData;

trait MultipleDatabaseUserDefinitionsTrait
{

	use ArrayPropertyTrait;

	public function setUsers($values)
	{
		return $this->setArrayProperty("users", $values);
	}

	public function hasUsers()
	{
		return $this->hasArrayProperty("users");
	}

	/**
	 *
	 * @return DatabaseRoleData[]
	 */
	public function getUsers()
	{
		return $this->getProperty("users");
	}

	public function getUser($i): DatabaseRoleData
	{
		return $this->getArrayPropertyValue("users", $i);
	}

	public function pushUsers(...$values)
	{
		return $this->pushArrayProperty("users", ...$values);
	}

	public function mergeUsers($values)
	{
		return $this->mergeArrayProperty("users", $values);
	}

	public function getUserCount()
	{
		return $this->getArrayPropertyCount("users");
	}

	public function user(...$values)
	{
		$this->setUsers($values);
		return $this;
	}
}