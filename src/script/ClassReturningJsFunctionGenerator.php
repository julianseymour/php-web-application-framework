<?php
namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\control\SwitchCommand;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;

abstract class ClassReturningJsFunctionGenerator extends JavaScriptFunctionGenerator
{

	/**
	 * generates a JS function named $functionName that returns JS classes $classes
	 *
	 * @param string $functionName
	 * @param string[] $classes
	 * @return JavaScriptFunction
	 */
	protected static function generateGetJavaScriptClassFunction(string $functionName, array $classes): JavaScriptFunction
	{
		$function = new JavaScriptFunction($functionName, "id");
		$function->setRoutineType(ROUTINE_TYPE_STATIC);
		$declarations = [];
		$switch = new SwitchCommand(new GetDeclaredVariableCommand("id"));
		foreach ($classes as $className) {
			$short = get_short_class($className);
			$declarations[$short] = new GetDeclaredVariableCommand($short);
			$switch->case($className::getJavaScriptClassIdentifier(), CommandBuilder::return(new GetDeclaredVariableCommand("classNames['{$short}']")));
		}
		$switch->default(CommandBuilder::return(new CallFunctionCommand("error", new GetDeclaredVariableCommand("f"), new ConcatenateCommand('Invalid class identifier \"', new GetDeclaredVariableCommand("id"), '\"'))));
		$declaration = DeclareVariableCommand::let("classNames", $declarations);
		$declaration->setEscapeType(ESCAPE_TYPE_OBJECT);
		$function->pushSubcommand($declaration, $switch);
		return $function;
	}
}
