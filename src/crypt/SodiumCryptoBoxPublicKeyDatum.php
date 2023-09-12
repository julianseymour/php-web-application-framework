<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Base64Datum;
use Exception;

class SodiumCryptoBoxPublicKeyDatum extends Base64Datum
{

	public static function validateStatic($value): int
	{
		$f = __METHOD__; //SodiumCryptoBoxPublicKeyDatum::getShortClass()."(".static::getShortClass().")::validateStatic()";
		try{
			$len = strlen($value);
			if($len !== SODIUM_CRYPTO_BOX_PUBLICKEYBYTES) {
				Debug::warning("{$f} key is incorrect length");
				return ERROR_SODIUM_PUBLICKEYSIZE;
			}
			return parent::validateStatic($value);
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}