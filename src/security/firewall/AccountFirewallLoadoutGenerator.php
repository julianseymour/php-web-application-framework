<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class AccountFirewallLoadoutGenerator extends LoadoutGenerator
{

	public function getRootNodeTreeSelectStatements(?PlayableUser $user = null, ?UseCase $use_case = null): ?array
	{
		return [
			ListedIpAddress::getPhylumName() => [
				ListedIpAddress::class => ListedIpAddress::selectStatic()->where(ListedIpAddress::whereIntersectionalHostKey(user()->getClass(), "userKey"))
					->orderBy(new OrderByClause("ipAddress", DIRECTION_ASCENDING), new OrderByClause("mask", DIRECTION_ASCENDING))
					->withParameters([
					$user->getIdentifierValue(),
					"userKey"
				])
			]
		];
	}
}

