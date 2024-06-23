<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\nonexistent_uris;

use function JulianSeymour\PHPWebApplicationFramework\ip_version;
use JulianSeymour\PHPWebApplicationFramework\admin\AdminOnlyAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\security\condemn\CondemnedIpAddress;
use mysqli;
use JulianSeymour\PHPWebApplicationFramework\datum\IpAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;

class NonexistentUriData extends DataStructure implements StaticTableNameInterface{
	
	use StaticTableNameTrait;
	
	public static function getDatabaseNameStatic():string{
		return "security";
	}
	
	public static function declareColumns(array& $columns, ?DataStructure $ds=null):void{
		parent::declareColumns($columns, $ds);
		$ipAddress = new IpAddressDatum("ipAddress");
		$ipVersion = new UnsignedIntegerDatum("ipVersion", 8);
		$uri = new TextDatum("uriSegment");
		$full = new TextDatum("requestUri");
		$list = new StringEnumeratedDatum("list");
		$list->setDefaultValue(POLICY_DEFAULT);
		$list->setValidEnumerationMap([
			POLICY_DEFAULT,
			POLICY_ALLOW,
			POLICY_BLOCK
		]);
		array_push($columns, $ipAddress, $ipVersion, $uri, $full, $list);
	}
	
	public static function getDataType():string{
		return DATATYPE_NONEXISTENT_URI;
	}
	
	public static function getTableNameStatic():string{
		return "nonexistent_uris";
	}
	
	public static function getPermissionStatic(string $name, $data){
		switch($name){
			case DIRECTIVE_INSERT:
				return SUCCESS;
			default:
				return new AdminOnlyAccountTypePermission($name);
		}
	}
	
	public function afterGenerateInitialValuesHook():int{
		$ret = parent::afterGenerateInitialValuesHook();
		$this->setIpVersion(ip_version($this->getIpAddress()));
		return $ret;
	}
	
	public function afterUpdateHook(mysqli $mysqli):int{
		$f = __METHOD__; //NonexistentUriData::getShortClass()."(".static::getShortClass().")->afterUpdateHook()";
		$print = false;
		if($this->getColumnValue("list") === POLICY_BLOCK){
			$ip = $this->getIpAddress();
			$select = CondemnedIpAddress::selectStatic("ipAddress")->where(
				new WhereCondition("ipAddress", OPERATOR_EQUALS)
			)->withTypeSpecifier('s')->withParameters($ip);
			$count = $select->executeGetResultCount($mysqli);
			if($count !== 0){
				Debug::print("{$f} IP address \"{$ip}\" was already condemned");
				return parent::afterUpdateHook($mysqli);
			}
			$condemned = new CondemnedIpAddress();
			$condemned->setIpAddress($ip);
			$status = $condemned->insert($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} inserting condemned IP address returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully condemned IP address");
			}
		}elseif($print){
			$uri = $this->getColumnValue("uriSegment");
			Debug::print("{$f} uri {$uri} was not blocked");
		}
		return parent::afterUpdateHook($mysqli);
	}
	
	public function getList(){
		return $this->getColumnValue("list");
	}
	
	public function hasIpAddress(): bool{
		return $this->hasColumnValue("ipAddress");
	}
	
	public function getIpAddress():string{
		$f = __METHOD__; //NonexistentUriData::getShortClass()."(".static::getShortClass().")->getIpAddress()";
		if(!$this->hasIpAddress()){
			Debug::error("{$f} IP Address is undefined");
		}
		return $this->getColumnValue('ipAddress');
	}
	
	public function setIpAddress(string $ip): string{
		return $this->setColumnValue('ipAddress', $ip);
	}
	
	public function setIpVersion($v){
		$f = __METHOD__; //NonexistentUriData::getShortClass()."(".static::getShortClass().")->setIpVersion()";
		if($v !== 4 && $v !== 6){
			Debug::error("{$f} unsupported IP version \"{$v}\"");
		}
		return $this->setColumnValue("ipVersion", $v);
	}
	
	public function getIpVersion(){
		return $this->getColumnValue('ipVersion');
	}
}

