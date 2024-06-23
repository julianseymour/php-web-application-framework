<?php

namespace JulianSeymour\PHPWebApplicationFramework\session\resume;

use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class RecoveredAuthenticationData extends FullAuthenticationData{

	public function getDeterministicSecretKey(?PlayableUser $user = null):string{
		$f = __METHOD__;
		if(!$this->hasDeterministicSecretKey()){
			Debug::error("{$f} you must set the recovered deterministic secret key first");
		}
		return $this->getColumnValue(static::getDeterministicSecretKeyColumnName());
	}
}
