<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification;

use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;

interface NoteworthyInterface{

	/**
	 *
	 * @return UserData
	 */
	function getUpdateNotificationRecipient();

	static function getNotificationClass(): string;

	function getNotificationPreview();

	function isNotificationDataWarranted(PlayableUser $user): bool;
}
