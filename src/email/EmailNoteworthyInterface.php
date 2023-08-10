<?php
namespace JulianSeymour\PHPWebApplicationFramework\email;

use JulianSeymour\PHPWebApplicationFramework\notification\NoteworthyInterface;

interface EmailNoteworthyInterface extends NoteworthyInterface
{

	static function getEmailNotificationClass();

	function getConfirmationUri();

	function isEmailNotificationWarranted($recipient): bool;
}
