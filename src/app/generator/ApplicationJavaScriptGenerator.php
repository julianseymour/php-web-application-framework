<?php

namespace JulianSeymour\PHPWebApplicationFramework\app\generator;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\ModuleBundler;
use JulianSeymour\PHPWebApplicationFramework\command\control\ReturnCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\arr\ArrayAccessCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptClass;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use Exception;

abstract class ApplicationJavaScriptGenerator extends Basic{

	public static function generateClassReturningFunction():JavaScriptFunction{
		$f = __METHOD__;
		$print = false;
		$function = new JavaScriptFunction("getApplicationClass");
		$function->setRoutineType(ROUTINE_TYPE_FUNCTION);
		$short = get_short_class(config());
		if(str_contains($short, '\\')){
			Debug::error("{$f} shortname contains \\");
		}elseif($print){
			Debug::print("{$f} shortname is \"{$short}\"");
		}
		$declaration = DeclareVariableCommand::let("arr", [
				$short => new GetDeclaredVariableCommand($short)
			]
		);
		$declaration->setEscapeType(ESCAPE_TYPE_OBJECT);
		$function->pushCodeBlock(
			$declaration, 
			new ReturnCommand(
				new ArrayAccessCommand(
					new GetDeclaredVariableCommand("arr"), 
					'APPLICATION_CONFIG_CLASS_NAME'
				) // removing the quotes prevents the js function from working correctly
			)
		);
		return $function;
	}

	public static function generateJavaScriptClass(?ModuleBundler $bundler):JavaScriptClass{
		$f = __METHOD__;
		try{
			$print = false;
			if($bundler === null){
				$bundler = mods();
			}
			$short = get_short_class(config());
			if(str_contains($short, '\\')){
				Debug::error("{$f} shortname contains \\");
			}elseif($print){
				Debug::print("{$f} shortname is \"{$short}\"");
			}
			$class = new JavaScriptClass($short);
			$functions = [];
			foreach($bundler->getJavaScriptFunctionGeneratorClasses() as $gen_class){
				$generator = new $gen_class();
				$function = $generator->generate($bundler);
				deallocate($generator);
				if($function instanceof JavaScriptFunction){
					array_push($functions, $function);
				}elseif($print){
					Debug::print("{$f} JS function generator \"{$gen_class}\" did not return a function");
				}
			}
			if(!empty($functions)){
				$class->appendChild(...$functions);
			}elseif($print){
				Debug::print("{$f} no functions generated");
			}
			return $class;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}