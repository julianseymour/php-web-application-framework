<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;

class InvokeableFunctionsJavaScriptConstantUseCase extends LocalizedJavaScriptFileUseCase{
	
	public function echoJavaScriptFileContents():void{
		$f = __METHOD__;
		$const = DeclareVariableCommand::const("invokable");
		$const->setEscapeType(ESCAPE_TYPE_OBJECT);
		$temp = mods()->getInvokeableJavaScriptFunctions();
		$values = [];
		foreach($temp as $key => $value) {
			$values[$key] = new GetDeclaredVariableCommand($value);
		}
		$const->setValue($values);
		echo $const->toJavaScript().";\n";
	}
	
	public static function getFilename(): string{
		return "invokeable.js";
	}
}
