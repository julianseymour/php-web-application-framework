<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\xsrf;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;

class AntiXsrfTokenData extends DataStructure{
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		$xsrf_token = new TextDatum("xsrf_token");
		$xsrf_token->setNullable(true);
		$secondary = new TextDatum("secondary_token");
		$secondary->setNullable(true);
		array_push($columns, $xsrf_token, $secondary);
	}

	public function getSecondaryToken(){
		$f = __METHOD__;
		if (! $this->hasSecondaryToken()) {
			Debug::error("{$f} secondary token is undefined");
		}
		return $this->getColumnValue("secondary_token");
	}

	public function hasSecondaryToken(){
		return $this->hasColumnValue("secondary_token") && $this->getColumnValue("secondary_token") !== "";
	}

	public function setSecondaryToken($value){
		return $this->setColumnValue("secondary_token", $value);
	}

	public function ejectSecondaryToken(){
		return $this->ejectColumnValue("secondary_token");
	}

	public function hasAntiXsrfToken(){
		return $this->hasColumnValue("xsrf_token") && $this->getColumnValue("xsrf_token") !== "";
	}

	public function getAntiXsrfToken(){
		return $this->getColumnValue("xsrf_token");
	}

	public function setAntiXsrfToken($value){
		return $this->setColumnValue("xsrf_token", $value);
	}

	public function getSecondaryHmac($action){
		$f = __METHOD__;
		$print = false;
		$hmac = hash_hmac('sha256', $action, $this->getSecondaryToken());
		if ($print) {
			Debug::print("{$f} returning \"{$hmac}\" for URI \"{$action}\"");
		}
		return $hmac;
	}

	public static function verifySessionToken($uri){
		$f = __METHOD__;
		try {
			$print = false;
			$session = new AntiXsrfTokenData();
			if (! isset($_SESSION) || ! $session->hasAntiXsrfToken()) {
				Debug::error("{$f}: primary session token is unset");
				return false;
			} elseif (! hasInputParameter('xsrf_token')) {
				Debug::error("{$f}: anti-XSRF token is NOT set in post");
				return false;
			}
			$xsrf_token = $session->getAntiXsrfToken();
			$posted_token = getInputParameter('xsrf_token');
			if (empty($posted_token)) {
				Debug::error("{$f} posted token is empty");
				return false;
			} elseif ($print) {
				Debug::print("{$f} posted token is \"{$posted_token}\"");
			}
			if (! hash_equals($xsrf_token, $posted_token)) {
				Debug::warning("{$f} primary session token \"{$xsrf_token}\" does not match posted value \"{$posted_token}\"");
				// static::logIncident($f);
				return false;
			} elseif ($print) {
				Debug::print("{$f} session and post tokens match");
			}
			$secondary = $session->getSecondaryToken();
			if (empty($secondary)) {
				Debug::error("{$f} secondary token is undefined");
				return false;
			}
			$known = hash_hmac('sha256', $uri, $secondary);
			if (! hasInputParameter('secondary_hmac')) {
				Debug::error("{$f} secondary hmac is unset");
				return false;
			}
			$hmac = getInputParameter('secondary_hmac');
			if ($print) {
				Debug::print("{$f} secondary hmac is already set as \"{$hmac}\"");
			}
			if (hash_equals($known, $hmac)) {
				if ($print) {
					Debug::print("{$f} success, both URIs match \"{$uri}\" with hmac \"{$known}\" for secondary session token \"{$_SESSION['secondary_token']}\"");
				}
				return true;
			}
			Debug::error("{$f} error: POSTed secondary token hmac \"{$hmac}\" differs from calculated session hmac \"{$known}\" for secondary session token \"{$secondary}\" and URI \"{$uri}\"");
			// static::logIncident($f); //XXX
			return false;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function initializeSessionToken($i){
		$f = __METHOD__;
		try {
			$print = false;
			$session = new AntiXsrfTokenData();
			switch ($i) {
				case (1):
					if (! $session->hasAntiXsrfToken()) {
						if (hasInputParameter('xsrf_token')) {
							Debug::warning("{$f} error: session xsrf token not set");
						}
						$xsrf_token = bin2hex(random_bytes(32));
						$session->setAntiXsrfToken($xsrf_token);
						if ($print) {
							Debug::print("{$f} initialized session token to {$xsrf_token}");
						}
					}
					return;
				case (2):
					if (! $session->hasSecondaryToken()) {
						$secondary = bin2hex(random_bytes(32));
						$session->setSecondaryToken($secondary);
						if ($print) {
							Debug::print("{$f} initialized secondary token to {$secondary}");
						}
					}
					return;
				default:
					Debug::error("{$f}: ({$i}) tokens received");
					return;
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getPrettyClassName():string{
		return _("Anti-XSRF token");
	}

	public static function getPrettyClassNames():string{
		return _("Anti-XSRF token");
	}

	public static function getDataType(): string{
		return DATATYPE_UNKNOWN;
	}

	public static function getPhylumName(): string{
		return "antiXSRFToken";
	}

	public static function getDefaultPersistenceModeStatic(): int{
		return PERSISTENCE_MODE_SESSION;
	}

	public function isRegistrable(): bool{
		return false;
	}

	public static function isRegistrableStatic(): bool{
		return false;
	}
}
