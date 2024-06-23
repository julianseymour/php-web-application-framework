<?php

namespace JulianSeymour\PHPWebApplicationFramework\app\generator;

use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\control\SwitchCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunctionGenerator;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;

class GetUseCaseClassJsFunctionGenerator extends JavaScriptFunctionGenerator{

	public function generate($context): ?JavaScriptFunction{
		$function = new JavaScriptFunction("getUseCaseClass", "id");
		$function->setRoutineType(ROUTINE_TYPE_STATIC);
		$declarations = [
			"UseCase" => new GetDeclaredVariableCommand("UseCase")
		];
		$id = new GetDeclaredVariableCommand("id");
		$switch = new SwitchCommand($id);
		foreach($context->getClientUseCaseDictionary() as $code => $className){
			$declarations[$className] = new GetDeclaredVariableCommand($className);
			$switch->case(
				$code, 
				CommandBuilder::log(new ConcatenateCommand("Action attribute ", $id)),
				CommandBuilder::return(new GetDeclaredVariableCommand("classNames['{$className}']"))
			);
		}
		$switch->default(
			CommandBuilder::log(
				new ConcatenateCommand("Default use case for value ", $id)
			), 
			CommandBuilder::return(new GetDeclaredVariableCommand("classNames['UseCase']"))
		);
		$declaration = DeclareVariableCommand::let("classNames", $declarations);
		$declaration->setEscapeType(ESCAPE_TYPE_OBJECT);
		$function->pushCodeBlock($declaration, $switch);
		return $function;
	}
}
