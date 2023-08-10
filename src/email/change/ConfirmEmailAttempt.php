<?php
namespace JulianSeymour\PHPWebApplicationFramework\email\change;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\CodeConfirmationAttempt;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;
use mysqli;

class ConfirmEmailAttempt extends CodeConfirmationAttempt
{

	/*
	 * public static function getTableNameStatic():string{
	 * return "confirm_email_attempts";
	 * }
	 */
	public static function getConfirmationCodeClass(): string
	{
		return ChangeEmailAddressConfirmationCode::class;
	}

	public static function getPhylumName(): string
	{
		return "confirmEmailAttempts";
	}

	public static function getAccessTypeStatic(): string
	{
		return ACCESS_TYPE_CHANGE_EMAIL;
	}

	public static function getSuccessfulResultCode()
	{
		return RESULT_CHANGEMAIL_SUCCESS;
	}

	protected function beforeInsertHook(mysqli $mysqli): int
	{
		$f = __METHOD__; //ConfirmEmailAttempt::getShortClass()."(".static::getShortClass().")->beforeInsertHook()";
		try {
			$status = $this->getObjectStatus();
			if ($status === ERROR_LINK_EXPIRED) {
				Debug::print("{$f} link expired; skipping write");
				return $status;
			}
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("{$f} returning normally with status \"{$err}\"");
			return parent::beforeInsertHook($mysqli);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getReasonLogged()
	{
		return BECAUSE_CHANGE_EMAIL;
	}

	public function getName()
	{
		return $this->getUserName();
	}

	public function isSecurityNotificationWarranted()
	{
		return true;
	}

	public static function getPrettyClassName(?string $lang = null)
	{
		return _("Email confirmation attempt");
	}

	public static function getPrettyClassNames(?string $lang = null)
	{
		return _("Email confirmation attempts");
	}

	public static function getIpLogReason()
	{
		return BECAUSE_CHANGE_EMAIL;
	}
}
