<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\role;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\form\RepeatingFormInterface;
use JulianSeymour\PHPWebApplicationFramework\form\RepeatingFormTrait;
use JulianSeymour\PHPWebApplicationFramework\input\TextInput;

class RoleDeclarationForm extends AjaxForm implements RepeatingFormInterface
{

	use RepeatingFormTrait;

	public static function getFormDispatchIdStatic(): ?string
	{
		return "declare_role";
	}

	public function generateButtons(string $directive): ?array
	{
		$f = __METHOD__; //RoleDeclarationForm::getShortClass()."(".static::getShortClass().")->generateButtons()";
		ErrorMessage::unimplemented($f);
	}

	public static function getActionAttributeStatic(): ?string
	{
		return "/groups";
	}

	public function getFormDataIndices(): ?array
	{
		return [
			"name" => TextInput::class
		];
	}

	public function getDirectives(): ?array
	{
		$f = __METHOD__; //RoleDeclarationForm::getShortClass()."(".static::getShortClass().")->getDirectives()";
		ErrorMessage::unimplemented($f);
	}
}
