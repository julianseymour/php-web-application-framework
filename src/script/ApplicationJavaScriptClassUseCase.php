<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\app\generator\ApplicationJavaScriptGenerator;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;

class ApplicationJavaScriptClassUseCase extends LocalizedJavaScriptFileUseCase{
	
	public function echoJavaScriptFileContents():void{
		$const = CommandBuilder::const(
			'APPLICATION_CONFIG_CLASS_NAME', get_short_class(config())
		);
		echo $const->toJavaScript().";\n";
		deallocate($const);
		$class = ApplicationJavaScriptGenerator::generateJavaScriptClass(mods());
		echo "{$class}\n";
		deallocate($class);
		$crf = ApplicationJavaScriptGenerator::generateClassReturningFunction(mods());
		echo "{$crf}\n";
		deallocate($crf);
	}
	
	public static function getFilename(): string{
		return "application.js";
	}
}
