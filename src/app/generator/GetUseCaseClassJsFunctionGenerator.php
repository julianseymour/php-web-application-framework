<?php
namespace JulianSeymour\PHPWebApplicationFramework\app\generator;

use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\control\SwitchCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunctionGenerator;

class GetUseCaseClassJsFunctionGenerator extends JavaScriptFunctionGenerator
{

	public function generate($context): ?JavaScriptFunction
	{
		$function = new JavaScriptFunction("getUseCaseClass", "id");
		$function->setRoutineType(ROUTINE_TYPE_STATIC);
		$declarations = [
			"UseCase" => new GetDeclaredVariableCommand("UseCase")
		];
		$switch = new SwitchCommand(new GetDeclaredVariableCommand("id"));

		foreach ($context->getClientUseCaseDictionary() as $code => $className) {
			$declarations[$className] = new GetDeclaredVariableCommand($className);
			$switch->case($code, CommandBuilder::return(new GetDeclaredVariableCommand("classNames['{$className}']")));
		}
		$switch->default(CommandBuilder::log("Default use case"), CommandBuilder::return(new GetDeclaredVariableCommand("classNames['UseCase']")) /*
		                                                                                                                                           * ,
		                                                                                                                                           * CommandBuilder::return(
		                                                                                                                                           * new CallFunctionCommand(
		                                                                                                                                           * "error", "f", "'Invalid class identifier \"'.concat(id).concat('\"')"
		                                                                                                                                           * )
		                                                                                                                                           * )
		                                                                                                                                           */
		);
		$declaration = DeclareVariableCommand::let("classNames", $declarations);
		$declaration->setEscapeType(ESCAPE_TYPE_OBJECT);
		$function->pushSubcommand($declaration, $switch);
		return $function;
	}
}
