<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\captcha;

use function JulianSeymour\PHPWebApplicationFramework\curl_post;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use Exception;

class hCaptcha extends DivElement
{

	public function setDataSiteKeyAttribute($sitekey)
	{
		return $this->setAttribute("data-sitekey", $sitekey);
	}

	public function getDataSiteKeyAttribute()
	{
		return $this->getAttribute("data-sitekey");
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->addClassAttribute("h-captcha");
		if (! defined('HCAPTCHA_SITE_KEY')) {
			Debug::error("{$f} please define HCAPTCHA_SITE_KEY before using this feature");
		}
		$sitekey = HCAPTCHA_SITE_KEY;
		$this->setDataSiteKeyAttribute($sitekey);
		$this->setAllowEmptyInnerHTML(true);
	}

	public final function hasColumnName()
	{
		return false;
	}

	public function getAllocationMode(): int
	{
		return ALLOCATION_MODE_NEVER;
	}

	public static function verifyResponse()
	{
		$f = __METHOD__; //hCaptcha::getShortClass()."(".static::getShortClass().")::verifyResponse()";
		try {
			if (! hasInputParameter("h-captcha-response")) {
				return ERROR_HCAPTCHA;
			} elseif (! defined('HCAPTCHA_SECRET')) {
				Debug::error("{$f} please define HCAPTCHA_SECRET before using this feature");
			}
			// curl -d "response=CLIENT-RESPONSE&secret=YOUR-SECRET" -X POST https://hcaptcha.com/siteverify
			$args = [
				"response" => getInputParameter("h-captcha-response"),
				"secret" => HCAPTCHA_SECRET
			];
			Debug::print("{$f} about to curl hcaptcha with the following arguments:");
			Debug::printArray($args);
			$url = "https://hcaptcha.com/siteverify";
			$result = curl_post($url, $args);
			Debug::print("{$f} curl result is \"{$result}\"");
			// {"success":false,"error-codes":["invalid-or-already-seen-response"]}
			$parsed = json_decode($result, true);
			if ($parsed["success"]) {
				Debug::print("{$f} success!");
				return SUCCESS;
			} else {
				Debug::print("{$f} hcaptcha failed with the following error codes:");
				Debug::printArray($parsed['error-codes']);
				return ERROR_HCAPTCHA;
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getAllowEmptyInnerHTML()
	{
		return true;
	}

	public function dispatchCommands(): int
	{
		$this->reportSubcommand(new hCaptchaRenderCommand($this));
		return SUCCESS;
	}
}