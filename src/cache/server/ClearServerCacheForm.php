<?php

namespace JulianSeymour\PHPWebApplicationFramework\cache\server;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;

class ClearServerCacheForm extends AjaxForm{

	public static function getFormDispatchIdStatic(): ?string{
		return "clear_cache";
	}

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_POST;
	}

	public function generateButtons(string $directive): ?array{
		$f = __METHOD__;
		if ($directive !== "clear") {
			Debug::error("{$f} invalid directive \"{$directive}\"");
		}
		return [
			ButtonInput::createElement(ALLOCATION_MODE_LAZY)->withInnerHTML(_("All"))->withAttributes([
				'id' => "clear_cache-all",
				'name' => 'clear',
				'type' => 'submit',
				'value' => CONST_ALL,
				"onclick" => "AjaxForm.appendSubmitterName(event, this)"
			]),
			ButtonInput::createElement(ALLOCATION_MODE_LAZY)->withInnerHTML(_("APCu"))->withAttributes([
				'id' => "clear_cache-apcu",
				'name' => 'clear',
				'type' => 'submit',
				'value' => 'apcu',
				"onclick" => "AjaxForm.appendSubmitterName(event, this)"
			]),
			ButtonInput::createElement(ALLOCATION_MODE_LAZY)->withInnerHTML(_("File"))->withAttributes([
				'id' => "clear_cache-file",
				'name' => 'clear',
				'type' => 'submit',
				'value' => 'file',
				"onclick" => "AjaxForm.appendSubmitterName(event, this)"
			])
		];
	}

	public static function getActionAttributeStatic(): ?string{
		return "/server_cache";
	}

	public function getFormDataIndices(): ?array{
		return [];
	}

	public function getDirectives(): ?array{
		return [
			"clear"
		];
	}

	protected function afterConstructorHook(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null): int{
		$this->setIdAttribute("clear_cache_form");
		$status = parent::afterConstructorHook($mode, $context);
		return $status;
	}
}
