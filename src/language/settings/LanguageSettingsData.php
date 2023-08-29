<?php

namespace JulianSeymour\PHPWebApplicationFramework\language\settings;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\default_lang_ip;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\AdminWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;

class LanguageSettingsData extends DataStructure{

	public static function getDatabaseNameStatic():string{
		return "error";
	}
	
	public static function getPrettyClassName():string{
		return _("Language selection");
	}

	public static function getPrettyClassNames():string{
		return static::getPrettyClassName();
	}

	public function getLanguageDirection(){
		return "ltr"; //XXX temp
	}

	public static function updateLanguageSettingsStatic($that){
		$f = __METHOD__;
		try {
			$print = false;
			$cmd = directive();
			if ($cmd === DIRECTIVE_LANGUAGE) {
				$post = getInputParameters();
				$lang = $post["directive"][DIRECTIVE_LANGUAGE];
			} else {
				Debug::printPost("{$f} undefined language; server command is \"{$cmd}\"");
			}
			$user = user();
			$user->setLanguagePreference($lang);
			if($user instanceof Administrator){
				$mysqli = db()->reconnect(AdminWriteCredentials::class);
			}else{
				$mysqli = db()->getConnection(PublicWriteCredentials::class);
			}
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
			$session = new LanguageSettingsData();
			$session->setLanguageCode($lang);
			$locale = $user->getLocaleString();
			$set = setlocale(LC_MESSAGES, $locale, "{$locale}.utf8", "{$locale}.UTF8", $lang);
			if(false === $set){
				Debug::error("{$f} setting locale failed");
			}elseif($print){
				Debug::print("{$f} successfully set locale to \"{$locale}\"");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getLanguageCode():string{
		$f = __METHOD__;
		if($this->hasColumnValue("sessionLanguageCode")){
			return $this->getColumnValue("sessionLanguageCode");
		}elseif($this->hasColumnValue("cookieLanguageCode")){
			return $this->getColumnValue(("cookieLanguageCode"));
		}
		return default_lang_ip($_SERVER['REMOTE_ADDR']);
	}

	public function setLanguageCode(string $code):string{
		$f = __METHOD__;
		try {
			if (! isset($code)) {
				Debug::error("{$f} language code is undefined");
			} elseif (is_int($code)) {
				Debug::error("{$f} language code is an integer");
			}
			// Debug::print("{$f} setting language code to \"{$code}\"");
			$ret = $this->setColumnValue("sessionLanguageCode", $code);
			$ret = $this->setColumnValue("cookieLanguageCode", $code);
			return $ret;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasLanguageCode():bool{
		return $this->hasColumnValue("sessionLanguageCode") || $this->hasColumnValue("cookieLanguageCode");
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		$language_session = new StringEnumeratedDatum("sessionLanguageCode");
		$language_session->setNullable(true);
		$language_session->setValidEnumerationMap(config()->getSupportedLanguages());
		$language_session->setPersistenceMode(PERSISTENCE_MODE_SESSION);
		$language_cookie = new StringEnumeratedDatum("cookieLanguageCode");
		$language_cookie->setNullable(true);
		$language_cookie->setValidEnumerationMap(config()->getSupportedLanguages());
		$language_cookie->setPersistenceMode(PERSISTENCE_MODE_COOKIE);
		$region_session = new StringEnumeratedDatum("sessionRegionCode");
		$region_session->setNullable(true);
		$region_session->setPersistenceMode(PERSISTENCE_MODE_SESSION);
		$region_cookie = new StringEnumeratedDatum("cookieRegionCode");
		$region_cookie->setNullable(true);
		$region_cookie->setPersistenceMode(PERSISTENCE_MODE_COOKIE);
		static::pushTemporaryColumnsStatic(
			$columns, 
			$language_session,
			$language_cookie,
			$region_session,
			$region_cookie
		);
	}

	public static function getTableNameStatic(): string{
		ErrorMessage::unimplemented(__METHOD__);
	}

	public static function getDataType(): string{
		return DATATYPE_UNKNOWN;
	}

	public static function getPhylumName(): string{
		return "languageSettings";
	}

	public function isRegistrable(): bool{
		return false;
	}

	public static function isRegistrableStatic(): bool{
		return false;
	}
	
	public function setRegionCode(string $code):string{
		$this->setColumnValue("sessionRegionCode", $code);
		$this->setColumnValue("cookieRegionCode", $code);
		return $code;
	}
	
	public function hasRegionCode():bool{
		return $this->hasColumnValue("sessionRegionCode") || $this->hasColumnValue("cookieRegionCode");
	}
	
	public function getRegionCode():string{
		if($this->hasColumnValue("sessionRegionCode")){
			return $this->getColumnValue("sessionRegionCode");
		}elseif($this->hasColumnValue("cookieRegionCode")){
			return $this->getColumnValue("cookieRegionCode");
		}
		return geoip_country_code_by_name($_SERVER['REMOTE_ADDR']);
	}
	
	public function getLocaleString():string{
		return $this->getLanguageCode()."_".$this->getRegionCode();
	}
	
	public static function getDefaultPersistenceModeStatic(): int{
		return PERSISTENCE_MODE_UNDEFINED;
	}
}
