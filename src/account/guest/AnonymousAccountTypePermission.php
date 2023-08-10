<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\guest;

use JulianSeymour\PHPWebApplicationFramework\account\role\RoleBasedPermission;
use Closure;

class AnonymousAccountTypePermission extends RoleBasedPermission
{

	public function __construct(string $name, ?Closure $closure = null)
	{
		parent::__construct($name, $closure, [
			"guest" => POLICY_REQUIRE,
			'enabled' => POLICY_ALLOW,
			'admin' => POLICY_BLOCK
		]);
	}
}
