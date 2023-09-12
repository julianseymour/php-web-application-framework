<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\password\reset;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticateUseCase;
use JulianSeymour\PHPWebApplicationFramework\auth\PreauthenticationUseCase;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadTreeUseCase;
use JulianSeymour\PHPWebApplicationFramework\email\EmailAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;

class ForgotCredentialsUseCase extends PreauthenticationUseCase{

	public function processForgotCredentialsRequest(){
		$f = __METHOD__;
		try{
			$print = false;
			$mode = getInputParameter('select_login_forgot');
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$user_class = $this->getAuthenticatedUserClass();
			$correspondent = new $user_class();
			$query = new SelectStatement();
			if($print) {
				Debug::print("{$f} about to call {$user_class}->getTableName()");
				$correspondent->getTableName();
			}
			$query->setDatabaseName($correspondent->getDatabaseName());
			$query->setTableName($correspondent->getTableName());
			switch ($mode) {
				case 'forgot_password':
					if(! hasInputParameter('name')) {
						Debug::warning("{$f} username field is blank");
						return ERROR_NULL_USERNAME;
					}
					$name = NameDatum::normalize(getInputParameter('name'));
					$select = $correspondent->getNormalizedNameSelectStatement($name);
					$result = $select->executeGetResult($mysqli);
					$results = $result->fetch_all(MYSQLI_ASSOC);
					$result->free_result();
					$count = count($results);
					if($count === 0) {
						if($print) {
							Debug::warning("{$f} no results");
						}
						return RESULT_RESET_SUBMIT;
					}elseif($count > 1) {
						Debug::error("{$f} {$count} results");
					}
					$status = $correspondent->processQueryResultArray($mysqli, $results[0]);
					if($status !== SUCCESS) {
						Debug::error("{$f} loading user with name \"{$name}\" failed");
						return $this->setObjectStatus($status);
					}elseif(!$correspondent->getForgotPasswordEnabled()) {
						return $this->setObjectStatus(ERROR_FORGOT_PASSWORD_DISABLED);
					}
					break;
				case 'forgot_name':
					if(! hasInputParameter('email')) {
						Debug::warning("{$f} email address is undefined");
						return $this->setObjectStatus(ERROR_EMAIL_UNDEFINED);
					}
					$email = EmailAddressDatum::normalize(getInputParameter('email'));
					$status = $correspondent->load($mysqli, new WhereCondition("normalizedEmailAddress", OPERATOR_EQUALS), [
						$email
					]);
					if($status !== SUCCESS) {
						Debug::error("{$f} loading user with email \"{$email}\" failed");
						return $this->setObjectStatus($status);
					}elseif(!$correspondent->getForgotUsernameEnabled()) {
						$correspondent->setObjectStatus(ERROR_FORGOT_USERNAME_DISABLED);
					}
					break;
				default:
					Debug::printPost("{$f} invalid forgot credentials request method");
					break;
			}
			if(!$correspondent->hasSerialNumber()) {
				Debug::error("{$f} correspondent object has undefined object number");
			}
			$status = $correspondent->loadForeignDataStructures($mysqli);
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} loading foreign data structures returned error status \"{$err}\"");
			}elseif($correspondent->isRegistrable()) {
				registry()->register($correspondent);
			}
			$status = $correspondent->filterIpAddress($mysqli, $_SERVER['REMOTE_ADDR'], false);
			if($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} validating IP address returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$user = user();
			$correspondent->setCorrespondentObject($user);
			$user->setCorrespondentObject($correspondent);
			if(!$correspondent->hasEmailAddress()) {
				Debug::error("{$f} loaded a user who lacks email address");
			}
			$status = ResetPasswordConfirmationCode::submitStatic($mysqli, $correspondent);
			if($status !== RESULT_RESET_SUBMIT) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} submitting reset password confirmation code returned error status \"{$err}\"");
			}elseif($print) {
				Debug::print("{$f} successfully submat a confirmation code for password reset");
			}
			return $user->setObjectStatus($status);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function execute(): int{
		$f = __METHOD__;
		try{
			$print = false;
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$guest = AuthenticateUseCase::getAnonymousUser();
			app()->setUserData($guest);
			$status = $this->processForgotCredentialsRequest();
			$auth = new AuthenticateUseCase($this);
			$auth->validateTransition();
			$auth->execute();
			$load = new LoadTreeUseCase($this);
			$load->validateTransition();
			$load->execute();
			if($print){
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} returning with error status \"{$err}\"");
			}
			return $this->setObjectStatus($status);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getActionAttribute(): ?string{
		return "/forgot";
	}

	public function getAuthenticatedUserClass():?string{
		return config()->getNormalUserClass();
	}
}
