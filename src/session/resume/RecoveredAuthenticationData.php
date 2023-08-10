<?php
namespace JulianSeymour\PHPWebApplicationFramework\session\resume;

use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class RecoveredAuthenticationData extends FullAuthenticationData
{

	public function getDeterministicSecretKey(?PlayableUser $user = null)
	{
		$f = __METHOD__; //RecoveredAuthenticationData::getShortClass()."(".static::getShortClass().")->getDeterministicSecretKey()";
		if (! $this->hasDeterministicSecretKey()) {
			Debug::error("{$f} you must set the recovered deterministic secret key first");
		}
		return $this->getColumnValue(static::getDeterministicSecretKeyColumnName());
	}
}
