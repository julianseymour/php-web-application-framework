<?php
namespace JulianSeymour\PHPWebApplicationFramework\notification\push;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\input\CheckboxInput;
use JulianSeymour\PHPWebApplicationFramework\input\ToggleInput;
use JulianSeymour\PHPWebApplicationFramework\ui\ExpandingMenuNestedForm;

class PushNotificationSettingsForm extends ExpandingMenuNestedForm{

	public static function getMaxHeightRequirement(){
		return "999px";
	}

	public static function getExpandingMenuLabelString($context)
	{
		return _("Push notifications");
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "push_settings";
	}

	public static function getActionAttributeStatic(): ?string{
		return '/settings';
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_UPDATE
		];
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setIdAttribute("push_notification_settings_form");
		$this->setStyleProperties(["padding" => "1rem"]);
	}

	public function getFormDataIndices(): ?array{
		$indices = [
			"pushAllNotifications" => ToggleInput::class
		];
		foreach(mods()->getTypedNotificationClasses() as $class){
			if($class::getNotificationTypeStatic() === NOTIFICATION_TYPE_TEST || ! $class::canDisable()){
				continue;
			}
			$indices[$class::getPushStatusVariableName()] = ToggleInput::class;
		}
		return $indices;
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		$vn = $input->getColumnName();
		$input->setIdAttribute("toggle_" . $input->getNameAttribute() . "-" . $this->getIdAttribute());
		$context = $this->getContext();
		if($input instanceof CheckboxInput && $context->getColumnValue($vn)){
			$input->setCheckedAttribute("checked");
		}
		return parent::reconfigureInput($input);
	}

	public static function getExpandingMenuRadioButtonIdAttribute(){
		return "radio_settings_push";
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch($name){
			case DIRECTIVE_UPDATE:
				$button = $this->generateGenericButton($name);
				$innerHTML = _("Save push notification settings");
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
