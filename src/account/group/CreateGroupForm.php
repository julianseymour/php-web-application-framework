<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\group;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;

/**
 * this form binds to a GroupInvitation that gets inserted for the current user
 *
 * @author j
 *        
 */
class CreateGroupForm extends AjaxForm{

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "create_group";
	}

	public function generateButtons(string $directive): ?array{
		$f = __METHOD__;
		if($directive !== DIRECTIVE_INSERT) {
			Debug::error("{$f} invalid directive \"{$directive}\"");
		}
		$button = $this->generateGenericButton($directive);
		$button->setInnerHTML(_("Create group"));
		return [
			$button
		];
	}

	public static function getActionAttributeStatic(): ?string{
		return "/create_group";
	}

	public function getFormDataIndices(): ?array{
		return [
			"groupKey" => EditGroupForm::class
		];
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_INSERT
		];
	}
}
