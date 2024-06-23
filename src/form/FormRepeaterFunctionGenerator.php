<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\NullCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\IfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\ReturnCommand;
use JulianSeymour\PHPWebApplicationFramework\command\debug\ErrorCommand;
use JulianSeymour\PHPWebApplicationFramework\command\debug\LogCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\GetAttributeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\InsertBeforeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetAttributeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetTypeOfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ParseIntegerCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\template\AbstractTemplateFunctionGenerator;
use Exception;

class FormRepeaterFunctionGenerator extends AbstractTemplateFunctionGenerator{

	use FormClassTrait;

	public function __construct(?string $formClass=null){
		parent::__construct();
		if(!empty($formClass)){
			$this->setFormClass($formClass);
		}
	}

	public function generate($element): ?JavaScriptFunction{
		$f = __METHOD__;
		try{
			$form_class = get_short_class($this->getFormClass());
			$function = new JavaScriptFunction("repeat{$form_class}", "event", "button");
			$function->setRoutineType(ROUTINE_TYPE_FUNCTION);
			$declare_iterator = DeclareVariableCommand::let("iterator", new ParseIntegerCommand(new GetAttributeCommand("button", "iterator")));
			$get_iterator = new GetDeclaredVariableCommand("iterator");
			$function->pushCodeBlock(
				CommandBuilder::let("context", new NullCommand()), 
				$declare_iterator, 
				new LogCommand(
					new ConcatenateCommand(
						"repeat{$form_class}(): iterator is \"", $get_iterator, "\""
					)
				), 
				IfCommand::if(
					CommandBuilder::isInteger($get_iterator)->negate()
				)->then(
					new ErrorCommand(
						new ConcatenateCommand(
							"repeat{$form_class}(): iterator is a ", 
							new GetTypeOfCommand($get_iterator)
						)
					), 
					new ReturnCommand()
				)
			);
			$counter = 0;
			$commands = static::getTemplateFunctionCommands($element, null, $counter);
			$function->pushCodeBlock(...$commands);
			$function->pushCodeBlock(
				new SetAttributeCommand(
					"button", [
						"iterator" => new BinaryExpressionCommand($get_iterator, OPERATOR_PLUS, 1)
					]
				), 
				new InsertBeforeCommand(new GetDeclaredVariableCommand("button"), $element)
			);
			return $function;
		}catch(Exception $x){
			return x($f, $x);
		}
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->formClass, $deallocate);
	}
}
