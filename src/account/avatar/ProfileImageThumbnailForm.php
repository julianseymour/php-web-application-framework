<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\avatar;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\image\FocalImageThumbnailForm;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use Exception;

class ProfileImageThumbnailForm extends AjaxForm{

	public static function getExpandingMenuLabelString($context){
		return _("Profile image");
	}

	public static function getExpandingMenuRadioButtonIdAttribute(): string{
		return "radio_settings_profile_pic";
	}

	public static function getMaxHeightRequirement(): string{
		return "256px";
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setStyleProperties([
			"padding" => "1rem",
			"text-align" => "center"
		]);
	}

	public static function getNewFormOption(): bool{
		return true;
	}

	public function bindContext($context){
		$f = __METHOD__;
		$print = false;
		if ($context->getDataType() !== DATATYPE_USER) {
			Debug::error("{$f} this is not the client object");
			$context = $context->getUserData();
		}
		if($print){
			if($context->hasProfileImageKey()){
				Debug::print("{$f} context has a profile image key");
				if($context->hasProfileImageData()){
					$decl = $context->getProfileImageData()->getDeclarationLine();
					Debug::print("{$f} it also has a profile image data, declared {$decl}");
					if($context->getProfileImageData()->isUninitialized()){
						Debug::print("{$f} the profile image data is uninitialized");
					}else{
						Debug::print("{$f} the profile image data has been initialized");
					}
				}else{
					Debug::print("{$f} however, it lacks a profile image data");
				}
			}else{
				Debug::print("{$f} context lack a profile image key");
			}
		}
		$ret = parent::bindContext($context);
		$this->setIdAttribute("profile_image_form-" . $context->getIdentifierValue());
		return $ret;
	}

	public function getAdHocInputs(): ?array{
		$f = __METHOD__;
		try {
			$inputs = parent::getAdHocInputs();
			$hidden = new HiddenInput($this->getAllocationMode());
			$hidden->setIgnoreDatumSensitivity(true);
			$hidden->setNameAttribute("profile_image");
			$inputs[$hidden->getNameAttribute()] = $hidden;
			return $inputs;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getDirectives(): ?array
	{
		$names = [
			DIRECTIVE_UPDATE
		];
		if ($this->getContext()->hasProfileImageKey()) {
			array_push($names, DIRECTIVE_DELETE_FOREIGN);
		}
		return $names;
	}

	public function getFormDataIndices(): ?array
	{
		return [
			'profileImageKey' => FocalImageThumbnailForm::class
		];
	}

	public static function getFormDispatchIdStatic(): ?string
	{
		return "profile_image";
	}

	public static function getActionAttributeStatic(): ?string
	{
		return '/settings';
	}

	public function generateButtons(string $name): ?array
	{
		$f = __METHOD__;
		$button = $this->generateGenericButton($name);
		switch ($name) {
			case DIRECTIVE_UPDATE:
				$button = $this->generateGenericButton($name);
				$innerHTML = _("Update profile image");
				break;
			case DIRECTIVE_DELETE_FOREIGN:
				$button = $this->generateGenericButton($name, "profileImageKey");
				// $button->setValueAttribute("profileImageKey");
				$innerHTML = _("Delete profile image");
				break;
			default:
				Debug::error("{$f} invalid name attribute \"{$name}\"");
				return null;
		}
		$button->setInnerHTML($innerHTML);
		$button->setWrapperElement(Document::createElement("div")->withStyleProperties([
			"margin-top" => "1rem"
		]));
		/*$button->setStyleProperties([
			"display" => "block"
		]);*/
		return [
			$button
		];
	}

	public static function getMethodAttributeStatic(): ?string
	{
		return HTTP_REQUEST_METHOD_POST;
	}
}
