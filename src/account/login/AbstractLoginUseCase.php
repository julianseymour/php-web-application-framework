<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\login;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\PreauthenticationUseCase;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\PreMultifactorAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\ClientUseCaseInterface;
use Exception;
use mysqli;

abstract class AbstractLoginUseCase extends PreauthenticationUseCase implements ClientUseCaseInterface{

	public function initializeAccessAttempt(mysqli $mysqli, LoginAttempt $attempt): int{
		$f = __METHOD__;
		try{
			$print = false;
			if(!$attempt->hasUserData()){
				if($print){
					Debug::print("{$f} user data is undefined; about to load it");
				}
				$user = $this->loadUserData($mysqli, $attempt);
				if($user === null){
					$status = $attempt->getObjectStatus();
					if($print){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} loadUserData returned null; login attempt has object status \"{$err}\"");
					}
					return $status;
				}elseif($print){
					Debug::print("{$f} successfully loaded user data");
				}
			}elseif($print){
				Debug::print("{$f} user data is already defined");
			}
			$half = new PreMultifactorAuthenticationData();
			if($half->hasSignature()){
				deallocate($half);
				if($attempt->hasUserKey()){
					if($print){
						$user_key = $attempt->getUserKey();
						Debug::print("{$f} user key is already defined as \"{$user_key}\"");
					}
					return $attempt->setObjectStatus(SUCCESS);
				}
				Debug::error("{$f} disabled?");
			}
			deallocate($half);
			$attempt->setUserName(NameDatum::normalize(getInputParameter("name")));
			$attempt->generateInsertTimestamp();
			$attempt->setInsertIpAddress($_SERVER['REMOTE_ADDR']);
			$attempt->setUserAgent($_SERVER['HTTP_USER_AGENT']);
			$status = $attempt->getObjectStatus();
			if($status !== SUCCESS && $status !== STATUS_UNINITIALIZED){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} error status \"{$err}\"");
				return $attempt->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully initialised request attempt");
			}
			return $attempt->setObjectStatus(SUCCESS);
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
