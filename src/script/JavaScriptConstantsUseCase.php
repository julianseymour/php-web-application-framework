<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;

class JavaScriptConstantsUseCase extends JavaScriptFileUseCase{
	
	public function echoJavaScriptFileContents():void{
		foreach(mods()->getClientConstants() as $name => $value){
			$const = DeclareVariableCommand::const($name, $value);
			echo $const->toJavaScript().";\n";
		}
	}
}
