<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\correspondent;

use function JulianSeymour\PHPWebApplicationFramework\db;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use mysqli;

class GetCorrespondentUseCase extends UseCase{

	public function hasImplicitParameter(string $name): bool{
		return $this->getPredecessor()->hasImplicitParameter($name);
	}

	public function getImplicitParameter(string $name){
		return $this->getPredecessor()->getImplicitParameter($name);
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @param UseCase $that
	 * @return UserData
	 */
	public function execute(): int{
		$f = __METHOD__;
		try{
			$print = false;
			if(user()->hasCorrespondentObject()) {
				if($print) {
					Debug::print("{$f} user already has a correspondent object");
				}
				return SUCCESS;
			}elseif(user()->hasForeignDataStructureList("correspondent")) {
				if($print) {
					Debug::print("{$f} correspondent is one of the user's children");
				}
				$correspondent = user()->getFirstRelationship("correspondent");
				if(! isset($correspondent)) {
					$class = user()->getClass();
					Debug::error("{$f} failed to acquireCorrespondentObject for class \"{$class}\"");
					static::debugErrorStatic($f, ERROR_NULL_CORRESPONDENT_OBJECT);
				}
			}else{
				$predecessor = $this->getPredecessor();
				if(hasInputParameter('correspondentKey', $predecessor)) {
					if(! hasInputParameter("correspondentAccountType", $predecessor)) {
						Debug::error("{$f} correspondent type was not posted");
					}elseif($print) {
						Debug::print("{$f} both correspondent key and account types are defined as input parameters");
					}
					$class = mods()->getUserClass(getInputParameter("correspondentAccountType", $predecessor));
					$mysqli = db()->getConnection(PublicReadCredentials::class);
					$correspondent = $class::getObjectFromKey($mysqli, getInputParameter("correspondentKey", $predecessor));
					if($correspondent === null) {
						Debug::warning("{$f} correspondent object returned null");
						return FAILURE;
					}
					$status = $correspondent->getObjectStatus();
					if($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($this->setObjectStatus($status));
						Debug::warning("{$f} correspondent object has error status \"{$err}\"");
					}elseif($print) {
						Debug::print("{$f} correspondent object loaded successfully");
					}
					$status = $correspondent->loadForeignDataStructures($mysqli);
					if($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} loading correspondent's foreign data structures returned error status \"{$err}\"");
					}elseif($print) {
						Debug::print("{$f} successfully loaded correspondent's foreign data structures");
					}
				}else{
					Debug::warning("{$f} you're shit out of luck");
					return FAILURE;
				}
			}
			$correspondent->setCorrespondentObject(user());
			user()->setCorrespondentObject($correspondent);
			return SUCCESS;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	public function getActionAttribute(): ?string{
		ErrorMessage::unimplemented(f(static::class));
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}
}
