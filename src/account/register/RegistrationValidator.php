<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\register;

use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\security\captcha\hCaptchaValidator;
use JulianSeymour\PHPWebApplicationFramework\security\honeypot\HoneypotValidator;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\AntiXsrfTokenValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\CheckboxValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\FormDataIndexValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\UniqueValidator;

class RegistrationValidator extends FormDataIndexValidator
{

	public function __construct($use_case)
	{
		parent::__construct(new RegistrationForm(ALLOCATION_MODE_FORM, new RegisteringUser()));
		// 1. anti-XSRF token validator
		$xsrf = new AntiXsrfTokenValidator('/register');
		// 2. honeypot validator
		$honey = new HoneypotValidator(RegistrationForm::class);
		// 3. hCaptcha validator
		$captcha = new hCaptchaValidator();
		// 4. agree terms checkbox validator
		$tos = new CheckboxValidator("agree_tos");
		$tos->setSpecialFailureStatus(ERROR_AGREE_TOS);
		// 5. form data indices validator ($this)
		$this->pushCovalidators($xsrf, $honey, $captcha, $tos);
		$this->setCovalidateWhen(COVALIDATE_BEFORE);
	}

	public static function getUsernameAvailableValidator($searchname)
	{
		return new UniqueValidator(NormalUser::class, [
			"normalizedName"
		], 's', [
			$searchname
		]);
	}

	public function getSubvalidatorByIndex($index, &$arr = null)
	{
		$f = __METHOD__; //RegistrationValidator::getShortClass()."(".static::getShortClass().")->getSubvalidatorByIndex(~, {$index})";
		switch ($index) {
			/*
			 * case NameDatum::getColumnNameStatic(): //username
			 * //a. is valid username
			 * $username_valid = new DatumValidator(new NameDatum());
			 * //b. is not already taken
			 * if(!array_key_exists("name", $arr)){
			 * Debug::printArray($arr);
			 * if(array_key_exists("name", $arr)){
			 * Debug::printPost("{$f} on the other hand, post is getting updated properly");
			 * }else{
			 * Debug::printPost("{$f} looks like a honeypot problem");
			 * }
			 * }
			 * $username_available = static::getUsernameAvailableValidator($arr["name"]);
			 * $username_available->pushCovalidators($username_valid);
			 * $username_available->setSpecialFailureStatus(ERROR_INVALID_USERNAME);
			 * return $username_available;
			 */
			/*
			 * case EmailAddressDatum::getColumnNameStatic(): //email address
			 * return new EmailAddressValidator();
			 */
			/*
			 * case PasswordDatum::getColumnNameStatic(): //password
			 * //a. is valid password
			 * $password_valid = new DatumValidator(new PasswordDatum());
			 * //b. password matches confirmation
			 * $password_confirm = new MatchingIndicesValidator(
			 * "password",
			 * "confirm"
			 * );
			 * $password_confirm->pushCovalidators($password_valid);
			 * return $password_confirm;
			 */
			default:
				Debug::error("{$f} invalid index \"{$index}\"");
		}
	}
}
