<?php

namespace JulianSeymour\PHPWebApplicationFramework\app\generator;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunctionGenerator;
use Exception;

class HandleMessageEventJsFunctionGenerator extends JavaScriptFunctionGenerator{

	public function generate($context): ?JavaScriptFunction{
		$f = __METHOD__;
		try{
			//$print = false;
			$function = new JavaScriptFunction("handleMessageEvent", "response");
			$function->setRoutineType(ROUTINE_TYPE_STATIC);
			$intent = new GetDeclaredVariableCommand("intent");
			$function->pushCodeBlock(
				CommandBuilder::let("f", "handleMessageEvent"), 
				CommandBuilder::let(
					"intent", 
					new GetDeclaredVariableCommand("response.getIntent()")
				), 
				CommandBuilder::error(
					new ConcatenateCommand(
						new GetDeclaredVariableCommand("f"), 
						": inside handleMessageEvent with intent \"", 
						$intent, 
						"\""
					)
				), 
				CommandBuilder::switch(
					$intent, 
					$context->getMessageEventHandlerCases(), 
					new CallFunctionCommand(
						"error", 
						new GetDeclaredVariableCommand("f"), 
						new ConcatenateCommand("Invalid intent \"", $intent, "\"")
					)
				)
			);
			return $function;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
