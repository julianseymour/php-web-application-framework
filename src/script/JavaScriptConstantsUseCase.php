<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;

class JavaScriptConstantsUseCase extends LocalizedJavaScriptFileUseCase{
	
	public function echoJavaScriptFileContents():void{
		foreach(mods()->getClientConstants() as $name => $value){
			$const = DeclareVariableCommand::const($name, $value);
			echo $const->toJavaScript().";\n";
			deallocate($const);
		}
	}
	
	public static function getFilename(): string{
		return "constants.js";
	}
}
