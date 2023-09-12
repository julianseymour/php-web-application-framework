<?php
namespace JulianSeymour\PHPWebApplicationFramework\location;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateElementInterface;

class InitializeLocationForm extends AjaxForm implements TemplateElementInterface
{

	public static function getTemplateContextClass(): string
	{
		return GeolocationPositionData::class;
	}

	public static function getFormDispatchIdStatic(): ?string
	{
		return "initialize_location";
	}

	public function generateButtons(string $name): ?array
	{
		$f = __METHOD__; //InitializeLocationForm::getShortClass()."(".static::getShortClass().")->generateButtons()";
		if($name !== DIRECTIVE_READ_MULTIPLE) {
			Debug::error("{$f} invalid button name \"{$name}\"");
		}
		return [
			$this->generateGenericButton($name)
		];
	}

	public static function getActionAttributeStatic(): ?string
	{
		return "/" . static::getFormDispatchIdStatic();
	}

	public function getFormDataIndices(): ?array
	{
		return [
			'accuracy' => HiddenInput::class,
			'altitude' => HiddenInput::class,
			'altitudeAccuracy' => HiddenInput::class,
			'heading' => HiddenInput::class,
			'latitude' => HiddenInput::class,
			'longitude' => HiddenInput::class,
			'speed' => HiddenInput::class,
			'timestamp' => HiddenInput::class
		];
	}

	public function getDirectives(): ?array
	{
		return [
			DIRECTIVE_READ_MULTIPLE
		];
	}
}
