<?php
namespace JulianSeymour\PHPWebApplicationFramework\db\credentials;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use mysqli;

abstract class PublicDatabaseCredentials extends DatabaseCredentials
{

	public static function getDataType(): string
	{
		return DATATYPE_UNKNOWN;
	}

	/*
	 * public function loadCredentials($mysqli){
	 * $f = __METHOD__; //PublicDatabaseCredentials::getShortClass()."(".static::getShortClass().")->loadCredentials()";
	 * try{
	 * $name = $this->getName();
	 * //Debug::print("{$f} skipping load of plaintext credentials for mysql user \"{$name}\"");
	 * return SUCCESS;
	 * }catch(Exception $x){
	 * x($f, $x);
	 * }
	 * }
	 */
	public function beforeLoadHook(mysqli $mysqli): int
	{
		return ERROR_DEPRECATED;
	}

	public function initializeName()
	{
		return $this->getName();
	}

	public static function getTableNameStatic(): string
	{
		$f = __METHOD__; //PublicDatabaseCredentials::getShortClass()."(".static::getShortClass().")::getTableNameStatic({$suffix})";
		return ErrorMessage::unimplemented($f);
	}
}
