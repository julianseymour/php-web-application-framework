<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\correspondent;

use JulianSeymour\PHPWebApplicationFramework\account\role\RoleBasedPermission;
use Closure;

class CorrespondentPermission extends RoleBasedPermission{
	
	public function __construct(string $name, ?Closure $closure = null){
		parent::__construct($name, $closure, [
			'correspondent' => POLICY_REQUIRE
		]);
	}
}
