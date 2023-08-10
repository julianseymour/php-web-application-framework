<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;
use mysqli;

abstract class ValidateAuthenticatedConfirmationCodeUseCase extends ValidateConfirmationCodeUseCase
{

	public static function requireAnonymousConfirmation()
	{
		return false;
	}

	public function afterLoadHook(mysqli $mysqli): int
	{
		$f = __METHOD__; //ValidateAuthenticatedConfirmationCodeUseCase::getShortClass()."(".static::getShortClass().")->afterLoadHook()";
		try {
			$status = parent::afterLoadHook($mysqli);
			$user = user();
			if ($user instanceof AnonymousUser) {
				Debug::print("{$f} user is anonymous -- about to check whether the user is logging in to activate");
				$post = getInputParameters();
				if (isset($post['activate'])) {
					Debug::error("{$f} yes, the user is logging in to activate, and this should have been caught in the parent function's reauthenticate");
					$key = UserData::getKeyFromName($post['name']);
					$user_class = mods()->getUserClass(NormalUser::getAccountTypeStatic());
					$correspondent = $user_class::getObjectFromKey($mysqli, $key);
					$user->setCorrespondentObject($correspondent);
					$user->setUserData($correspondent);
				} else {
					Debug::print("{$f} no, the user is not logging in to activate");
					return $this->setObjectStatus(ERROR_MUST_LOGIN);
				}
			}
			return $status;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function decrypt(string $data): ?string
	{
		return user()->decrypt($data);
	}
}
