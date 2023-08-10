<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\lockout;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\CodeConfirmationAttempt;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use mysqli;

class LockoutWaiverAttempt extends CodeConfirmationAttempt{

	protected static $confirmationCodeAlreadyUsedStatus = ERROR_ALREADY_WAIVED;

	public static function getSuccessfulResultCode(){
		return RESULT_BFP_WAIVER_SUCCESS;
	}

	public static function getPhylumName(): string{
		return "lockoutWaivers";
	}

	public function getParentKey(){
		return $this->getUserKey();
	}

	public function getName(){
		return $this->getUserNormalizedName();
	}

	public static function getAccessTypeStatic(): string{
		return ACCESS_TYPE_LOCKOUT_WAIVER;
	}

	public static function getConfirmationCodeClass(): string{
		return LockoutConfirmationCode::class;
	}

	public function bruteforceProtection(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			// 3. see if the user already has a valid waiver
			$user = $this->getUserData();
			$select = $this->select()
				->where(new AndCommand($this->whereIntersectionalHostKey($user->getClass(), "userKey"), new WhereCondition('loginResult', OPERATOR_EQUALS), new WhereCondition('insertTimestamp', OPERATOR_GREATERTHAN)))
				->orderBy(new OrderByClause("insertTimestamp", DIRECTION_DESCENDING))
				->limit(1)
				->withTypeSpecifier('ssii')
				->withParameters([
				$user->getIdentifierValue(),
				"userKey",
				RESULT_BFP_WAIVER_SUCCESS,
				$this->generateExpiredTimestamp()
			]);
			if ($print) {
				Debug::print("{$f} select statement is \"{$select}\"");
			}
			$count = $select->executeGetResultCount($mysqli);
			if ($count === 1) {
				if ($print) {
					Debug::print("{$f} login waiver already valid");
				}
				return $this->setLoginResult(RESULT_BFP_WAIVER_SUCCESS);
			} elseif ($print) {
				Debug::print("{$f} there is not an existing lockout waiver in effect");
			}
			$result = parent::bruteforceProtection($mysqli);
			if ($print) {
				$err = ErrorMessage::getResultMessage($result);
				Debug::print("{$f} parent function returned error status \"{$err}\"");
			}
			return $result;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isSecurityNotificationWarranted(){
		return true;
	}

	public static function getPrettyClassName(?string $lang = null){
		return _("Lockout waiver attempt");
	}

	public static function getPrettyClassNames(?string $lang = null){
		return _("Lockout waiver attempts");
	}

	public static function getIpLogReason(){
		return BECAUSE_WAIVER;
	}

	public static function getPermissionStatic(string $name, $data){
		if ($name === DIRECTIVE_INSERT) {
			return new AnonymousAccountTypePermission($name);
		}
		return parent::getPermissionStatic($name, $data);
	}
}
