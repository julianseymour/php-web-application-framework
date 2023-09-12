<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\ui;

use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\notification\RetrospectiveNotificationData;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class NotificationsWidgetLoadoutGenerator extends LoadoutGenerator
{

	public function getRootNodeTreeSelectStatements(?PlayableUser $user = null, ?UseCase $use_case = null): ?array
	{
		$f = __METHOD__; //NotificationsWidget::getShortClass()."(".static::getShortClass().")::getNotificationListDependencies()";
		try{
			$print = false;
			if(! user()->isEnabled()) {
				return [];
			}elseif(request()->getFlag("nonAjaxJsEnabled")) {
				if($print) {
					Debug::print("{$f} this is a non-ajax request where javascript is enabled");
				}
				return [];
			}elseif($print) {
				Debug::print("{$f} user is enabled, and this is either an AJAX request or JS is disabled");
			}
			$class = RetrospectiveNotificationData::class;
			return [
				'notifications' => [
					$class => $class::selectStatic()->where(new AndCommand($class::whereIntersectionalHostKey(user()->getClass(), "userKey"), new WhereCondition("notificationState", OPERATOR_EQUALS)))
						->orderBy(new OrderByClause("pinnedTimestamp", DIRECTION_DESCENDING), new OrderByClause("insertTimestamp", DIRECTION_DESCENDING))
						->withTypeSpecifier('sss')
						->withParameters(user()->getIdentifierValue(), "userKey", NOTIFICATION_STATE_UNREAD)
				]
			];
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}

