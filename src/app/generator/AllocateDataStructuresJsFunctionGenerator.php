<?php
namespace JulianSeymour\PHPWebApplicationFramework\app\generator;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\control\BreakCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\ForEachLoopCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunctionGenerator;
use Exception;

class AllocateDataStructuresJsFunctionGenerator extends JavaScriptFunctionGenerator
{

	public function generate($context): ?JavaScriptFunction
	{
		$f = __METHOD__; //AllocateDataStructuresJsFunctionGenerator::getShortClass()."(".static::getShortClass().")->generate()";
		try {
			$classes = $context->getClientDataStructureClasses();
			if (empty($classes)) {
				return null;
			}
			$function = new JavaScriptFunction("allocateDataStructures", "data", "response");
			$function->setRoutineType(ROUTINE_TYPE_STATIC);
			$cases = [];
			$break = new BreakCommand();
			$raw_data = new GetDeclaredVariableCommand("raw_data");
			$response = new GetDeclaredVariableCommand("response");
			$default = [
				/*CommandBuilder::if(
					CommandBuilder::equals(
						new GetDeclaredVariableCommand("raw_data.dataType"), 
						"JulianSeymourmessengerSecureMessage"
					)
				)->then(
					CommandBuilder::alert("Invalid dataType"),
					CommandBuilder::return()
				),*/
				CommandBuilder::log("Default DataStructure"),
				DeclareVariableCommand::redeclare("struct", CommandBuilder::construct("DataStructure", $raw_data, $response)),
				$break
			];
			foreach ($classes as $type => $value) {
				if (is_array($value)) {
					$switch = CommandBuilder::switch(new GetDeclaredVariableCommand("raw_data.subtype"))->default($default);
					foreach ($value as $subtype => $class) {
						$switch->case(
							$subtype, 
							DeclareVariableCommand::redeclare(
								"struct", 
								CommandBuilder::construct($class, $raw_data, $response)
							), 
							$break
						);
					}
					$cases[$type] = [
						$switch,
						$break
					];
				} elseif (is_string($type)) {
					$cases[$type] = [
						CommandBuilder::log("Calling constructor \"".get_short_class($value)."\""),
						DeclareVariableCommand::redeclare(
							"struct", 
							CommandBuilder::construct($value, $raw_data, $response)
						),
						$break
					];
				}
			}
			$function->pushSubcommand(
				DeclareVariableCommand::let("print", false), 
				new ForEachLoopCommand(
					DeclareVariableCommand::let("key"), 
					"data", 
					DeclareVariableCommand::let(
						"raw_data", 
						CommandBuilder::arrayAccess(new GetDeclaredVariableCommand("data"), "key")
					),
					DeclareVariableCommand::let("struct"), 
					CommandBuilder::switch(
						new GetDeclaredVariableCommand("raw_data.dataType")
					)->cases($cases)->default($default), 
					CommandBuilder::call(
						"response.setDataStructure", 
						new GetDeclaredVariableCommand("key"), 
						new GetDeclaredVariableCommand("struct")
					), 
					DeclareVariableCommand::redeclare("struct", null)
				)
			);
			return $function;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}