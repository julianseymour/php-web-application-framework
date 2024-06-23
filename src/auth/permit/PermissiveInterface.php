<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\permit;

interface PermissiveInterface{
	
	function hasPermissionGateway():bool;
	function setPermissionGateway($gateway);
	function getPermissionGateway();
	static function hasStaticPermissionGatewayClass():bool;
	static function getStaticPermissionGatewayClass();
	function hasPermission($name):bool;
	function setPermission($name, $closure);
	function hasSinglePermissionGateway($name):bool;
	function setSinglePermissionGateway($name, $gateway);
	function getSinglePermissionGateway($name);
	function getPermission(string $name);
	function releasePermission($name);
	function permit($user, $permission_name, ...$params);
	function hasPermissions(...$keys):bool;
	function setPermissions(?array $permissions):?array;
	function getPermissions(...$keys):array;
	function hasSinglePermissionGateways(...$keys):bool;
	function setSinglePermissionGateways(?array $gateways):?array;
	function getSinglePermissionGateways(...$keys):array;
	function copyPermissions(PermissiveInterface $that):int;
}