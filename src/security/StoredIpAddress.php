<?php

namespace JulianSeymour\PHPWebApplicationFramework\security;

use function JulianSeymour\PHPWebApplicationFramework\ip_version;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\security\access\UserFingerprint;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\CidrIpAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;

abstract class StoredIpAddress extends UserFingerprint implements StaticSubtypeInterface, StaticTableNameInterface{
	
	use StaticSubtypeTrait;
	use StaticTableNameTrait;
	
	public static function getIpAddressTypeStatic(): string{
		return static::getSubtypeStatic();
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

	public function setIpVersion(int $v):int{
		$f = __METHOD__;
		if($v !== 4 && $v !== 6) {
			Debug::error("{$f} unsupported IP version \"{$v}\"");
		}
		return $this->setColumnValue("ipVersion", $v);
	}

	public function getIpVersion():int{
		return $this->getColumnValue('ipVersion');
	}

	public function getSubtype():string{
		if($this->hasColumnValue('subtype')) {
			return $this->getColumnValue('subtype');
		}
		return $this->setSubtype(static::getSubypeStatic());
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		parent::declareColumns($columns, $ds);
		$ip = new CidrIpAddressDatum("ipAddress");
		$ip->setUserWritableFlag(true);
		$ipv = new UnsignedIntegerDatum("ipVersion", 8);
		array_push($columns, $ip, $ipv);
	}

	public function getName():string{
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
		foreach($fields as $field) {
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
