<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\owner;

use JulianSeymour\PHPWebApplicationFramework\auth\permit\StaticPermissionGatewayInterface;

class OwnerPermissionGateway implements StaticPermissionGatewayInterface
{

	public static function getPermissionStatic($name, $object)
	{
		return new OwnerPermission($name);
	}
}
