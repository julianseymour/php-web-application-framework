<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\dismiss;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use Exception;

class DismissAllNotificationsForm extends AjaxForm{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setIdAttribute("dismiss_all_form");
	}

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function getAdHocInputs(): ?array{
		$f = __METHOD__;
		try {
			$mode = $this->getAllocationMode();
			$inputs = parent::getAdHocInputs();
			$ai = new HiddenInput($mode);
			$ai->setNameAttribute("all");
			$ai->setValueAttribute(1);
			$inputs[$ai->getNameAttribute()] = $ai;
			return $inputs;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getDirectives(): ?array{
		return [
			"dismiss_all"
		];
	}

	public function getFormDataIndices(): ?array{
		return [];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "dismiss_all";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/dismiss_all';
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch ($name) {
			case "dismiss_all":
				$button = new ButtonInput();
				$button->setNameAttribute("directive");
				$button->setValueAttribute($name);
				$innerHTML = _("Dismiss all");
				$button->setInnerHTML($innerHTML);
				$button->setOnClickAttribute($button->getDefaultOnClickAttribute());
				$button->setTypeAttribute("submit");
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}
}
