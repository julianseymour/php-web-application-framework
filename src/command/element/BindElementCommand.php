<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\IncrementVariableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\command\NodeBearingCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\control\IfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\NodeBearingIfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructuralTrait;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class BindElementCommand extends ElementCommand implements AllocationModeInterface, IncrementVariableNameInterface, ValueReturningCommandInterface{

	use DataStructuralTrait;
	use AllocationModeTrait;

	public static function getCommandId(): string{
		return "bind";
	}

	public function __construct($element, $ds){
		parent::__construct($element);
		if(isset($ds)) {
			$this->setDataStructure($ds);
		}
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		try{
			$element = $this->getElement();
			if(is_string($element)) {
				$binder = $element::getTemplateFunctionName();
			}elseif($element instanceof Element) {
				$binder = $element->getTemplateFunctionName();
			}else{
				Debug::error("{$f} none of the above");
			}
			$ds = $this->getDataStructure();
			if($ds instanceof DataStructure) {
				// Debug::error("{$f} somehow, context is a datastructure");
				$ds = "context";
			}elseif($ds instanceof JavaScriptInterface) {
				$ds = $ds->toJavaScript();
			}
			return "{$binder}({$ds})";
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function hasIdAttribute(): bool{
		return $this->hasId();
	}

	public function hasIdOverride(){
		return $this->hasId();
	}

	public static function bindSubordinateElement($mode, $ds, $class, $index){
		return new BindElementCommand(new $class($mode), new GetForeignDataStructureCommand($ds, $index));
	}

	public static function appendSubordinateElement($mode, $ds, $class, $index, $parent_node){
		return $parent_node->appendChildCommand(static::bindSubordinateElement($mode, $ds, $class, $index));
	}

	/**
	 * return a conditional media command that can be evaluated server side or converted to
	 * generative javascript
	 * appends a child node of class $class bound to $ds's foreign data structure at index $index
	 * to parent node $parent_node iff the context has a foreign data structure at index $index
	 * otherwise does nothing
	 *
	 * @param int $mode
	 * @param DataStructure $ds
	 * @param string $class
	 * @param string $index
	 * @param Element $parent_node
	 * @return IfCommand
	 */
	public static function conditionalSubordinateElement($mode, $ds, $class, $index, $parent_node){
		return NodeBearingIfCommand::if($ds->hasColumnValueCommand($index))->then(static::appendSubordinateElement($mode, $ds, $class, $index, $parent_node));
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		try{
			$element = $this->getElement();
			$ds = $this->getDataStructure();
			while ($ds instanceof ValueReturningCommandInterface) {
				$ds = $ds->evaluate();
			}
			if($element instanceof NodeBearingCommandInterface) {
				$ec = $element->getClass();
				Debug::error("{$f} this command's element is a node-bearing media command of class \"{$ec}\"");
				// return $element->extractChildNodes();
			}elseif($element instanceof Element) {
				$ec = $element->getClass();
				// Debug::print("{$f} element is an element of class \"{$ec}\"");
				if($element->getTemplateLoopFlag()) {
					// Debug::print("{$f} element of class \"{$ec}\" has template loop flag set");
					$mode = $element->getAllocationMode();
					$element = new $ec($mode);
				}else{
					// Debug::print("{$f} element of class \"{$ec}\" does NOT have template loop flag set");
				}
				if(!$element->hasContext()) {
					$element->bindContext($ds);
				}
				return $element;
			}elseif(is_string($element) && class_exists($element)) {
				$ec = $element;
				// Debug::print("{$f} element is the class name \"{$ec}\"");
				$ret = new $ec($this->getAllocationMode());
				$ret->bindContext($ds);
				return $ret;
			}
			Debug::error("{$f} none of the above");
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function incrementVariableName(int &$counter){
		return $this->getElement()->incrementVariableName($counter);
	}

	public function extractChildNodes(int $mode): ?array{
		$this->setAllocationMode($mode);
		return [
			$this->evaluate()
		];
	}

	public static function extractAnyway(){
		return false;
	}
}
