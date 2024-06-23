<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\condemn;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\common\UriTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class CondemnIpAddressUseCase extends UseCase{

	use UriTrait;

	protected $reasonLogged;

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	public function getActionAttribute(): ?string{
		return null;
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}

	public function setReasonLogged($reasonLogged){
		return $this->reasonLogged = $reasonLogged;
	}

	public function hasReasonLogged(){
		return isset($this->reasonLogged);
	}

	public function getReasonLogged(){
		return $this->reasonLogged;
	}

	public function execute(): int{
		$f = __METHOD__;
		try{
			$ip = new CondemnedIpAddress();
			if(!$ip->hasColumn("ipAddress")){
				Debug::error("{$f} IP address datum is undefined");
			}
			$ip->setIpAddress($_SERVER['REMOTE_ADDR']);
			if(app()->hasUserData()){
				$user = user();
				if($user instanceof PlayableUser && $user->getLoadedFlag()){
					$ip->setUserData($user);
				}
			}
			$ip->setReasonLogged($this->getReasonLogged());
			$ip->setUri($this->getUri());
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$status = $ip->insert($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} inserting condemned IP address returned error status \"{$err}\"");
			}else{
				Debug::print("{$f} successfully wrote condemned IP address");
			}
			db()->disconnect();
			header("HTTP/1.0 400 Bad Request");
			exit();
			// unset($ip);
			// unset($mysqli);
			// drop_request();
			return $this->setObjectStatus($status);
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
