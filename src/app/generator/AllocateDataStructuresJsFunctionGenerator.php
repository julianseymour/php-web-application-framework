<?php
namespace JulianSeymour\PHPWebApplicationFramework\app\generator;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\control\BreakCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\ForEachLoopCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\SwitchCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\ConstructorCommand;
use JulianSeymour\PHPWebApplicationFramework\command\debug\LogCommand;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\arr\ArrayAccessCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunctionGenerator;
use Exception;

class AllocateDataStructuresJsFunctionGenerator extends JavaScriptFunctionGenerator{

	public function generate($context): ?JavaScriptFunction{
		$f = __METHOD__;try{
			$classes = $context->getClientDataStructureClasses();
			if(empty($classes)){
				return null;
			}
			$function = new JavaScriptFunction("allocateDataStructures", "data", "response");
			$function->setRoutineType(ROUTINE_TYPE_STATIC);
			$cases = [];
			$break = new BreakCommand();
			$raw_data = new GetDeclaredVariableCommand("raw_data");
			$response = new GetDeclaredVariableCommand("response");
			$datatype = new GetDeclaredVariableCommand("raw_data.dataType");
			$get_subtype = new GetDeclaredVariableCommand("raw_data.subtype");
			foreach($classes as $type => $value){
				if(is_array($value)){
					$switch = new SwitchCommand($get_subtype);
					$concat1 = new ConcatenateCommand();
					$concat1->setStrings(["Default DataStructure for datatype ", $datatype, ", sub type ", $get_subtype]);
					$switch->default([
						new LogCommand($concat1),
						DeclareVariableCommand::redeclare("struct", new ConstructorCommand("DataStructure", $raw_data, $response)),
						$break
					]);
					foreach($value as $subtype => $class){
						$switch->case(
							$subtype, 
							DeclareVariableCommand::redeclare(
								"struct", 
								new ConstructorCommand($class, $raw_data, $response)
							), 
							$break
						);
					}
					$cases[$type] = [
						$switch,
						$break
					];
				}elseif(is_string($type)){
					$cases[$type] = [
						new LogCommand("Calling constructor \"".get_short_class($value)."\""),
						DeclareVariableCommand::redeclare(
							"struct", 
							new ConstructorCommand($value, $raw_data, $response)
						),
						$break
					];
				}
			}
			$switch2 = new SwitchCommand($datatype);
			$concat2 = new ConcatenateCommand();
			$concat2->setStrings(["Default DataStructure for datatype ", $datatype, ", sub type ", $get_subtype]);
			$log2 = new LogCommand();
			$log2->setMessage($concat2);
			$switch2->cases($cases)->default([
				$log2,
				DeclareVariableCommand::redeclare("struct", new ConstructorCommand("DataStructure", $raw_data, $response)),
				$break
			]);
			$function->pushCodeBlock(
				DeclareVariableCommand::let("print", false), 
				new ForEachLoopCommand(
					DeclareVariableCommand::let("key"), 
					"data", 
					DeclareVariableCommand::let(
						"raw_data", 
						new ArrayAccessCommand(new GetDeclaredVariableCommand("data"), "key")
					),
					new LogCommand($raw_data),
					DeclareVariableCommand::let("struct"), 
					$switch2, 
					new CallFunctionCommand(
						"response.setDataStructure", 
						new GetDeclaredVariableCommand("key"), 
						new GetDeclaredVariableCommand("struct")
					), 
					DeclareVariableCommand::redeclare("struct", null)
				)
			);
			return $function;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}