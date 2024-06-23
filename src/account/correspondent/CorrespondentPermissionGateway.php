<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\correspondent;

use JulianSeymour\PHPWebApplicationFramework\auth\permit\StaticPermissionGatewayInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class CorrespondentPermissionGateway implements StaticPermissionGatewayInterface{

	public static function getPermissionStatic($name, $object){
		$f = __METHOD__;
		switch($name){
			case DIRECTIVE_INSERT:
			case DIRECTIVE_UPDATE:
			case DIRECTIVE_PREINSERT_FOREIGN:
			case DIRECTIVE_POSTINSERT_FOREIGN:
			case DIRECTIVE_PREUPDATE_FOREIGN:
			case DIRECTIVE_POSTUPDATE_FOREIGN:
				return new CorrespondentPermission($name);
			default:
				Debug::error("{$f} invalid permission name \"{$name}\"");
		}
	}
}
