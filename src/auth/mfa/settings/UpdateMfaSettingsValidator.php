<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa\settings;

use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\MfaOtpValidator;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordValidator;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class UpdateMfaSettingsValidator extends PasswordValidator
{

	public function __construct()
	{
		$f = __METHOD__; //UpdateMfaSettingsValidator::getShortClass()."(".static::getShortClass().")->__construct()";
		/*
		 * if(!isset($use_case)){
		 * Debug::error("{$f} use case is undefined");
		 * }
		 */
		parent::__construct("mfa-password");
		$user = user();
		$server_cmd = directive();
		if ($server_cmd === DIRECTIVE_UPDATE || ($server_cmd === DIRECTIVE_UNSET && $user->getMFAStatus() === MFA_STATUS_ENABLED)) {
			$this->setCovalidateWhen(COVALIDATE_AFTER);
			$otp1 = new MfaOtpValidator('mfa-confirm1');
			$otp2 = new MfaOtpValidator('mfa-confirm2');
			$this->pushCovalidators($otp1, $otp2);
		}
	}

	public function evaluate(&$validate_me): int
	{
		$f = __METHOD__; //UpdateMfaSettingsValidator::getShortClass()."(".static::getShortClass().")->evaluate()";
		$user = user();
		$server_cmd = directive();
		if ($server_cmd === DIRECTIVE_REGENERATE || $server_cmd === DIRECTIVE_UPDATE) {
			if ($user->getMFAStatus() !== MFA_STATUS_DISABLED) {
				Debug::warning("{$f} MFA status must be disabled to update");
			}
		}
		switch ($server_cmd) {
			case DIRECTIVE_UNSET:
			case DIRECTIVE_UPDATE:
				if (! $user->hasMfaSeed()) {
					Debug::warning("{$f} user does not have an MFA seed");
					return $this->setObjectStatus(ERROR_NULL_MFA_SEED);
				}
			default:
		}
		Debug::print("{$f} returning parent function");
		return parent::evaluate($validate_me);
	}
}
