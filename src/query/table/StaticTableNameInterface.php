<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use JulianSeymour\PHPWebApplicationFramework\query\database\StaticDatabaseNameInterface;

interface StaticTableNameInterface extends StaticDatabaseNameInterface{
	
	static function getTableNameStatic():string;
	
	function getTableName():string;
	
	function hasTableName():bool;
}
