<?php

namespace JulianSeymour\PHPWebApplicationFramework\account;

use JulianSeymour\PHPWebApplicationFramework\account\role\RoleBasedPermission;
use Closure;

class SelfPermission extends RoleBasedPermission{
	
	public function __construct(string $name, ?Closure $closure = null)
	{
		parent::__construct($name, $closure, [
			"self" => POLICY_REQUIRE
		]);
	}
}
