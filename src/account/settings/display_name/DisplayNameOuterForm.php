<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\settings\display_name;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\ui\ExpandingMenuNestedForm;

class DisplayNameOuterForm extends ExpandingMenuNestedForm{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("list_leaves", "background_color_5");
		$this->setIdAttribute("settings_display_name");
		$this->setStyleProperties([
			"display" => "inline-block",
			"padding" => "1rem"
		]);
	}

	public function generateFormHeader(): void{
		$div = new DivElement();
		$innerHTML = _("Your display name is optionally shown in communications and cannot be used for authentication.");
		$div->setInnerHTML($innerHTML);
		$div->setStyleProperties([
			"margin-bottom" => "1rem",
			"white-space" => "normal"
		]);
		$this->appendChild($div);
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch ($name) {
			case DIRECTIVE_UPDATE:
				$button = $this->generateGenericButton($name);
				$button->setInnerHTML(_("Update display name"));
				$button->setStyleProperties(["display" => "block"]);
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}

	public function getFormDataIndices(): ?array{
		return [
			"userNameKey" => DisplayNameInnerForm::class
		];
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_UPDATE
		];
	}

	public static function getExpandingMenuLabelString($context){
		return _("Display name");
	}

	public static function getExpandingMenuRadioButtonIdAttribute(){
		return "radio_settings_name";
	}

	public static function getMaxHeightRequirement(){
		return "135px";
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "update_display_name";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/settings';
	}
}
