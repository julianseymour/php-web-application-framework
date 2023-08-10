<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\owner;

use JulianSeymour\PHPWebApplicationFramework\account\role\RoleBasedPermission;
use Closure;

class OwnerPermission extends RoleBasedPermission
{

	public function __construct(string $name, ?Closure $closure = null)
	{
		parent::__construct($name, $closure, [
			"owner" => POLICY_REQUIRE
		]);
	}
}
