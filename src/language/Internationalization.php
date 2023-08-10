<?php
namespace JulianSeymour\PHPWebApplicationFramework\language;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\getCurrentUserLanguagePreference;
use function JulianSeymour\PHPWebApplicationFramework\starts_with;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\language\settings\LanguageSettingsSessionData;
use JulianSeymour\PHPWebApplicationFramework\language\translate\ChainTranslateCommand;
use JulianSeymour\PHPWebApplicationFramework\language\translate\TranslateCommand;
use Exception;

class Internationalization extends Basic{

	public static function getLanguageNameFromCode($code){
		$f = __METHOD__; //Internationalization::getShortClass()."(".static::getShortClass().")::getLanguageNameFromCode({$code})";
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
	public static function lastNameFirst($lang){
		return false;
	}

	public static function chainTranslate($language_id = null, $string_id, ...$chain){
		$f = __METHOD__; //Internationalization::getShortClass()."(".static::getShortClass().")::chainTranslate()";
		try {
			$count = count($chain);
			if ($count === 1) {
				if (is_string($chain[0])) {
					return Internationalization::translate($string_id, $language_id, $chain[0]);
				} elseif (is_object($chain[0])) {
					if ($chain[0] instanceof ValueReturningCommandInterface) {
						return Internationalization::translate($string_id, $language_id, $chain[0]->evaluate());
					}
					return Internationalization::translate($string_id, $language_id, $chain[0]->__toString());
				}
				return Internationalization::translate($string_id, $language_id, Internationalization::translate($chain[0], $language_id));
			}
			if (! array_key_exists(0, $chain)) {
				Debug::error("{$f} undefined index 0");
			}
			return Internationalization::translate($string_id, $language_id, Internationalization::chainTranslate($language_id, $chain[0], ...array_slice($chain, 1)));
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function translate($string_id, $language_id = null, ...$substitutions){
		$f = __METHOD__; //Internationalization::getShortClass()."(".static::getShortClass().")::translate()";
		try {
			$print = false;
			if ($language_id !== null && ! is_string($language_id)) {
				Debug::error("{$f} language ID is not a string or integer");
			} elseif ($language_id === LANGUAGE_UNDEFINED) {
				if ($print) {
					Debug::print("{$f} language ID is undefined");
				}
				if (app()->hasUserData() || app()->hasLanguageOverride()) {
					$language_id = getCurrentUserLanguagePreference();
				} elseif (class_exists(LanguageSettingsSessionData::class)) {
					if ($print) {
						Debug::print("{$f} about to create new language settings session data");
					}
					$language_data = new LanguageSettingsSessionData();
					$language_id = $language_data->getLanguageCode();
					if ($print) {
						Debug::print("{$f} language ID returned by session data is \"{$language_id}\"");
					}
				} else {
					if ($print) {
						Debug::print("{$f} LanguageSettingsSessionData does not exist");
					}
					$language_id = LANGUAGE_DEFAULT;
				}
			}
			if ($language_id === LANGUAGE_DEFAULT) {
				if ($print) {
					Debug::print("{$f} returning default language string");
				}
				$string = StringTable::getDefaultLanguageString($string_id);
			} elseif ($language_id === LANGUAGE_DERP) {
				return "Derp";
			} else {
				if ($print) {
					Debug::print("{$f} language code is \"{$language_id}\"");
				}
				if (! isset($_SESSION) || ! is_array($_SESSION) || ! array_key_exists('languages', $_SESSION)) {
					if ($print) {
						Debug::warning("{$f} language index is not defined in session superglobal");
					}
					return Internationalization::translate($string_id, LANGUAGE_DEFAULT, ...$substitutions);
				} elseif (! array_key_exists($language_id, $_SESSION['languages'])) {
					Debug::error("{$f} language \"{$language_id}\" not indexed in session");
				}
				$strings = $_SESSION['languages'][$language_id];
				if (! is_array($strings)) {
					Debug::error("{$f} session-allocated strings array is not an array");
				}
				if (! array_key_exists($string_id, $strings)) { // [(string)$string['id']] = (string)$string;
					Debug::warning("{$f} string ID \"{$string_id}\" is not indexed");
					return "UNTRANSLATED STRING";
				}
				$string = $strings[$string_id];
			}
			$i = 1;
			foreach ($substitutions as $sub) {
				if ($sub instanceof ValueReturningCommandInterface) {
					if ($print) {
						Debug::print("{$f} substitution {$i} is a value returning media command");
					}
					while ($sub instanceof ValueReturningCommandInterface) {
						$sub = $sub->evaluate();
					}
				}
				if ($i === 1 && starts_with($string, "%{$i}%")) {
					// capitalize? should be taken care of already
				} elseif (is_string($sub)) {
					if (strlen($sub) >= 2 && ctype_upper($sub[1])) {
						if ($print) {
							Debug::print("{$f} substitution \"{$sub}\" is probably an acronym");
						}
					} elseif (ctype_upper($sub[0])) { // make the substitution lower case
						$sub[0] = strtolower($sub[0]);
						if ($print) {
							Debug::print("{$f} substitution has been made lower case to \"{$sub}\"");
						}
					}
				}

				if ($print) {
					Debug::print("{$f} iterator \"{$i}\"");
					Debug::print("{$f} substitution \"{$sub}\"");
					Debug::print("{$f} before substitution: \"{$string}\"");
				}
				$string = str_replace("%{$i}%", $sub, "{$string}");
				if ($print) {
					Debug::print("{$f} after substitution: \"{$string}\"");
				}
				$i ++;
			}
			return $string;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function doesLanguageUseLatinAlphabet($code){
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

	public static function getLanguageFromIpAddress($ip){
		$country = geoip_country_code_by_name($ip);
		switch ($country) {
			case "AR": // ,"Argentina"
			case "BO": // ,"Bolivia"
			case "CL": // ,"Chile"
			case "CO": // ,"Colombia"
			case "CR": // ,"Costa Rica"
			case "CU": // ,"Cuba"
			case "DO": // ,"Dominican Republic"
			case "EC": // ,"Ecuador"
			case "SV": // ,"El Salvador"
			case "GQ": // ,"Equatorial Guinea"
			case "GT": // ,"Guatemala"
			case "HN": // ,"Honduras"
			case "MX": // ,"Mexico"
			case "NI": // ,"Nicaragua"
			case "PA": // ,"Panama"
			case "PY": // ,"Paraguay"
			case "PE": // ,"Peru"
			case "ES": // ,"Spain"
			case "UY": // ,"Uruguay"
			case "VE": // ,"Venezuela"
			case "GI": // ,"Gibraltar"
			case "PR": // ,"Puerto Rico"
				return LANGUAGE_SPANISH;
			case "AU": // ,"Australia"
			case "NZ": // ,"New Zealand"
			case "GB": // ,"United Kingdom"
			case "US": // ,"United States"
				return LANGUAGE_ENGLISH;
			default:
				return LANGUAGE_DEFAULT;
		}
	}
	
	public static function translateCommand($string_id, $language_id = null, ...$substitutions){
		return new TranslateCommand($string_id, $language_id = null, ...$substitutions);
	}
	
	public static function chainTranslateCommand($language_id = null, $string_id, ...$chain){
		return new ChainTranslateCommand($language_id = null, $string_id, ...$chain);
	}
}

