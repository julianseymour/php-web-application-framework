<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth;

use JulianSeymour\PHPWebApplicationFramework\account\role\RoleBasedPermission;
use Closure;

class AuthenticatedAccountTypePermission extends RoleBasedPermission
{

	public function __construct(string $name, ?Closure $closure = null)
	{
		parent::__construct($name, $closure, [
			'enabled' => POLICY_ALLOW,
			'guest' => POLICY_BLOCK,
			'admin' => POLICY_ALLOW
		]);
	}
}
