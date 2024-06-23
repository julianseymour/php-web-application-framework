<?php

namespace JulianSeymour\PHPWebApplicationFramework\email;

use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\notification\NoteworthyTrait;

trait EmailNoteworthyTrait{

	use NoteworthyTrait;

	public abstract static function getEmailNotificationClass():?string;

	public abstract function getConfirmationUri();

	public abstract function isNotificationDataWarranted(PlayableUser $user): bool;
}
