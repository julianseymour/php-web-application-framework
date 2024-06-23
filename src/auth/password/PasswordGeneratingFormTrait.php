<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\password;

trait PasswordGeneratingFormTrait{

	public abstract static function getPasswordInputName();

	public abstract static function getConfirmPasswordInputName();

	/**
	 * creates validators for the password generating input
	 *
	 * @return object[]|ServerConfirmPasswordValidator[]
	 */
	protected function getConfirmPasswordValidator(){
		$matching = new ServerConfirmPasswordValidator($this->getConfirmPasswordInputName());
		$matching->setSpecialFailureStatus(ERROR_PASSWORD_MISMATCH);
		return $matching;
	}
}
