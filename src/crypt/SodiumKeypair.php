<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;

abstract class SodiumKeypair extends UserOwned
{

	use KeypairedTrait;

	public static function getDataType(): string
	{
		return DATATYPE_1ST_PARTY_SERVER_KEYPAIR;
	}

	public static function getPhylumName(): string
	{
		return "keypairs";
	}

	public static function userIsParent()
	{
		return false;
	}
}
