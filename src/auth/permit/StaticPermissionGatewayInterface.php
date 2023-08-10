<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\permit;

interface StaticPermissionGatewayInterface
{

	public static function getPermissionStatic(string $name, $object);
}
