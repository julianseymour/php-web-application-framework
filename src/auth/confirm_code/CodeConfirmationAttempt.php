<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\security\access\AccessAttempt;
use Exception;
use mysqli;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;

abstract class CodeConfirmationAttempt extends AccessAttempt{

	/**
	 *
	 * @return ConfirmationCode
	 */
	public abstract static function getConfirmationCodeClass();

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try {
			parent::declareColumns($columns, $ds);
			$ccc = static::getConfirmationCodeClass();
			$confirmationCodeKey = new ForeignKeyDatum("confirmationCodeKey");
			$confirmationCodeKey->setNullable(true);
			$confirmationCodeKey->setDefaultValue(null);
			$confirmationCodeKey->setForeignDataStructureClass($ccc);
			$confirmationCodeKey->setConstraintFlag(true);
			$confirmationCodeKey->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
			$confirmationCodeKey->setOnUpdate(REFERENCE_OPTION_CASCADE);
			$confirmationCodeKey->setOnDelete(REFERENCE_OPTION_SET_NULL);
			$confirmationCodeType = new StringEnumeratedDatum("confirmationCodeType");
			$confirmationCodeType->setValidEnumerationMap([
				ACCESS_TYPE_ACTIVATION,
				ACCESS_TYPE_CHANGE_EMAIL,
				ACCESS_TYPE_UNLISTED_IP_ADDRESS,
				ACCESS_TYPE_RESET,
				ACCESS_TYPE_LOCKOUT_WAIVER
			]);
			$confirmationCodeType->setValue($ccc::getConfirmationCodeTypeStatic());
			static::pushTemporaryColumnsStatic($columns, $confirmationCodeKey, $confirmationCodeType);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getConfirmationCodeAlreadyUsedStatus(){
		return ERROR_LINK_EXPIRED;
	}

	public function acquireUserData(mysqli $mysqli):?UserData{
		$f = __METHOD__;
		try {
			if ($this->hasUserData()) {
				Debug::print("{$f} client is already defined");
				return $this->getUserData();
			}
			Debug::print("{$f} about to debug print GET");
			$confirmation_code = $this->acquireConfirmationCodeObject($mysqli);
			$client = $confirmation_code->acquireUserData($mysqli);
			if (! $confirmation_code->hasSecretCode()) {
				Debug::error("{$f} confirmation code does not have its secret code");
			}
			// $this->setUserData($client);
			return $this->setUserData($client);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasConfirmationCodeKey(): bool{
		return $this->hasColumnValue('confirmationCodeKey');
	}

	public function hasConfirmationCodeObject(): bool{
		return $this->hasForeignDataStructure('confirmationCodeKey');
	}

	public function getConfirmationCodeKey():?string{
		$f = __METHOD__;
		try {
			if ($this->hasConfirmationCodeKey()) {
				return $this->getColumnValue('confirmationCodeKey');
			} elseif ($this->hasConfirmationCodeObject()) {
				$confirmation_code = $this->getConfirmationCodeObject();
				$rk = $confirmation_code->getIdentifierValue();
				return $this->setConfirmationCodeKey($rk);
			}
			return null;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setConfirmationCodeKey($key){
		return $this->setColumnValue('confirmationCodeKey', $key);
	}

	/**
	 *
	 * @return ConfirmationCode
	 */
	public function getConfirmationCodeObject(){
		return $this->getForeignDataStructure('confirmationCodeKey');
	}

	public function setConfirmationCodeObject($confirmation_code){
		$f = __METHOD__;
		try {
			if (isset($confirmation_code)) {
				$status = $confirmation_code->getObjectStatus();
				if ($status === SUCCESS) {
					$rk = $confirmation_code->getIdentifierValue();
					$this->setConfirmationCodeKey($rk);
				} elseif ($status === STATUS_INITIALIZATION_IN_PROGRESS) {
					Debug::warning("{$f} confirmation code key is undefined");
					// return $confirmation_code;
				} else {
					$status = $confirmation_code->getObjectStatus();
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} confirmation code object has error status \"{$err}\"");
				}
			} else {
				Debug::print("{$f} confirmation code object is undefined");
			}
			return $this->setForeignDataStructure('confirmationCodeKey', $confirmation_code);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static final function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try {
			parent::reconfigureColumns($columns, $ds);
			$indices = [
				"reasonLogged"
			];
			foreach ($indices as $index) {
				if (! array_key_exists($index, $columns)) {
					Debug::warning("{$f} array key \"{$index}\" does not exist");
					continue;
				}
				$columns[$index]->volatilize();
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static final function getTableNameStatic(): string{
		return "code_confirmation_attempts";
	}
}
