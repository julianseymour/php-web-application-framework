<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\register;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;
use JulianSeymour\PHPWebApplicationFramework\account\activate\PreActivationConfirmationCode;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\auth\ReauthenticationEvent;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BlobDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\language\settings\LanguageSettingsData;
use Exception;
use mysqli;

class RegisteringUser extends NormalUser{

	public function __construct(){
		$f = __METHOD__;
		parent::__construct();
		$this->setHardResetCount(0);
		$this->setAccountType(ACCOUNT_TYPE_USER);
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$dsk = new BlobDatum("deterministicSecretKey");
		$dsk->volatilize();
		static::pushTemporaryColumnsStatic($columns, $dsk);
	}

	public function setDeterministicSecretKey($value, $unused = LOGIN_TYPE_UNDEFINED){
		return $this->setColumnValue("deterministicSecretKey", $value);
	}

	protected function beforeGenerateInitialValuesHook(): int{
		$f = __METHOD__;
		try {
			$ret = parent::beforeGenerateInitialValuesHook();
			$session = new LanguageSettingsData();
			$language = $session->getLanguageCode();
			$this->setLanguagePreference($language);
			$this->setFilterPolicy(POLICY_NONE);
			return $ret;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasGuestUser(): bool{
		return $this->hasForeignDataStructure("guest");
	}

	public function setGuestUser($struct): AnonymousUser{
		return $this->setForeignDataStructure("guest", $struct);
	}

	public function getGuestUser(): AnonymousUser{
		$f = __METHOD__;
		if (! $this->hasGuestUser()) {
			Debug::error("{$f} anonymous user data was never assigned");
		}
		return $this->getForeignDataStructure("guest");
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @param AnonymousUser $user
	 * @return int
	 */
	public function migrateAnonymousMessages($mysqli): int{
		$f = __METHOD__;
		try {
			ErrorMessage::deprecated($f);
			Debug::print("{$f} entered; about to get anonymous user");
			$anon = $this->getAnonymousUser();
			Debug::print("{$f} about to mark anonymous user as having authenticated");
			$anon->setColumnValue('hasEverAuthenticated', true);
			Debug::print("{$f} returned from marking anonymous user as having authenticated");
			$status = $anon->update($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} setting hasEverAuthenticated flag to 1 returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			Debug::print("{$f} about to rename message and notification tables");
			$post = getInputParameters();
			if (! isset($post['name'])) {
				Debug::error("{$f} name was not posted");
			}
			$status = $anon->writeIntroductoryMessageNote($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} writing anonymous user's introductory message notifications returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			Debug::print("{$f} returning normally");
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function afterInsertHook($mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			if($print){
				Debug::print("{$f} about to call parent function");
			}
			$status = parent::afterInsertHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} registration returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("{$f} parent function executed successfully");
			}
			$status = PreActivationConfirmationCode::submitStatic($mysqli, $this);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} error finishing registration: \"{$err}\"");
			}elseif($print){
				Debug::print("{$f} successfully submitted PreActivationConfirmationCode");
			}
			if ($print) {
				Debug::print("{$f} about to insert registration IP address");
			}
			$status = $this->insertRegistrationIpAddress($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} insertRegistrationIpAddress returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully inserted registration IP address");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function insertRegistrationIpAddress(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$event = new ReauthenticationEvent();
			$event->setUserData($this);
			$event->setObjectStatus(SUCCESS);
			$this->setRequestEventObject($event);
			$this->filterIpAddress($mysqli, $_SERVER['REMOTE_ADDR'], false);
			Debug::warning("{$f} not implemented -- denote the IP address that was just inserted as the one used for registration");
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getPermissionStatic($name, $object){
		$f = __METHOD__;
		switch ($name) {
			case DIRECTIVE_INSERT:
			case DIRECTIVE_PREINSERT_FOREIGN:
			case DIRECTIVE_POSTINSERT_FOREIGN:
			case DIRECTIVE_UPDATE:
				return new AnonymousAccountTypePermission($name);
			default:
				return parent::getPermissionStatic($name, $object);
		}
	}
}

