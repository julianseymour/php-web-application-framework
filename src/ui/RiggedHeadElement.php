<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\getCurrentUserLanguagePreference;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\BaseElement;
use JulianSeymour\PHPWebApplicationFramework\element\HeadElement;
use JulianSeymour\PHPWebApplicationFramework\element\LinkElement;
use JulianSeymour\PHPWebApplicationFramework\element\MetaElement;
use JulianSeymour\PHPWebApplicationFramework\element\ScriptElement;
use JulianSeymour\PHPWebApplicationFramework\element\TitleElement;
use Exception;

class RiggedHeadElement extends HeadElement{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setIdAttribute("head");
	}

	protected static function allowUseCaseAsContext(): bool{
		return true;
	}

	/*public static function getStringTableScriptElement()
	{
		$lang = getCurrentUserLanguagePreference();
		$script = new ScriptElement();
		$path = "/script/strings-{$lang}.js";
		$script->setSourceAttribute($path);
		$script->setTypeAttribute("text/javascript");
		$script->setAllowEmptyInnerHTML(true);
		$script->setIdAttribute("string_table_script");
		return $script;
	}*/

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try {
			$charset = new MetaElement();
			$charset->setCharacterSetAttribute("UTF-8");
			$this->appendChild($charset);
			$viewport = new MetaElement();
			$viewport->setNameAttribute("viewport");
			$viewport->setContentAttribute("user-scalable=no, width=device-width, initial-scale=1.0,interactive-widget=resizes-content");
			$this->appendChild($viewport);
			$csp = new ContentSecurityPolicyElement(); 
			$this->appendChild($csp);
			$base = new BaseElement();
			$base->setHrefAttribute('/');
			$this->appendChild($base);
			$title = new TitleElement();
			$title->setInnerHTML(WEBSITE_NAME);
			$this->appendChild($title);
			$bundle_css = true;
			if ($bundle_css) {
				$styles = [
					'bundle'
				];
				foreach ($styles as $s) {
					$link = new LinkElement();
					$link->setTypeAttribute("text/css");
					$link->setRelationshipAttribute("stylesheet");
					$link->setHrefAttribute("/style/{$s}.css");
					$this->appendChild($link);
				}
			} else {
				foreach (mods()->getCascadingStyleSheetFilepaths() as $fn => $path) {
					$fn = "/style/{$fn}";
					$link = new LinkElement();
					$link->setTypeAttribute("text/css");
					$link->setRelationshipAttribute("stylesheet");
					$link->setHrefAttribute($fn);
					$this->appendChild($link);
				}
			}
			$this->appendChild(RiggedHeadElement::getStringTableScriptElement());
			$scripts = [
				"bundle"
			];
			foreach ($scripts as $s) {
				$script = new ScriptElement();
				$path = "/script/{$s}.js";
				// Debug::print("{$f} path to script file: \"{$path}\"");
				$script->setSourceAttribute($path);
				$script->setTypeAttribute("text/javascript");
				$script->setAllowEmptyInnerHTML(true);
				$this->appendChild($script);
			}
			if (defined("HCAPTCHA_SITE_KEY")) {
				$hcaptcha = new ScriptElement();
				$hcaptcha->setSourceAttribute("https://hcaptcha.com/1/api.js");
				$hcaptcha->setAsyncAttribute("async");
				$hcaptcha->setDeferAttribute("defer");
				$hcaptcha->setAllowEmptyInnerHTML(true);
				$this->appendChild($hcaptcha);
			} else {
				Debug::error("{$f} hCaptcha site key is undefined");
			}
			// script for sending a js enabled cookine on non-ajax requests
			$najecs = new ScriptElement();
			$najecs->setInnerHTML("setNonAjaxJsEnabledCookie()");
			$this->appendChild($najecs);
			$manifest = new LinkElement();
			$manifest->setRelationshipAttribute("manifest");
			$manifest->setHrefAttribute("/manifest.json");
			$this->appendChild($manifest);
			return $this->getChildNodes();
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
