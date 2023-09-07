<?php

namespace JulianSeymour\PHPWebApplicationFramework\db\credentials;

use mysqli;

abstract class PublicDatabaseCredentials extends DatabaseCredentials{

	public static function getDataType(): string{
		return DATATYPE_UNKNOWN;
	}

	public function beforeLoadHook(mysqli $mysqli): int{
		return ERROR_DEPRECATED;
	}

	public function initializeName():string{
		return $this->getName();
	}
}
