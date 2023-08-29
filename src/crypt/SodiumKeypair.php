<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

abstract class SodiumKeypair extends DataStructure{

	use KeypairedTrait;

	public static function getDataType(): string{
		return DATATYPE_1ST_PARTY_SERVER_KEYPAIR;
	}

	public static function getPhylumName(): string{
		return "keypairs";
	}
}
