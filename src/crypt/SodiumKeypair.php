<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use JulianSeymour\PHPWebApplicationFramework\data\StandardDataStructure;

abstract class SodiumKeypair extends StandardDataStructure{

	use KeypairedTrait;

	public static function getDataType(): string{
		return DATATYPE_1ST_PARTY_SERVER_KEYPAIR;
	}

	public static function getPhylumName(): string{
		return "keypairs";
	}
}
