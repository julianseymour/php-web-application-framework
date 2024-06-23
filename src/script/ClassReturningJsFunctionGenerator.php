<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use JulianSeymour\PHPWebApplicationFramework\command\control\ReturnCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\SwitchCommand;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

abstract class ClassReturningJsFunctionGenerator extends JavaScriptFunctionGenerator{

	/**
	 * generates a JS function named $functionName that returns JS classes $classes
	 *
	 * @param string $functionName
	 * @param string[] $classes
	 * @return JavaScriptFunction
	 */
	protected static function generateGetJavaScriptClassFunction(string $functionName, array $classes): JavaScriptFunction{
		$f = __METHOD__;
		$function = new JavaScriptFunction($functionName, "id");
		$function->setRoutineType(ROUTINE_TYPE_STATIC);
		$declarations = [];
		$switch = new SwitchCommand(new GetDeclaredVariableCommand("id"));
		foreach($classes as $className){
			$case = $className::getJavaScriptClassIdentifier();
			if($switch->hasCase($case)){
				Debug::error("{$f} SwitchCommand already has a case for \"{$case}\"");
			}
			$short = get_short_class($className);
			$declarations[$short] = new GetDeclaredVariableCommand($short);
			$gdvc2 = new GetDeclaredVariableCommand("classNames['{$short}']");
			$return = new ReturnCommand();
			$return->setValue($gdvc2);
			$switch->case(
				$case, 
				$return
			);
		}
		$switch->default(
			new ReturnCommand(
				new CallFunctionCommand(
					"error", 
					new GetDeclaredVariableCommand("f"), 
					new ConcatenateCommand(
						'Invalid class identifier \"', 
						new GetDeclaredVariableCommand("id"), 
						'\"'
					)
				)
			)
		);
		$declaration = DeclareVariableCommand::let("classNames", $declarations);
		$declaration->setEscapeType(ESCAPE_TYPE_OBJECT);
		$function->pushCodeBlock($declaration, $switch);
		return $function;
	}
}
