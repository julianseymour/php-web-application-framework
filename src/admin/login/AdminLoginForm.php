<?php
namespace JulianSeymour\PHPWebApplicationFramework\admin\login;

use JulianSeymour\PHPWebApplicationFramework\account\login\LoginForm;

class AdminLoginForm extends LoginForm
{

	protected function getLoginDirective(): string
	{
		return DIRECTIVE_ADMIN_LOGIN;
	}

	public static function getFormDispatchIdStatic(): ?string
	{
		return "admin_login";
	}

	public static function getActionAttributeStatic(): ?string
	{
		return "/admin_login";
	}
}
