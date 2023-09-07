<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\database;

interface StaticDatabaseNameInterface{
	
	static function getDatabaseNameStatic():string;
	
	function getDatabaseName():string;
	
	function hasDatabaseName():bool;
}
