<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;

class LocaleJavaScriptConstantUseCase extends LocalizedJavaScriptFileUseCase{
	
	public function echoJavaScriptFileContents():void{
		static::echoJavaScriptFileContentsStatic($this->getInputParameter("locale"));
	}
	
	public static function echoJavaScriptFileContentsStatic(string $locale):void{
		echo CommandBuilder::const('LOCALE', $locale)->toJavaScript().";\n";
	}
	
	public static function getFilename(): string{
		return "locale.js";
	}
}
