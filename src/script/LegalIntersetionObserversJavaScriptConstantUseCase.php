<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;

class LegalIntersetionObserversJavaScriptConstantUseCase extends LocalizedJavaScriptFileUseCase{
	
	public function echoJavaScriptFileContents():void{
		$const = DeclareVariableCommand::const("legalIntersectionObservers");
		$const->setEscapeType(ESCAPE_TYPE_OBJECT);
		$temp = mods()->getLegalIntersectionObservers();
		$values = [];
		foreach($temp as $key => $value){
			$values[$key] = new GetDeclaredVariableCommand($value);
		}
		$const->setValue($values);
		echo $const->toJavaScript().";\n";
		deallocate($const);
	}
	
	public static function getFilename():string{
		return "observers.js";
	}
}