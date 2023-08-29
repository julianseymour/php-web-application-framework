<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use mysqli;

/**
 * A set of data that is processed from GET, POST or php://input
 *
 * @author j
 */
abstract class SuperGlobalData extends DataStructure{

	public static function getDatabaseNameStatic():string{
		return "error";
	}
	
	public static function getTableNameStatic(): string{
		$f = __METHOD__;
		return ErrorMessage::unimplemented($f);
	}

	public static function getPermissionStatic(string $name, $data){
		return SUCCESS;
	}

	public function delete(mysqli $mysqli):int{
		foreach ($this->getColumns() as $column) {
			$column->unsetValue();
		}
		return SUCCESS;
	}
}
