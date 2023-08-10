<?php
namespace JulianSeymour\PHPWebApplicationFramework\admin;

use JulianSeymour\PHPWebApplicationFramework\auth\permit\StaticPermissionGatewayInterface;

class AdminOnlyPermissionGateway implements StaticPermissionGatewayInterface
{

	public static function getPermissionStatic($name, $object)
	{
		return new AdminOnlyAccountTypePermission($name);
	}
}
