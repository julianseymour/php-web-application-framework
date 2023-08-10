<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;
use mysqli;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;

abstract class AuthenticatedConfirmationCode extends ConfirmationCode{

	public function getKeypair(){
		$f = __METHOD__;
		$user = $this->getUserData();
		$class = $user->getClass();
		Debug::print("{$f} about to return {$class}->getKeyPair()");
		return $user->getKeypair();
	}

	public function getPublicKey():string{
		return $this->getUserData()->getPublicKey();
	}

	protected function encrypt(string $data): ?string{
		return $this->getUserData()->encrypt($data);
	}

	protected function decrypt(string $data): ?string{
		return $this->getUserData()->decrypt($data);
	}

	public function acquireUserData(mysqli $mysqli):?UserData{
		$f = __METHOD__;
		try {
			if ($this->hasUserData()) {
				$tco = $this->getUserData();
				if ($tco instanceof AnonymousUser) {
					Debug::error("{$f} true client object must not be anonymous");
				}
				return $tco;
			} else {
				Debug::warning("{$f} true client object is undefined");
			}
			$user = user();
			if ($user == null) {
				Debug::error("{$f} client object is undefined");
			}
			$status = $user->getObjectStatus();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} client object returned error status \"{$err}\"");
			} elseif ($user instanceof AnonymousUser) {
				Debug::warning("{$f} client object is anonymous");
				$this->setObjectStatus(ERROR_MUST_LOGIN);
			}
			Debug::print("{$f} returning normally");
			$this->setUserData($user);
			return $this->setUserData($user);
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
