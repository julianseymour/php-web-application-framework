<?php
namespace JulianSeymour\PHPWebApplicationFramework\account;

use JulianSeymour\PHPWebApplicationFramework\account\role\RoleBasedPermission;
use Closure;

class SelfPermission extends RoleBasedPermission
{

	/*
	 * public function permit($user, $object, ...$params){
	 * if(!$user instanceof Administrator && !DataStructure::equals($user, $object)){
	 * return FAILURE;
	 * }elseif(!$this->hasPermittanceClosure()){
	 * return SUCCESS;
	 * }
	 * $arr = [];
	 * if(isset($params) && !empty($params)){
	 * foreach($params as $p){
	 * array_push($arr, $p);
	 * }
	 * }
	 * return parent::permit($user, $object, ...$arr);
	 * }
	 */
	public function __construct(string $name, ?Closure $closure = null)
	{
		parent::__construct($name, $closure, [
			"self" => POLICY_REQUIRE
		]);
	}
}
