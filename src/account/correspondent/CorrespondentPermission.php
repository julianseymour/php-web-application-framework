<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\correspondent;

use JulianSeymour\PHPWebApplicationFramework\account\role\RoleBasedPermission;
use Closure;

class CorrespondentPermission extends RoleBasedPermission
{

	/*
	 * public function permit($user, $object, ...$params){
	 * $f = __METHOD__; //CorrespondentPermission::getShortClass()."(".static::getShortClass().")->permit()";
	 * $print = false;
	 * $user_key = $user->getIdentifierValue();
	 * if($print){
	 * $oc = $object->getClass();
	 * Debug::print("{$f} about to call {$oc}->getCorrespondentKey()");
	 * }
	 * $correspondent_key = $object->getCorrespondentKey();
	 * if(empty($user_key)){
	 * Debug::warning("{$f} user key is null or empty string");
	 * return FAILURE;
	 * }elseif(empty($correspondent_key)){
	 * Debug::warning("{$f} correspondent key is null or empty string");
	 * return FAILURE;
	 * }elseif($user_key !== $correspondent_key){
	 * Debug::print("{$f} user key \"{$user_key}\" does not match correspondent key \"{$correspondent_key}\"");
	 * return FAILURE;
	 * }elseif($this->hasPermittanceClosure()){
	 * $arr = [];
	 * if(isset($params) && !empty($params)){
	 * foreach($params as $p){
	 * array_push($arr, $p);
	 * }
	 * }
	 * return parent::permit($user, $object, ...$arr);
	 * }
	 * return SUCCESS;
	 * }
	 */
	public function __construct(string $name, ?Closure $closure = null)
	{
		parent::__construct($name, $closure, [
			'correspondent' => POLICY_REQUIRE
		]);
	}
}
