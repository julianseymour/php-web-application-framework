<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\app\generator\ApplicationJavaScriptGenerator;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;

class ApplicationJavaScriptClassUseCase extends LocalizedJavaScriptFileUseCase{
	
	public function echoJavaScriptFileContents():void{
		echo CommandBuilder::const('APPLICATION_CONFIG_CLASS_NAME', get_short_class(config()))->toJavaScript() . ";\n";
		echo ApplicationJavaScriptGenerator::generateJavaScriptClass(mods())."\n";
		echo ApplicationJavaScriptGenerator::generateClassReturningFunction(mods())."\n";
	}
	
	public static function getFilename(): string{
		return "application.js";
	}
}