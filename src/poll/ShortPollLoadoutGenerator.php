<?php
namespace JulianSeymour\PHPWebApplicationFramework\poll;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\notification\recent\RecentNotificationsUseCase;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class ShortPollLoadoutGenerator extends LoadoutGenerator{

	public function getRootNodeTreeSelectStatements(?PlayableUser $user = null, ?UseCase $use_case = null): ?array
	{
		$f = __METHOD__;
		$print = false;
		$ret = [];
		foreach ($use_case->getUseCases() as $uc) {
			if ($print) {
				$ucc = get_short_class($uc);
			}
			if ($uc->getDisabledFlag()) {
				if ($print) {
					Debug::print("{$f} {$ucc} is disabled");
				}
				continue;
			}
			$generator = $uc->getLoadoutGenerator(user());
			if (! $generator instanceof LoadoutGenerator) {
				if ($print) {
					Debug::print("{$f} use case \"{$ucc}\" does not have a loadout generator");
				}
				continue;
			}
			$statements = $generator->getRootNodeTreeSelectStatements($user, $uc);
			if ($statements) {
				$ret = array_merge_recursive($ret, $statements);
			}
		}
		if ($print) {
			Debug::print("{$f} got the following root node tree select statements:");
			Debug::printArray($ret);
		}
		return $ret;
	}

	public function getNonRootNodeTreeSelectStatements(DataStructure $user = null, ?UseCase $use_case = null): ?array{
		$f = __METHOD__;
		try{
			$print = false;
			$ret = [];
			foreach ($use_case->getUseCases() as $uc) {
				if ($print) {
					$ucc = get_short_class($uc);
				}
				if ($uc->getDisabledFlag()) {
					if ($print) {
						Debug::print("{$f} {$ucc} is disabled");
					}
					continue;
				}
				$generator = $uc->getLoadoutGenerator(user());
				if (! $generator instanceof LoadoutGenerator) {
					if ($print) {
						Debug::print("{$f} use case \"{$ucc}\" does not have a loadout generator");
					}
					continue;
				}elseif($print){
					Debug::print("{$f} ".get_short_class($uc)." produced a ".get_short_class($generator));
				}
				$statements = $generator->getNonRootNodeTreeSelectStatements($user, $uc);
				if ($statements) {
					$ret = array_merge_recursive($ret, $statements);
				}
			}
			return $ret;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
