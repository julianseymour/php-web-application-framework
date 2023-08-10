<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use JulianSeymour\PHPWebApplicationFramework\form\UniqueFormInterface;

interface ConfirmationCodeGeneratingFormInterface extends UniqueFormInterface
{

	function getConfirmationCodeClass();
}
