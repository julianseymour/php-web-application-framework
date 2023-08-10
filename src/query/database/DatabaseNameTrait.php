<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\database;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DatabaseNameTrait
{

	protected $databaseName;

	public function setDatabaseName($db)
	{
		$f = __METHOD__; //"DatabaseNameTrait(".static::getShortClass().")->setDatabaseName()";
		if ($db == null) {
			unset($this->databaseName);
			return null;
		} elseif (! is_string($db)) {
			Debug::error("{$f} database name must be a string");
		}
		return $this->databaseName = $db;
	}

	public function hasDatabaseName()
	{
		return ! empty($this->databaseName);
	}

	public function getDatabaseName(): string
	{
		$f = __METHOD__; //"DatabaseNameTrait(".static::getShortClass().")->getDatabaseName()";
		if (! $this->hasDatabaseName()) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} database name is undefined. Declared {$decl}");
		}
		return $this->databaseName;
	}

	public function withDatabaseName($name)
	{
		$this->setDatabaseName($name);
		return $this;
	}
}
