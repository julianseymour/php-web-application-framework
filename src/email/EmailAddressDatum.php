<?php
namespace JulianSeymour\PHPWebApplicationFramework\email;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticHumanReadableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use Exception;

class EmailAddressDatum extends TextDatum implements StaticHumanReadableNameInterface{


	public function __construct(string $name){
		parent::__construct($name);
		$this->setRegularExpression(REGEX_EMAIL_ADDRESS);
		$this->setMaximumLength(254);
	}

	public static function normalize(string $email):string{
		$f = __METHOD__;
		try {
			if (empty($email)) {
				Debug::error("{$f} empty input parameter");
			}
			$print = false;
			// 1. split at @
			$lower = strtolower($email);
			$splat_at = explode('@', $lower);
			$prefix = $splat_at[0];
			$suffix = $splat_at[1];
			// 2. if it's a gmail address, remove all .s from the first part
			// $tld = explode('.', $suffix)[0];
			if (true) { // too many sites use gmail //$tld === "gmail"){
				$search = '.';
				$replace = "";
				$prefix = str_replace($search, $replace, $prefix);
			}
			// 3. condition for subaddressing
			if (substr_count($prefix, '+') > 0) {
				if ($print) {
					Debug::print("{$f} email address has a + sign; this is a sub address");
				}
				$splat_plus = explode($prefix, '+');
				$prefix = $splat_plus[0]; // subaddress is none of our concern
			}
			// 4. concatenate
			$normal = "{$prefix}@{$suffix}";
			if ($print) {
				Debug::print("{$f} returning \"{$normal}\"");
			}
			return $normal;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setValue($v){
		$f = __METHOD__;
		return parent::setValue($v);
	}

	public static function getHumanReadableNameStatic(?StaticHumanReadableNameInterface $that = null){
		return _("Email address");
	}
}
