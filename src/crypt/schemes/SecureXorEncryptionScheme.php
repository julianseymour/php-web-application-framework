<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

class SecureXorEncryptionScheme extends IntermediateSymmetricEncryptionScheme
{

	public static function getIntermediateEncryptionSchemeClass()
	{
		return XorEncryptionScheme::class;
	}
}
