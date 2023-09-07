<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\database;

trait StaticDatabaseNameTrait{
	
	public abstract static function getDatabaseNameStatic():string;
	
	public function getDatabaseName():string{
		return static::getDatabaseNameStatic();
	}
	
	public function hasDatabaseName():bool{
		return true;
	}
}
