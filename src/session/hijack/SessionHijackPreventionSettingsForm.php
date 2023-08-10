<?php
namespace JulianSeymour\PHPWebApplicationFramework\session\hijack;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\session\AbstractSessionForm;

class SessionHijackPreventionSettingsForm extends AbstractSessionForm{
	
	public static function getExpandingMenuLabelString($context){
		return _("Session hijack prevention data");
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "hijack";
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		if ($name !== DIRECTIVE_UPDATE) {
			Debug::error("{$f} invalid button name \"{$name}\"");
		}
		$button = $this->generateGenericButton($name);
		$button->setInnerHTML(_("Update"));
		$button->setStyleProperties(["margin-top" => "1rem"]);
		return [
			$button
		];
	}

	public static function getExpandingMenuRadioButtonIdAttribute(){
		return "radio-hijack_form";
	}

	public static function getActionAttributeStatic(): ?string{
		return "/settings";
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_UPDATE
		];
	}

	protected function getUserAgentHint(){
		return substitute(_("With this option enabled, your session will automatically terminate if your user agent string differs from what it was when you logged in. Your user agent string is \"%1%\""), $_SERVER['HTTP_USER_AGENT']);
	}

	protected function getIpAddressHint(){
		return substitute(_("With this option enabled, your session will automatically terminate if your IP address changes from what it was when you logged in. Your IP address is %1%"), $_SERVER['REMOTE_ADDR']);
	}

	protected function getFormHeaderString(){
		$dur = SESSION_TIMEOUT_SECONDS / 60;
		return substitute(_("Sessions last %1% minutes or until you delete your cookies."), $dur);
	}
}
