<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use JulianSeymour\PHPWebApplicationFramework\query\database\StaticDatabaseNameTrait;

trait StaticTableNameTrait{
	
	use StaticDatabaseNameTrait;
	
	public abstract static function getTableNameStatic():string;
	
	public function getTableName():string{
		return static::getTableNameStatic();
	}
	
	public function hasTableName():bool{
		return true;
	}
}
