<?php
namespace JulianSeymour\PHPWebApplicationFramework\template;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\control\ReturnCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\DocumentFragment;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use Exception;

class TemplateElementFunctionGenerator extends AbstractTemplateFunctionGenerator
{

	public function generate($element): ?JavaScriptFunction
	{
		$f = __METHOD__; //TemplateElementFunctionGenerator::getShortClass()."(".static::getShortClass().")::generate()";
		try {
			$print = false;
			if (! $element->isTemplateworthy()) {
				Debug::error("{$f} be sure to mark this class as templateworthy if you're going to generate a template function");
			}
			$element->generateContents();
			$function = new JavaScriptFunction($element->getTemplateFunctionName(), "context");
			$function->setRoutineType(ROUTINE_TYPE_FUNCTION);
			// $function->setTemplateFlag(true);
			$counter = 0;
			$commands = [];
			// $element->generateContents();
			// create document fragment, if applicable
			if ($element->hasPredecessors() || $element->hasSuccessors()) {
				if ($print) {
					Debug::print("{$f} yes, this has predecessor or successor nodes");
				}
				$fragment = $element->setDocumentFragment(new DocumentFragment());
			} elseif ($print) {
				Debug::print("{$f} no, this does not have predecessor or successor nodes");
			}
			// generate commands for this node
			$my_commands = static::getTemplateFunctionCommands($element, null, $counter);
			$counter ++;
			$commands = array_merge($commands, $my_commands);
			// return statement
			if ($element->hasDocumentFragment()) {
				if ($print) {
					Debug::print("{$f} yes, this object has a document fragment");
				}
				if (! isset($fragment)) {
					if ($print) {
						Debug::print("{$f} fragment was no locally defined");
					}
					$fragment = $element->getDocumentFragment();
				} elseif ($print) {
					Debug::print("{$f} fragment was declared locally");
				}
				$fragment_commands = static::getFragmentTemplateFunctionCommands($fragment, null, $counter);
				$counter ++;
				$commands = array_merge($commands, $fragment_commands);
				$return = new ReturnCommand(new GetDeclaredVariableCommand($fragment));
			} else {
				if ($print) {
					Debug::print("{$f} no, this object does not have a document fragment");
				}
				$vname = $element->getIdOverride();
				if (is_string($vname) || $vname instanceof StringifiableInterface) {
					$vname = new GetDeclaredVariableCommand($vname);
				}
				$return = new ReturnCommand($vname);
			}
			array_push($commands, $return);
			// insert commands into function
			if (! empty($commands)) {
				$function->pushSubcommand(...$commands);
			} else {
				Debug::error("{$f} this function should never be without commands at the end");
			}
			return $function;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}