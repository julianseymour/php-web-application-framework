<?php

namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\getCurrentUserLanguagePreference;
use function JulianSeymour\PHPWebApplicationFramework\starts_with;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\language\settings\LanguageSettingsData;
use Exception;

class Internationalization extends Basic{

	public static function getLanguageNameFromCode(string $code):string{
		$f = __METHOD__;
		switch ($code) {
			case LANGUAGE_ENGLISH:
				return "English";
			case LANGUAGE_SPANISH:
				return "Español";
			case LANGUAGE_DERP:
				return "Derp";
			default:
				Debug::error("{$f} invalid language code \"{$code}\"");
		}
	}

	/**
	 * return true if surnames come before given names in the language passed as a parameter
	 *
	 * @param string $lang
	 * @return boolean
	 */
	public static function lastNameFirst(string $lang):bool{
		return false;
	}

	public static function doesLanguageUseLatinAlphabet(string $code):bool{
		$f = __METHOD__;
		switch ($code) {
			case LANGUAGE_ARABIC:
			case LANGUAGE_CHINESE:
			case LANGUAGE_FARSI:
			case LANGUAGE_JAPANESE:
			case LANGUAGE_KOREAN:
			case LANGUAGE_RUSSIAN:
				return false;
			case LANGUAGE_ENGLISH:
			case LANGUAGE_FRENCH:
			case LANGUAGE_GERMAN:
			case LANGUAGE_ITALIAN:
			case LANGUAGE_LITHUANIAN:
			case LANGUAGE_POLISH:
			case LANGUAGE_PORTUGUESE:
			case LANGUAGE_SPANISH:
			case LANGUAGE_DERP:
				return true;
			default:
				Debug::error("{$f} undefined language \"{$code}\"");
				return null;
		}
	}
	
	public static function getFallbackLocale(string $locale):string{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} locale \"{$locale}\" is unsupported, so we are going to return one with the same language");
		}
		$splat = explode("_", $locale);
		$similar = glob("/var/www/locale/$splat[0]_??", GLOB_ONLYDIR);
		if(is_array($similar) && !empty($similar)){
			$splat = explode('/', $similar[0]);
			$locale = $splat[count($splat)-1];
		}else{
			$locale = LOCALE_DEFAULT;
		}
		if($print){
			Debug::print("{$f} returning \"{$locale}\"");
		}
		return $locale;
	}
}

