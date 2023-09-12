<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\x;
use Exception;

class IpAddressDatum extends TextDatum{

	public static function validateStatic($ip): int{
		$f = __METHOD__;
		try{
			if(preg_match(REGEX_IPv4_ADDRESS, $ip)) {
				// Debug::print("{$f} IPv4 address");
				return SUCCESS;
			}elseif(preg_match(REGEX_IPv6_ADDRESS, $ip)) {
				// Debug::print("{$f} IPv6 address");
				return SUCCESS;
			}
			return FAILURE;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
