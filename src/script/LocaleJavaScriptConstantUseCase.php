<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;

class LocaleJavaScriptConstantUseCase extends LocalizedJavaScriptFileUseCase{
	
	public function echoJavaScriptFileContents():void{
		static::echoJavaScriptFileContentsStatic($this->getInputParameter("locale"));
	}
	
	public static function echoJavaScriptFileContentsStatic(string $locale):void{
		$locale = CommandBuilder::const('LOCALE', $locale);
		echo $locale->toJavaScript().";\n";
		deallocate($locale);
	}
	
	public static function getFilename(): string{
		return "locale.js";
	}
}
