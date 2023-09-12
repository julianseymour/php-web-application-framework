<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\group;

use JulianSeymour\PHPWebApplicationFramework\account\role\RoleDeclaration;
use JulianSeymour\PHPWebApplicationFramework\account\role\RoleDeclarationForm;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\TextInput;

class EditGroupForm extends AjaxForm
{

	public static function getFormDispatchIdStatic(): ?string
	{
		return "edit_group";
	}

	public function generateButtons(string $directive): ?array
	{
		$f = __METHOD__; //EditGroupForm::getShortClass()."(".static::getShortClass().")->generateButtons()";
		if(!is_string($directive)) {
			Debug::error("{$f} directive must be a string");
		}elseif(empty($directive)) {
			Debug::error("{$f} received empty string as input parameter");
		}
		switch ($directive) {
			case DIRECTIVE_UPDATE:
			case DIRECTIVE_DELETE:
				return [
					$this->generateGenericButton($directive)
				];
			default:
				Debug::error("{$f} invalid directive \"{$directive}\"");
		}
	}

	public static function getActionAttributeStatic(): ?string
	{
		return "/edit_group";
	}

	public function getFormDataIndices(): ?array
	{
		$ret = [
			"name" => TextInput::class
			// "groupType" => SelectInput::class
		];
		if(!$this->getContext()->isUninitialized()) {
			$ret[RoleDeclaration::getPhylumName()] = RoleDeclarationForm::class;
		}
		return $ret;
	}

	public function getDirectives(): ?array
	{
		return [
			DIRECTIVE_UPDATE,
			DIRECTIVE_DELETE
		];
	}

	public static function getNewFormOption(): bool
	{
		return true;
	}

	/*
	 * protected function getSubordinate/FormClasses(){
	 * return [
	 * RoleDeclaration::getPhylumName() => RoleDeclarationForm::class
	 * ];
	 * }
	 */
}
