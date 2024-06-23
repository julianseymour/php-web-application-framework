<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\NodeBearingCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\HitPointsInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\event\DeallocateEvent;

/**
 * This class only exists to overridde override dynamic button generation with a template-compatible
 * method.
 * For example, the StateUpdateForm, or any form that changes insert/update/delete depending on context
 */
abstract class GenerateFormButtonsCommand extends ElementCommand 
implements AllocationModeInterface, NodeBearingCommandInterface{

	use AllocationModeTrait;

	public function extractChildNodes(int $mode): ?array{
		$f = __METHOD__;
		$form = $this->getElement();
		$names = $form->getDirectives();
		$buttons = [];
		if(!empty($names)){
			foreach($names as $name){
				$generated = $form->generateButtons($name);
				if(!is_array($generated)){
					Debug::error("{$f} not an array");
				}
				$buttons = array_merge($buttons, $generated);
			}
		}
		return $buttons;
	}

	public function evaluate(?array $params = null){
		return $this->extractChildNodes($this->getAllocationMode());
	}

	public function incrementVariableName(int &$counter){
		return null;
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasAllocationMode()){
			$this->setAllocationMode(replicate($that->getAllocationMode()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->allocationMode, $deallocate);
	}
	
	public function setElement($element){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(is_string($element)){
			if($print){
				Debug::print("{$f} element is a string");
			}
			$this->setId($element);
		}elseif($element instanceof Element){
			if($element->hasIdAttribute()){
				$this->setId($element->getIdAttribute());
			}elseif($element->hasAttribute("temp_id")){
				$this->setId($element->getAttribute("temp_id"));
			}
			if($print){
				Debug::print("{$f} element class is " . $element->getClass());
			}
		}elseif($print){
			$gottype = gettype($element);
			Debug::print("{$f} setting a \"{$gottype}\" as an element");
		}
		return $this->element = $element;
	}
	
	public function releaseElement(bool $deallocate=false):void{
		$f = __METHOD__;
		if(!$this->hasElement()){
			Debug::error("{$f} element is undefined");
		}
		unset($this->element);
	}
}
