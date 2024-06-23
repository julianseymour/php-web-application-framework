<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\cookie;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;

class DeleteCookiesForm extends AjaxForm{

	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setIdAttribute("delete_cookies_form");
	}

	public function generateButtons(string $directive): ?array{
		$f = __METHOD__;
		switch($directive){
			case DIRECTIVE_DELETE:
				$button = $this->generateGenericButton($directive);
				$button->setInnerHTML(_("Delete all cookies"));
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
			DIRECTIVE_DELETE
		];
	}

	public function generateFormHeader(): void{
		$div1 = new DivElement(ALLOCATION_MODE_LAZY);
		$innerHTML = _("Click \"Delete all cookies\" to remove all cookies placed on your device by this website. This will terminate your session.");
		$div1->setInnerHTML($innerHTML);
		$this->appendChild($div1);
	}

	public static function getActionAttributeStatic(): ?string{
		return "/delete_cookies";
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "delete_cookies";
	}
}
