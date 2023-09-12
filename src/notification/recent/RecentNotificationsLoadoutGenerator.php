<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\recent;

use function JulianSeymour\PHPWebApplicationFramework\getCurrentUserKey;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\notification\NotificationData;
use JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class RecentNotificationsLoadoutGenerator extends LoadoutGenerator
{

	public function getRootNodeTreeSelectStatements(?PlayableUser $user = null, ?UseCase $use_case = null): ?array
	{
		$f = __METHOD__; //RecentNotificationsUseCase::getShortClass()."(".static::getShortClass().")->getRootNodeTreeSelectStatements()";
		$print = false;
		$statements = [];
		if(! hasInputParameter('pushSubscriptionKey')) {
			$statements[NotificationData::getPhylumName()] = [
				RetrospectiveNotificationData::class => RetrospectiveNotificationData::getRecentNotificationSelectStatement()->withParameters([
					getCurrentUserKey(),
					"userKey",
					$user->getNotificationDeliveryTimestamp()
				])
			];
		}elseif($print) {
			Debug::print("{$f} pushSubscriptionKey is a defined parameter, skipping recent notification check");
		}
		if(defined("NOTIFICATION_TYPE_MESSAGE")) {
			$statements['online'] = [
				RetrospectiveNotificationData::class => RetrospectiveNotificationData::selectStatic()->where(new AndCommand(RetrospectiveNotificationData::whereIntersectionalHostKey(user()->getClass(), "userKey"), new WhereCondition("subtype", OPERATOR_EQUALS)))
					->withParameters([
					getCurrentUserKey(),
					"userKey",
					NOTIFICATION_TYPE_MESSAGE
				])
			];
		}
		return $statements;
	}
}

