<?php

namespace JulianSeymour\PHPWebApplicationFramework\email;

use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\notification\NoteworthyInterface;

interface EmailNoteworthyInterface extends NoteworthyInterface{

	static function getEmailNotificationClass():?string;

	function getConfirmationUri();

	function isEmailNotificationWarranted(UserData $recipient):bool;
}
