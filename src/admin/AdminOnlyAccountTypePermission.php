<?php
namespace JulianSeymour\PHPWebApplicationFramework\admin;

use JulianSeymour\PHPWebApplicationFramework\account\role\RoleBasedPermission;
use Closure;

class AdminOnlyAccountTypePermission extends RoleBasedPermission
{

	public function __construct(string $name, ?Closure $closure = null)
	{
		parent::__construct($name, $closure, [
			ACCOUNT_TYPE_ADMIN => POLICY_REQUIRE
		]);
	}
}
