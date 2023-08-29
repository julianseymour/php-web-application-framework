<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\captcha;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\curl_post;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;
use Exception;

class hCaptcha extends DivElement{
	
	use StyleSheetPathTrait;
	
	public function __construct(int $mode=ALLOCATION_MODE_UNDEFINED, $context=null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("h-captcha");
		if (! defined('HCAPTCHA_SITE_KEY')) {
			Debug::error("{$f} please define HCAPTCHA_SITE_KEY before using this feature");
		}
		$this->setAttribute("data-sitekey", HCAPTCHA_SITE_KEY);
		$this->setAllowEmptyInnerHTML(true);
	}
	
	public final function hasColumnName():bool{
		return false;
	}
	
	public static function verifyResponse():int{
		$f = __METHOD__;
		try {
			$print = false;
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
			if($print){
				Debug::print("{$f} about to curl hcaptcha with the following arguments:");
				Debug::printArray($args);
			}
			$result = curl_post("https://hcaptcha.com/siteverify", $args);
			if($print){
				Debug::print("{$f} curl result is \"{$result}\"");
			}
			// {"success":false,"error-codes":["invalid-or-already-seen-response"]}
			$parsed = json_decode($result, true);
			if ($parsed["success"]) {
				if($print){
					Debug::print("{$f} success!");
				}
				return SUCCESS;
			}elseif($print){
				Debug::print("{$f} hcaptcha failed with the following error codes:");
				Debug::printArray($parsed['error-codes']);
			}
			return ERROR_HCAPTCHA;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function dispatchCommands(): int{
		$f = __METHOD__;
		if(!$this->hasIdAttribute()){
			Debug::error("{$f} ID attribute is undefined");
		}
		$this->reportSubcommand(new hCaptchaRenderCommand($this));
		return SUCCESS;
	}
}
