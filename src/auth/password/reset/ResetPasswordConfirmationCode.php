<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\password\reset;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\AnonymousConfirmationCode;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class ResetPasswordConfirmationCode extends AnonymousConfirmationCode
{

	protected static $requestTimeoutDuration = 300;

	public function setName($name)
	{
		return $name;
	}

	public static function getSentEmailStatus()
	{
		return RESULT_RESET_SUBMIT;
	}

	protected function afterGenerateInitialValuesHook(): int{
		$f = __METHOD__;
		try {
			// Debug::print("{$f} entered");
			$post = getInputParameters();
			if (! isset($post['select_login_forgot'])) {
				Debug::warning("{$f} reason logged is undefined");
				$reason = BECAUSE_NOREASON;
			} else {
				$mode = $post['select_login_forgot'];
				switch ($mode) {
					case 'forgot_password':
						$reason = BECAUSE_FORGOTPASS;
						break;
					case 'forgot_name':
						$reason = BECAUSE_FORGOTNAME;
						break;
					default:
						Debug::error("{$f} invalid forgot credentials mode \"{$mode}\"");
						$reason = BECAUSE_NOREASON;
						break;
				}
			}
			$this->setReasonLogged($reason);
			Debug::print("{$f} about to call parent function");
			$status = parent::afterGenerateInitialValuesHook();
			Debug::print("{$f} returning normally");
			return $status;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setEmailAddress($email){
		return $email;
	}

	public function isSecurityNotificationWarranted():bool{
		return true;
	}

	public static function getConfirmationUriStatic($suffix)
	{
		return WEBSITE_URL . "/reset/{$suffix}";
	}

	public static function getEmailNotificationClass():string{
		return ResetPasswordEmail::class;
	}

	public static function getConfirmationCodeTypeStatic(){
		return ACCESS_TYPE_RESET;
	}

	public static function getReasonLoggedStatic(){
		return BECAUSE_RESET;
	}

	public static function getPermissionStatic(string $name, $data){
		$f = __METHOD__;
		switch ($name) {
			case DIRECTIVE_INSERT:
				if (user() instanceof AnonymousUser) {
					Debug::print("{$f} current user is guest -- this should succeed");
				}
				return new AnonymousAccountTypePermission($name);
			default:
				return parent::getPermissionStatic($name, $data);
		}
	}

	public function getFraudReportUri(): string{
		return WEBSITE_URL . "/reset_fraud/" . $this->getIdentifierValue();
	}
}
