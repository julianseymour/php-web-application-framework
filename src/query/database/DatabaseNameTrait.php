<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\database;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DatabaseNameTrait{

	protected $databaseName;

	public function setDatabaseName(?string $db):?string{
		$f = __METHOD__;
		if ($db == null) {
			unset($this->databaseName);
			return null;
		} elseif (! is_string($db)) {
			Debug::error("{$f} database name must be a string");
		}
		return $this->databaseName = $db;
	}

	public function hasDatabaseName():bool{
		return !empty($this->databaseName);
	}

	public function getDatabaseName():string{
		$f = __METHOD__;
		if(!$this->hasDatabaseName()){
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} database name is undefined. Declared {$decl}");
		}
		return $this->databaseName;
	}

	public function withDatabaseName(?string $name):object{
		$this->setDatabaseName($name);
		return $this;
	}
}
