<?php
namespace JulianSeymour\PHPWebApplicationFramework\security;

use function JulianSeymour\PHPWebApplicationFramework\ip_version;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\security\access\UserFingerprint;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\CidrIpAddressDatum;

class StoredIpAddress extends UserFingerprint{

	public static function hasSubtypeStatic():bool{
		return true;
	}
	
	public static function getIPAddressTypeStatic(): string{
		return CONST_UNDEFINED;
	}

	public static function getSubtypeStatic(): string{
		return static::getIPAddressTypeStatic();
	}

	public function hasIpAddress(): bool{
		return $this->hasColumnValue("ipAddress");
	}

	public function getIpAddress(): string{
		return $this->getColumnValue('ipAddress');
	}

	public function setIpAddress(string $ip): string{
		return $this->setColumnValue('ipAddress', $ip);
	}

	public static function getDataType(): string{
		return DATATYPE_IP_ADDRESS;
	}

	public final function hasSubtypeValue(): bool{
		return true;
	}

	public function setIpVersion(int $v):int{
		$f = __METHOD__;
		if ($v !== 4 && $v !== 6) {
			Debug::error("{$f} unsupported IP version \"{$v}\"");
		}
		return $this->setColumnValue("ipVersion", $v);
	}

	public function getIpVersion(){
		return $this->getColumnValue('ipVersion');
	}

	public final function getSubtypeValue(): string{
		return $this->getIPAddressTypeStatic();
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		$ip = new CidrIpAddressDatum("ipAddress");
		$ip->setUserWritableFlag(true);
		$ipv = new UnsignedIntegerDatum("ipVersion", 8);
		parent::declareColumns($columns, $ds);
		static::pushTemporaryColumnsStatic($columns, $ip, $ipv);
	}

	public function getName(){
		return $this->getIPAddress();
	}

	public static function getPrettyClassName():string{
		return _("IP address");
	}

	public static function getPrettyClassNames():string{
		return _("IP addresses");
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::reconfigureColumns($columns, $ds);
		$fields = [
			"userName",
			"userTemporaryRole"
		];
		foreach ($fields as $field) {
			$columns[$field]->volatilize();
		}
	}

	public static function getPhylumName(): string{
		return "ip_addresses";
	}

	public static function getTableNameStatic(): string{
		return "ip_addresses";
	}

	protected function afterGenerateInitialValuesHook(): int{
		$ret = parent::afterGenerateInitialValuesHook();
		$this->setIpVersion(ip_version($this->getIpAddress()));
		return $ret;
	}
}
