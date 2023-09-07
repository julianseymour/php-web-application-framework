<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;
use Exception;

class MfaOtpValidator extends Validator{

	use ColumnNameTrait;

	protected $user;

	public function __construct($index = null){
		parent::__construct();
		if (! empty($index)) {
			$this->setColumnName($index);
		}
	}

	public function getSpecialFailureStatus(){
		return ERROR_INVALID_MFA_OTP;
	}

	public function getUserData(): AuthenticatedUser{
		$f = __METHOD__;
		if (! $this->hasUserData()) {
			Debug::warning("{$f} user data is undefined; returning current user");
			return user();
		}
		return $this->user;
	}

	public function hasUserData(): bool{
		return isset($this->user) && $this->user instanceof AuthenticatedUser;
	}

	public function setUserData(?AuthenticatedUser $user): ?AuthenticatedUser{
		$f = __METHOD__;
		if ($user == null) {
			unset($this->user);
			return null;
		} elseif (! $user->hasMfaSeed()) {
			Debug::error("{$f} user lacks an MFA seed");
		} elseif (! $user->hasUsernameData()) {
			Debug::error("{$f} user lacks username data");
		}
		return $this->user = $user;
	}

	public function evaluate(&$validate_me): int{
		$f = __METHOD__;
		try {
			// return SUCCESS; //XXX delete this
			$print = false;
			if (is_object($validate_me)) {
				Debug::error("{$f} parameter 0 must be an array, not an object");
			} elseif (! is_array($validate_me)) {
				Debug::error("{$f} parameter 0 must be an array");
			}
			// return SUCCESS;
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$user = $this->getUserData();
			if (! $user->hasMfaSeed()) {
				Debug::error("{$f} MFA seed is undefined");
			} elseif (! $user->hasUsernameData()) {
				$uc = $user->getClass();
				Debug::error("{$f} username data is undefined for user of class \"{$uc}\"");
			}
			$index = $this->getColumnName();
			if (! array_key_exists($index, $validate_me)) {
				Debug::error("{$f} index \"{$index}\" was not posted");
			}
			$otp = $validate_me[$index];
			if ($user->isOTPInvalidated($mysqli, $otp)) {
				if ($print) {
					Debug::warning("{$f} MFA OTP has already been invalidated");
				}
				return $this->getSpecialFailureStatus();
			}
			// $mfa_seed = $user->getMfaSeed();
			$verify = $user->getColumn("MFASeed")->verifyOTP($otp);
			if ($verify) {
				if ($print) {
					Debug::print("{$f} MFA OTP verification successful");
				}
				return SUCCESS;
			} elseif ($print) {
				Debug::warning("{$f} MFA OTP verification failed");
			}
			return $this->getSpecialFailureStatus();
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
