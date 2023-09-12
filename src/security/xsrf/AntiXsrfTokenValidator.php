<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\xsrf;

use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\str_contains;
use JulianSeymour\PHPWebApplicationFramework\common\UriTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;

/**
 * Automates anti-XSRF token validation for AjaxForms
 * Note this validator will strip get parameters from URIs
 *
 * @author j
 *        
 */
class AntiXsrfTokenValidator extends Validator
{

	use UriTrait;

	public function __construct($uri = null)
	{
		if(empty($uri)) {
			$uri = request()->getRequestURI();
		}
		$this->setUri($uri);
		parent::__construct();
	}

	public function setUri($uri)
	{
		$f = __METHOD__; //AntiXsrfTokenValidator::getShortClass()."(".static::getShortClass().")->setUri()";
		$print = false;
		if($uri == null) {
			unset($this->uri);
			return null;
		}elseif(str_contains($uri, "?")) {
			$uri = explode("?", $uri)[0];
			if($print) {
				Debug::print("{$f} split string at ? to make new URI \"{$uri}\"");
			}
		}
		return $this->uri = $uri;
	}

	/*
	 * public static function logXSRFIncident($where){
	 * $f = __METHOD__; //static::class."::logXSRFIncident({$where})";
	 * $host = "localhost";
	 * $user = "writer_xsrf";
	 * $pass = "tempXSRFWriterPassword"; //XXX
	 * $mysqli = new mysqli($host, $user, $pass);
	 * if($mysqli->connect_error)
	 * {
	 * Debug::error("{$f}: error connecting to XSRF incident log database: \"{$mysqli->error}\"");
	 * return null;
	 * }
	 * $mysqli->select_db("xsrf");
	 * $userData = static::fingerprint($_SERVER);
	 * //timestamp
	 * //error code
	 * //username if logged in
	 * //user agent string
	 * //OS
	 * //browser
	 * }
	 */
	public function evaluate(&$validate_me): int
	{
		$f = __METHOD__; //AntiXsrfTokenValidator::getShortClass()."(".static::getShortClass().")->evaluate()";
		$print = false;
		if(! array_key_exists('xsrf_token', $validate_me)) {
			if($print) {
				Debug::print("{$f} anti-XSRF token was not posted");
				Debug::print($validate_me);
			}
			return ERROR_XSRF;
		}elseif($print) {
			Debug::print("{$f} user submitted a form - about to check XSRF token");
		}
		$uri = $this->getUri();
		if(! AntiXsrfTokenData::verifySessionToken($uri)) {
			Debug::warning("{$f} session token failed verification");
			return $this->setObjectStatus(ERROR_XSRF);
		}elseif($print) {
			Debug::print("{$f} anti-XSRF token validated successfully");
		}
		return SUCCESS;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->uri);
	}
}
