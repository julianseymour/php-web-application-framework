<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

class CorrespondentMessageEncryptionScheme extends MessageEncryptionScheme
{

	public static function getIntermediateEncryptionSchemeClass()
	{
		return CorrespondentAsymmetricEncryptionScheme::class;
	}
}
