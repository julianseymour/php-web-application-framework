<?php
namespace JulianSeymour\PHPWebApplicationFramework\template;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\NodeBearingCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\AddClassCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\AppendChildCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\CreateElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\ReidentifyElementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetAttributeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetInnerHTMLCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetStylePropertiesCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\SetInputValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\CreateTextNodeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\CompoundElement;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\GhostElementInterface;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ForAttributeInterface;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ValueAttributeInterface;
use JulianSeymour\PHPWebApplicationFramework\input\InputElement;
use JulianSeymour\PHPWebApplicationFramework\script\DocumentFragment;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunctionGenerator;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\HasColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;

abstract class AbstractTemplateFunctionGenerator extends JavaScriptFunctionGenerator
{

	/**
	 * returns the commands needed to set this node's attributes inside a template function
	 *
	 * @return array
	 */
	protected static function getTemplateFunctionAttributeCommands(Element $element){
		$f = __METHOD__;
		try{
			$print = false;
			$commands = [];
			// local variables
			if($element->hasLocalDeclarations()) {
				foreach($element->getLocalDeclarations() as $ld) {
					array_push($commands, $ld);
				}
			}
			// id
			if($element->hasIdAttribute()) {
				$id = $element->getIdAttribute();
				$reid = new ReidentifyElementCommand($element, $id);
				if(is_string($id) && preg_match(REGEX_TEMPLATE_LITERAL, $id)) {
					if($print) {
						Debug::print("{$f} ID \"{$id}\" is a template literal");
					}
					$reid->setQuoteStyle(QUOTE_STYLE_BACKTICK);
				}elseif($print) {
					Debug::print("{$f} ID is not a template literal");
				}
				array_push($commands, $reid);
			}elseif($print) {
				Debug::print("{$f} element lacks an ID attribute");
			}
			// class
			if($element->hasClassAttribute()) {
				$template_class = false;
				if($element->hasClassList()) {
					foreach($element->getClassList() as $class) {
						if($class instanceof Command) {
							// Debug::print("{$f} at least one item on the class list is a media command");
							$template_class = true;
							break;
						}
					}
				}
				if($template_class) {
					// Debug::print("{$f} about to push AddClassCommands");
					foreach($element->getClassList() as $class) {
						$add_class = new AddClassCommand($element, $class);
						array_push($commands, $add_class);
					}
				}else{
					// Debug::print("{$f} none of the classes are media commands; setting all class names as one attribute");
					$reclass = new SetAttributeCommand($element, [
						"class" => implode(" ", $element->getClassList())
					]);
					array_push($commands, $reclass);
				}
			}elseif($print) {
				Debug::print("{$f} element lacks a class attribute");
			}
			// event listeners
			$keyvalues = $element->getValidOnEventCommands();
			foreach($keyvalues as $attr_str => $cmd_class) {
				if($print) {
					Debug::print("{$f} event handler \"{$attr_str}\"");
				}
				if($element->hasAttribute($attr_str)) {
					if($print){
						Debug::print("{$f} element has a {$attr_str} event");
					}
					$cmd_class = $keyvalues[$attr_str];
					$onclick = $element->getAttribute($attr_str);
					if(is_string($onclick)) {
						if($print){
							Debug::print("{$f} onclick attribute is the function \"{$onclick}\"");
						}
						array_push($commands, new SetAttributeCommand($element, [
							$attr_str => $onclick
						]));
					}elseif($onclick instanceof CallFunctionCommand || $onclick instanceof JavaScriptFunction){
						if($print){
							Debug::print("{$f} onclick attribute is a CallFunctionCommand or JAvaScriptFunction");
						}
						if($onclick instanceof JavaScriptFunction){
							$onclick->setRoutineType(ROUTINE_TYPE_FUNCTION);
						}
						$set_onclick = new $cmd_class($element, $onclick);
						if($onclick instanceof CallFunctionCommand && $onclick->hasEscapeType() && $onclick->getEscapeType() === ESCAPE_TYPE_FUNCTION){
							$set_onclick->setEscapeType($onclick->getEscapeType());
						}
						array_push($commands, $set_onclick);
					}else{
						$gottype = is_object($onclick) ? get_short_class($onclick) : gettype($onclick);
						$decl = is_object($onclick) ? $onclick->getDeclarationLine() : "N/A";
						Debug::error("{$f} neither of the above, event handler is a(n) {$gottype}, instantiated {$decl}");
					}
				}elseif($print){
					Debug::print("{$f} element does not have an {$attr_str} event");
				}
			}
			$skipme = array_merge(array_keys($keyvalues), [
				"class",
				"for",
				"id",
				"name",
				"value",
				"declared",
				"debugid"
			]);
			$attributes_to_set = [];
			// attributes
			if($element->hasAttributes()) {
				foreach(array_keys($element->getAttributes()) as $attr_key) {
					if(false !== array_search($attr_key, $skipme)) {
						continue;
					}
					$attributes_to_set[$attr_key] = $element->getAttribute($attr_key);
				}
			}
			if(!empty($attributes_to_set)) {
				$command = new SetAttributeCommand($element, $attributes_to_set);
				array_push($commands, $command);
			}
			// style properties
			if($element->hasInlineStyleAttribute()) {
				array_push($commands, new SetStylePropertiesCommand($element, $element->getStyleProperties())); // properties));
			}
			// responsive style properties
			if($element->hasResponsiveStyleProperties()) {
				$properties = [];
				foreach(array_keys($element->getResponsiveStyleProperties()) as $key) {
					$properties[$key] = $element->getResponsiveStyleProperty($key);
				}
				array_push($commands, new SetStylePropertiesCommand($element, $properties));
			}
			// special attributes that are better set with the javascript object property
			if($element instanceof InputElement) {
				// name attribute
				if($element->hasNameAttribute()) {
					$name = $element->getNameAttribute();
					$rename = $element->setNameAttributeCommand($name);
					if(is_string($name) && preg_match(REGEX_TEMPLATE_LITERAL, $name)) {
						$rename->setQuoteStyle(QUOTE_STYLE_BACKTICK);
					}
					array_push($commands, $rename);
				}elseif($print) {
					Debug::print("{$f} element lacks a name attribute");
				}
				// value
				if($element instanceof ValueAttributeInterface) {
					if($element->hasValueAttribute()) {
						$value = $element->getValueAttribute();
						$revalue = new SetInputValueCommand($element, $value);
						if($value instanceof GetColumnValueCommand){
							$get = new GetDeclaredVariableCommand("context");
							$command = CommandBuilder::if(
								new AndCommand(
									$get,
									new HasColumnValueCommand(
										$get, 
										$value->getColumnName()
									)
								)
							)->then($revalue);
						}else{
							$command = $revalue;
						}
						array_push($commands, $command);
					}elseif($print) {
						Debug::print("{$f} element lacks a value attribute");
					}
				}elseif($print) {
					Debug::print("{$f} element cannot possibly have a value attribute");
				}
			}elseif($element instanceof ForAttributeInterface) {
				if($element->hasForAttribute()) {
					$refor = $element->setForAttributeCommand($element->getForAttribute());
					array_push($commands, $refor);
				}
			}
			// array_push($commands, CommandBuilder::log("{$f} returning normally"));
			return $commands;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * Part of generateTemplateFunction().
	 * Returns Commands to generate a child node clientside
	 *
	 * @param Element|Command $child
	 *        	: the child node to generate
	 * @param int $counter
	 *        	: element counter for automatically generating variable names
	 * @return array|array|NodeBearingCommandInterface[]|ServerExecutableCommandInterface[]|AppendChildCommand[]
	 */
	protected static function getChildTemplateFunctionCommands(Element $element, $child, &$counter){
		$f = __METHOD__;
		$print = $element->getDebugFlag();
		$print = false;
		$child_class = $child->getClass();
		if($child instanceof CompoundElement) {
			if($print) {
				Debug::print("{$f} child is a compound element of class \"{$child_class}\"");
			}
			$commands = [];
			foreach($child->getComponents() as $component) {
				$commands = array_merge($commands, static::getChildTemplateFunctionCommands($element, $component, $counter));
			}
			return $commands;
		}elseif($child instanceof Element) {
			if($print) {
				Debug::print("{$f} child is an element of class \"{$child_class}\"");
			}
			$vname = $element->getIdOverride();
			$child_commands = static::getTemplateFunctionCommands($child, $vname, $counter);
			$counter ++;
			return $child_commands;
		}elseif($child instanceof NodeBearingCommandInterface) {
			if($print) {
				Debug::print("{$f} child is a node-bearing command of class \"{$child_class}\"");
			}
			$child->incrementVariableName($counter);
			return [
				$child
			];
		}elseif($child instanceof ServerExecutableCommandInterface) {
			if($print) {
				Debug::print("{$f} child is a server-executable command of class \"{$child_class}\"");
			}
			return [
				$child
			];
		}elseif($child instanceof ValueReturningCommandInterface) {
			if($print) {
				Debug::print("{$f} child is a value-returning media command of class \"{$child_class}\"");
			}
			// array_push($commands,
			return [
				new AppendChildCommand($element, $child)
			]; // );
		}
		Debug::warning("{$f} child is an object of class \"{$child_class}\"");
		$element->debugPrintRootElement();
	}

	protected static function getFragmentTemplateFunctionCommands(DocumentFragment $fragment, $parent_name, &$counter){
		$f = __METHOD__;
		try{
			// $counter++;
			if(!$fragment->hasIdOverride()) {
				$fragment->setIdOverride("fragment{$counter}");
			}
			$commands = [
				DeclareVariableCommand::let($fragment, $fragment)
			];
			$reserved = [];
			foreach($fragment->getChildNodes() as $child) {
				$ido = $child->getIdOverride();
				if(array_key_exists($ido, $reserved)) {
					Debug::error("{$f} ID override \"{$ido}\" has already been used");
				}
				$reserved[$ido] = $child;
				array_push($commands, $fragment->appendChildCommand($child));
			}
			return $commands;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * Returns a list of commands needed to generate this element in a client side JS function.
	 * Called both by the element generating a template function and recursively by its child nodes.
	 *
	 * @param string $parent_name
	 *        	: variable name of parent node to append this element to
	 * @param int $counter
	 *        	: counts the number of nodes for automatically generating variable names
	 * @return array
	 */
	protected static function getTemplateFunctionCommands(Element $element, $parent_name, &$counter){
		$f = __METHOD__;
		try{
			$print = false;
			if($print) {
				$ec = $element->getClass();
				$did = $element->getDebugId();
				Debug::print("{$f} generating template function commands for element of class \"{$ec}\" with debug ID \"{$did}\"");
			}
			if($element instanceof DocumentFragment) {
				Debug::error("{$f} DocumentFragment has a separate function for dealing with this");
			}elseif($element instanceof GhostElementInterface) {
				return null;
			}
			if(!$element->getContentsGeneratedFlag()) {
				$element->generateContents(); // Debug::error("{$f} don't call on unfinalized objects");
			}
			$commands = [];
			// set template flag if not already set
			if(!$element->getTemplateFlag()) {
				$element->setTemplateFlag(true);
			}
			// deal with self-generated predecessors
			$fragment = null;
			if(
			// ($element->hasPredecessors() || $element->hasSuccessors()) &&
			$element->hasDocumentFragment()) {
				$fragment = $element->getDocumentFragment();
			}
			if($element->hasPredecessors()) {
				if($print) {
					Debug::print("{$f} yes, this element has predecessor nodes");
				}
				$predecessors = $element->getPredecessors();
				foreach($predecessors as $p) {
					if(is_object($p)) {
						$p_class = $p->getClass();
						if($p instanceof Element) {
							$pc = static::getTemplateFunctionCommands($p, $parent_name, $counter);
							$commands = array_merge($commands, $pc);
						}elseif($p instanceof NodeBearingCommandInterface) {
							if($print) {
								Debug::print("{$f} command is node-bearing of class \"{$p_class}\"");
							}
							$p->incrementVariableName($counter);
							array_push($commands, $p);
						}elseif($p instanceof ServerExecutableCommandInterface) {
							if($print) {
								Debug::print("{$f} command is a server-executable command of class \"{$p_class}\"");
							}
							array_push($commands, $p);
						}else{
							Debug::error("{$f} predecessor is an object of class \"{$p_class}\"");
						}
					}
					$counter ++;
					if($fragment instanceof DocumentFragment) {
						if($print) {
							$decl = $fragment->getDeclarationLine();
							Debug::print("{$f} document fragment was instantiated {$decl}");
						}
						$fragment->appendChild($p);
					}
				}
			}elseif($print) {
				Debug::print("{$f} no, this element does not have predecessor nodes");
			}
			// declare element
			$vname = $element->incrementVariableName($counter);
			if($print) {
				Debug::print("{$f} element variable name is \"{$vname}\"");
			}
			$declare = DeclareVariableCommand::let($element->getIdOverride(), // $element,
			new CreateElementCommand($element->getElementTag()));
			array_push($commands, $declare);
			// attributes
			$attribute_commands = static::getTemplateFunctionAttributeCommands($element);
			if(!empty($attribute_commands)) {
				$commands = array_merge($commands, $attribute_commands);
			}
			// innerHTML/child nodes
			if($element->hasInnerHTML()) {
				$rehtml = new SetInnerHTMLCommand($element, $element->getInnerHTML());
				array_push($commands, $rehtml);
			}elseif($element->hasChildNodes()) {
				$children = $element->getChildNodes();
				foreach($children as $child) {
					if(is_object($child)) {
						$child_commands = static::getChildTemplateFunctionCommands($element, $child, $counter);
						if(!empty($child_commands)) {
							$commands = array_merge($commands, $child_commands);
						}
					}elseif(is_string($child)) {
						$text_command = new AppendChildCommand($element, new CreateTextNodeCommand($child));
						array_push($commands, $text_command);
					}else{
						$typeof = gettype($child);
						Debug::error("{$f} child object is a \"{$typeof}\"");
					}
				}
			}
			// dispatched media commands that normally get reported to parent node/use case
			$element->setCatchReportedSubcommandsFlag(true);
			$element->dispatchCommands();
			if($element->hasReportedSubcommands()) {
				$reported = $element->getReportedSubcommands();
				if(!is_array($reported) || empty($reported)) {
					Debug::error("{$f} reported subcommands array is empty");
				}
				foreach($reported as $rc) {
					if($print) {
						$rcc = $rc->getClass();
						Debug::print("{$f} pushing a reported subcommand of class \"{$rcc}\"");
					}
					array_push($commands, $rc);
				}
			}
			// append this object to fragment if it exists
			if($fragment instanceof DocumentFragment) {
				$fragment->appendChild($element);
			}
			// append to parent node if it exists
			if(isset($parent_name)) {
				array_push($commands, new AppendChildCommand($parent_name, $element));
			}
			// deal with self-generated successors
			if($element->hasSuccessors()) {
				if($print) {
					Debug::print("{$f} yes, this element has successor nodes");
				}
				$successors = $element->getSuccessors();
				foreach($successors as $successor) {
					if($successor instanceof Element) {
						$successor_commands = static::getTemplateFunctionCommands($successor, $parent_name, $counter);
						$commands = array_merge($commands, $successor_commands);
					}elseif($successor instanceof NodeBearingCommandInterface) {
						$successor->incrementVariableName($counter);
						array_push($commands, $successor);
					}elseif($successor instanceof ServerExecutableCommandInterface) {
						array_push($commands, $successor);
					}else{
						Debug::error("{$f} successor is neither a media command nor element");
					}
					$counter ++;
					if($fragment instanceof DocumentFragment) {
						$fragment->appendChild($successor);
					}
				}
			}
			return $commands;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}