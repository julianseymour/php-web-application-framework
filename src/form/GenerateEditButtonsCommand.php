<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class GenerateEditButtonsCommand extends GenerateFormButtonsCommand
{

	public static function getCommandId(): string
	{
		return "generateEditButtons";
	}

	public static function extractAnyway()
	{
		return false;
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //GenerateEditButtonsCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		$idcs = $this->getIdCommandString();
		if($idcs instanceof JavaScriptInterface) {
			$idcs = $idcs->toJavaScript();
		}
		return "AjaxForm.generateEditButtons({$idcs}, context)";
	}
}
