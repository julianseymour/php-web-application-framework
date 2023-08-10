<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\password;

use JulianSeymour\PHPWebApplicationFramework\form\UniqueFormInterface;

interface PasswordGeneratingFormInterface extends UniqueFormInterface
{

	static function getPasswordInputName();

	static function getConfirmPasswordInputName();
}
