<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\password\reset;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\input\ToggleInput;
use JulianSeymour\PHPWebApplicationFramework\ui\ExpandingMenuNestedForm;
use Exception;

class PasswordResetOptionsForm extends ExpandingMenuNestedForm{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("list_leaves", "background_color_5");
		$this->setIdAttribute("password_reset_settings_form");
		$this->setStyleProperties(["padding" => "1rem"]);
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try {
			$vn = $input->getColumnName();
			switch ($vn) {
				case "forgotPasswordEnabled":
					$input->setIdAttribute("forgot_password_enabled");
					break;
				case "forgotUsernameEnabled":
					$input->setIdAttribute("forgot_username_enabled");
					break;
				default:
			}
			return parent::reconfigureInput($input);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_UPDATE
		];
	}

	public static function getExpandingMenuLabelString($context){
		return _("Password reset options");
	}

	public static function getExpandingMenuRadioButtonIdAttribute(){
		return "radio_settings_reset";
	}

	public function getFormDataIndices(): ?array{
		return [
			'forgotPasswordEnabled' => ToggleInput::class,
			'forgotUsernameEnabled' => ToggleInput::class
		];
	}

	public static function getMaxHeightRequirement(){
		return "166px";
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "reset_password";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/settings';
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch ($name) {
			case DIRECTIVE_UPDATE:
				$button = $this->generateGenericButton($name);
				$innerHTML = _("Save settings");
				$button->setInnerHTML($innerHTML);
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
	}
}
