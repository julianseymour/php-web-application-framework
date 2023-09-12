<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\condemn;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\drop_request;
use function JulianSeymour\PHPWebApplicationFramework\ip_version;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class BlockCondemnedIpAddressesUseCase extends UseCase
{

	public function execute(): int
	{
		$f = __METHOD__; //BlockCondemnedIpAddressesUseCase::getShortClass()."(".static::getShortClass().")->execute()";
		$print = false;
		if(! isset($_SERVER['REMOTE_ADDR'])) {
			Debug::warning("{$f} remote IP address is undefined");
		}else{
			$ip = $_SERVER['REMOTE_ADDR'];
			$ipv = ip_version($ip);
			$select = CondemnedIpAddress::selectStatic()->where(new AndCommand(new WhereCondition("ipAddress", OPERATOR_EQUALS), new WhereCondition("ipVersion", OPERATOR_EQUALS)))
				->withTypeSpecifier('si')
				->withParameters($ip, $ipv);
			if(array_key_exists('__applicationInstance', $GLOBALS)) {
				$dbm = db();
			}
			$mysqli = $dbm->getConnection(PublicReadCredentials::class);
			$count = $select->executeGetResultCount($mysqli);
			if($count == 0) {
				if($print) {
					Debug::print("{$f} this IP address was not blacklisted globally");
				}
				return $this->setObjectStatus(SUCCESS);
			}elseif($print) {
				Debug::print("{$f} IP address {$ip} was globally blacklisted");
			}
			db()->disconnect();
			unset($mysqli);
			unset($ip);
			unset($ipv);
			unset($select);
			unset($count);
		}
		unset($f);
		unset($print);
		unset($_SERVER);
		drop_request();
	}

	public function getActionAttribute(): ?string
	{
		return null;
	}

	protected function getExecutePermissionClass()
	{
		return SUCCESS;
	}
}
