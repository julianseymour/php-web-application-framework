<?php
namespace JulianSeymour\PHPWebApplicationFramework\account;

use JulianSeymour\PHPWebApplicationFramework\account\role\RoleBasedPermission;
use Closure;

class EnabledAccountTypePermission extends RoleBasedPermission
{

	public function __construct(string $name, ?Closure $closure = null)
	{
		parent::__construct($name, $closure, [
			'enabled' => POLICY_REQUIRE,
			'guest' => POLICY_ALLOW,
			'admin' => POLICY_ALLOW
		]);
	}
}
