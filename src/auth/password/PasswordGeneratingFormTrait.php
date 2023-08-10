<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\password;

trait PasswordGeneratingFormTrait
{

	public abstract static function getPasswordInputName();

	public abstract static function getConfirmPasswordInputName();

	/**
	 * creates validators for the password generating input
	 *
	 * @return object[]|ServerConfirmPasswordValidator[]
	 */
	protected function getConfirmPasswordValidator()
	{
		/*
		 * $password_name = $this->getPasswordInputName();
		 * $value = null;
		 * if(hasInputParameter($password_name)){
		 * $value = getInputParameter($password_name);
		 * }
		 * $valid_new = new DatumValidator(new PasswordDatum($password_name), $value);
		 * $valid_new->setSpecialFailureStatus(ERROR_PASSWORD_WEAK);
		 */
		$matching = new ServerConfirmPasswordValidator($this->getConfirmPasswordInputName());
		$matching->setSpecialFailureStatus(ERROR_PASSWORD_MISMATCH);
		return $matching; // [$valid_new, $matching];
	}
}


