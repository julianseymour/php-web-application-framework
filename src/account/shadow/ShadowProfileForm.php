<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\shadow;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\form\GenerateEditButtonsCommand;
use JulianSeymour\PHPWebApplicationFramework\input\EmailInput;
use JulianSeymour\PHPWebApplicationFramework\input\TextInput;
use JulianSeymour\PHPWebApplicationFramework\language\Internationalization;
use JulianSeymour\PHPWebApplicationFramework\language\settings\LanguageSettingsData;
use JulianSeymour\PHPWebApplicationFramework\app\Request;

class ShadowProfileForm extends AjaxForm{

	public static function getFormDispatchIdStatic(): ?string{
		return "shadow";
	}

	public function bindContext($context){
		$resolved_key = $this->getResolvedKey($context);
		$this->setIdAttribute(new ConcatenateCommand("shadow_profile_form-", $resolved_key));
		return parent::bindContext($context);
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch($name){
			case DIRECTIVE_INSERT:
			case DIRECTIVE_UPDATE:
			case DIRECTIVE_DELETE:
				return [
					$this->generateGenericButton($name)
				];
			default:
				Debug::error("{$f} invalid input name \"{$name}\"");
		}
	}

	public static function getActionAttributeStatic(): ?string{
		return '/shadows';
	}

	public function getFormDataIndices(): ?array{
		$session = new LanguageSettingsData();
		$lang = $session->getLanguageCode();
		deallocate($session);
		if(Internationalization::lastNameFirst($lang)){
			$indices = [
				'lastName' => TextInput::class,
				'firstName' => TextInput::class
			];
		}else{
			$indices = [
				'firstName' => TextInput::class,
				'lastName' => TextInput::class
			];
		}
		$indices['emailAddress'] = EmailInput::class;
		return $indices;
	}

	public static function getMethodAttributeStatic():?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function getDirectives():?array{
		$f = __METHOD__;
		$print = false;
		$context = $this->getContext();
		if($context->isUninitialized()){
			if($print){
				Debug::error("{$f} context ".$context->getDebugString()." is uninitialized");
			}
			return [DIRECTIVE_INSERT];
		}elseif($print){
			Debug::print("{$f} context ".$context->getDebugString()." is initialized");
		}
		return [
			DIRECTIVE_UPDATE,
			DIRECTIVE_DELETE
		];
	}

	protected static function getGenerateFormButtonsCommandClassStatic(): string
	{
		return GenerateEditButtonsCommand::class;
	}
}