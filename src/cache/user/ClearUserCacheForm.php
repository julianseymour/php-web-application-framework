<?php
namespace JulianSeymour\PHPWebApplicationFramework\cache\user;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;

class ClearUserCacheForm extends AjaxForm{

	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setIdAttribute("user_cache_form");
	}

	public function generateButtons(string $directive): ?array{
		$f = __METHOD__; //ClearUserCacheForm::getShortClass()."(".static::getShortClass().")->generateButtons()";
		switch ($directive) {
			case DIRECTIVE_SUBMIT:
				$button = $this->generateGenericButton($directive);
				$button->setInnerHTML(_("Clear cache"));
				$button->setOnClickAttribute("clearClientCache(event, this);");
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid directive \"{$directive}\"");
		}
	}

	public function getFormDataIndices(): ?array{
		return [];
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_SUBMIT
		];
	}

	public static function getActionAttributeStatic(): ?string{
		return "/user_cache";
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "clear_user_cache";
	}
}
