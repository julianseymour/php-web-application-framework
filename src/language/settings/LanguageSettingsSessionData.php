<?php
namespace JulianSeymour\PHPWebApplicationFramework\language\settings;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;

class LanguageSettingsSessionData extends DataStructure
{

	public static function getPrettyClassName(?string $lang = null)
	{
		return _("Language selection");
	}

	public static function getPrettyClassNames(?string $lang = null)
	{
		return static::getPrettyClassName($lang);
	}

	public function getLanguageDirection()
	{
		return "ltr";
	}

	public static function updateLanguageSettingsStatic($that)
	{
		$f = __METHOD__;
		try {
			$cmd = directive();
			if ($cmd === DIRECTIVE_LANGUAGE) {
				$post = getInputParameters();
				$lang = $post["directive"][DIRECTIVE_LANGUAGE];
			} else {
				Debug::printPost("{$f} undefined language; server command is \"{$cmd}\"");
			}
			$user = user();
			$user->setLanguagePreference($lang);
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if ($mysqli == null) {
				return $that->setObjectStatus(ERROR_MYSQL_CONNECT);
			}
			$user->setLanguagePreference($lang);
			$status = $user->update($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} updating language preference returned error status \"{$err}\"");
				return $that->setObjectStatus($status);
			}
			// Debug::print("{$f} successfully updated language preference");
			$session = new LanguageSettingsSessionData();
			$session->setLanguageCode($lang);
			return SUCCESS; // static::getUpdatedSettingsSuccessStatus();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getLanguageCode()
	{
		$f = __METHOD__; //LanguageSettingsSessionData::getShortClass()."(".static::getShortClass().")->getLanguageCode()";
		if (! $this->hasColumnValue("languageCode")) {
			return LANGUAGE_DEFAULT;
		}
		$code = $this->getColumnValue("languageCode");
		if ($code === null) {
			Debug::warning("{$f} language code is null");
			return LANGUAGE_DEFAULT;
		} elseif (! is_string($code)) {
			$gottype = gettype($code);
			Debug::error("{$f} code \"{$code}\" is a {$gottype}, not a string");
		}
		return $this->getColumnValue("languageCode");
	}

	public function setLanguageCode($code)
	{
		$f = __METHOD__; //LanguageSettingsSessionData::getShortClass()."(".static::getShortClass().")->setLanguageCode({$code})";
		try {
			if (! isset($code)) {
				Debug::error("{$f} language code is undefined");
			} elseif (is_int($code)) {
				Debug::error("{$f} language code is an integer");
			}
			// Debug::print("{$f} setting language code to \"{$code}\"");
			$ret = $this->setColumnValue("languageCode", $code);
			switch ($code) {
				case LANGUAGE_DEFAULT:
				case LANGUAGE_DERP:
					return $ret;
				default:
					$supported = config()->getSupportedLanguages();
					if (false === array_search($code, $supported)) {
						Debug::error("{$f} language \"{$code}\" is unsupported");
					} elseif (! defined("DOMAIN_BASE")) {
						Debug::error("{$f} undefined constant \"DOMAIN_BASE\" numbskull");
					}
					$path = "/var/" . DOMAIN_BASE . "/languages/{$code}.xml";
					// Debug::print("{$f} about to load XML file \"{$path}\"");
					if (file_exists($path)) {
						$xml = simplexml_load_file($path);
						foreach ($xml->string as $string) {
							$_SESSION['languages'][$code][(string) $string['id']] = (string) $string;
						}
					} else {
						Debug::warning("{$f} failed to open file \"{$path}\"");
						return $this->setColumnValue("languageCode", LANGUAGE_DEFAULT);
					}
					break;
			}
			// Debug::print("{$f} returning normally");
			return $ret;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasLanguageCode()
	{
		return $this->hasColumnValue("languageCode");
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		$f = __METHOD__; //LanguageSettingsSessionData::getShortClass()."(".static::getShortClass().")::declareColumns()";
		$language = new StringEnumeratedDatum("languageCode");
		$language->setNullable(true);
		$language->setValidEnumerationMap(config()->getSupportedLanguages());
		// $language->setDefaultValue(LANGUAGE_DEFAULT);
		static::pushTemporaryColumnsStatic($columns, $language);
	}

	public static function getTableNameStatic(): string
	{
		$f = __METHOD__; //LanguageSettingsSessionData::getShortClass()."(".static::getShortClass().")::getTableNameStatic()";
		ErrorMessage::unimplemented($f);
	}

	public static function getDataType(): string
	{
		return DATATYPE_UNKNOWN;
	}

	public static function getPhylumName(): string
	{
		return "languageSettings";
	}

	public static function getDefaultPersistenceModeStatic(): int
	{
		return PERSISTENCE_MODE_SESSION;
	}

	public function isRegistrable(): bool
	{
		return false;
	}

	public static function isRegistrableStatic(): bool
	{
		return false;
	}
}
