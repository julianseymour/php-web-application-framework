<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

class MessageEncryptionScheme extends IntermediateSymmetricEncryptionScheme{

	public static function getIntermediateEncryptionSchemeClass(){
		return AsymmetricEncryptionScheme::class;
	}
}
