<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\condemn;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\security\StoredIpAddress;

class CondemnedIpAddress extends StoredIpAddress{

	public static function getDatabaseNameStatic():string{
		return "security";
	}
	
	public static function getTableNameStatic(): string{
		return "condemned_ip_addresses";
	}

	public static function getSubtypeStatic(): string{
		return IP_ADDRESS_TYPE_CONDEMNED;
	}

	public static function throttleOnInsert(): bool{
		return false;
	}

	public static function getPhylumName(): string{
		return "condemnedIpAddresses";
	}

	public static function getKeyGenerationMode(): int{
		return KEY_GENERATION_MODE_HASH;
	}

	public static function getCompositeUniqueColumnNames(): ?array{
		return [
			[
				'ipAddress'
			]
		];
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::reconfigureColumns($columns, $ds);
		// $columns['ipAddress']->setUniqueFlag(true);
		$fields = [
			"insertIpAddress"
		];
		foreach ($fields as $i) {
			$columns[$i]->volatilize();
		}
		$columns['userKey']->setNullable(true);
		$columns['userAccountType']->setNullable(true);
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		parent::declareColumns($columns, $ds);
		$action = new TextDatum("action");
		$action->setNullable(false);
		array_push($columns, $action);
	}

	public function setUri($action){
		return $this->setColumnValue("action", $action);
	}

	public function hasURI(){
		return $this->hasColumnValue("action");
	}

	public function getUri(){
		$f = __METHOD__;
		if (! $this->hasURI()) {
			Debug::error("{$f} action is undefined");
		}
		return $this->getColumnValue("action");
	}

	public static function getPermissionStatic(string $name, $data){
		switch ($name) {
			case DIRECTIVE_INSERT:
				return SUCCESS;
			default:
		}
		return parent::getPermissionStatic($name, $data);
	}
}
