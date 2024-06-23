<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\mutual_reference;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\IncrementVariableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\command\NodeBearingCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\SetManagedPropertyEvent;
use JulianSeymour\PHPWebApplicationFramework\json\Json;

abstract class InsertElementCommand extends MultipleElementCommand implements AllocationModeInterface, NodeBearingCommandInterface{

	use AllocationModeTrait;
	use ReferenceElementIdTrait;
	
	protected $onDuplicateId;

	public abstract static function getInsertWhere();

	public function __construct($insert_here=null, ...$inserted_elements){
		$f = __METHOD__;
		$print = false;
		parent::__construct();
		$closure = function(SetManagedPropertyEvent $event, InsertElementCommand $target) 
		use ($f, $print){
			$print = $target->getDebugFlag();
			if($event->getProperty('key') === 'elements'){
				foreach($event->getProperty('value') as $key => $element){
					if($element instanceof Element){
						if($print){
							Debug::print("{$f} inside outer closure");
						}
						if(!$element->getCatchReportedSubcommandsFlag()){
							$element->setCatchReportedSubcommandsFlag(true);
						}
						if(!$element->hasSubcommandCollector()){
							$element->setSubcommandCollector($this);
							if(BACKWARDS_REFERENCES_ENABLED){
								//setting the subcommand collector makes both objects reference each other in a way that prevents deallocation from occuring
								$closure1 = function(InsertElementCommand $command, bool $deallocate=false)
								use ($key, $f, $print){
									if($print){
										Debug::print("{$f} inside inner closure for releasing elements from InsertElementCommand");
									}
									if($command->hasArrayPropertyKey('elements', $key)){
										$command->releaseArrayPropertyKey('elements', $key, $deallocate);
									}
								};
								$closure2 = function(Element $element, bool $deallocate=false) use ($f, $print){
									if($print){
										Debug::print("{$f} inside the closure for releasing subcommand collector from element");
									}
									if($element->hasSubcommandCollector()){
										$element->releaseSubcommandCollector(false);
									}
								};
								mutual_reference($this, $element, $closure1, $closure2, EVENT_RELEASE_PROPERTY_KEY, EVENT_RELEASE_SUBCOMMAND_COLLECTOR, [
									'propertyName' => "elements",
									'key' => $key
								]);
							}
						}
					}
				}
			}
		};
		$this->addEventListener(EVENT_SET_PROPERTY, $closure);
		if(isset($inserted_elements) && count($inserted_elements) > 0){
			$this->setElements($inserted_elements);
		}
		if($insert_here !== null){
			$this->insertHere($insert_here);
		}
	}
	
	public function insertHere($insert_here){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(is_string($insert_here)){
			if($print){
				Debug::print("{$f} insertion target is the string \"{$insert_here}\"");
			}
			$this->setReferenceElementId($insert_here);
		}elseif(is_object($insert_here)){
			if($insert_here instanceof Element){
				if($print){
					Debug::print("{$f} insertion target is an element");
				}
				if($insert_here->hasIdOverride()){
					if($print){
						Debug::print("{$f} insertion target has an ID override");
					}
					$id = $insert_here->getIdOverride();
					$this->setReferenceElementId($id);
				}elseif($print){
					$where = $insert_here->getDeclarationLine();
					Debug::print("{$f} insertion target does not have an ID attribute or override; declared {$where}. This is a ".$this->getDebugString());
				}
				if($insert_here->hasIdAttribute()){
					if($print){
						Debug::print("{$f} insertion target has an ID attribute");
					}
					$id = $insert_here->getIdAttribute();
					$this->setReferenceElementId($id);
				}
			}elseif($insert_here instanceof ValueReturningCommandInterface){
				$this->setElement($insert_here);
			}elseif($print){
				Debug::error("{$f} insertion target is an object, but not an element or command");
			}
		}else{
			Debug::error("{$f} invalid insertion target");
		}
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasAllocationMode()){
			$this->setAllocationMode(replicate($that->getAllocationMode()));
		}
		if($that->hasOnDuplicateIdCommand()){
			$this->setOnDuplicateIdCommand(replicate($that->getOnDuplicateIdCommand()));
		}
		if($that->hasReferenceElementId()){
			$this->setReferenceElementId(replicate($that->getReferenceElementId()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->allocationMode, $deallocate);
		$this->release($this->onDuplicateId, $deallocate);
		$this->release($this->referenceElementId, $deallocate);
	}

	public function hasOnDuplicateIdCommand():bool{
		return isset($this->onDuplicateId);
	}

	public function setOnDuplicateIdCommand($command){
		if($this->hasOnDuplicateIdCommand()){
			$this->release($this->onDuplicateId);
		}
		return $this->onDuplicateId = $this->claim($command);
	}

	public function getOnDuplicateIdCommand(){
		$f = __METHOD__;
		if(!$this->hasOnDuplicateIdCommand()){
			Debug::error("{$f} on duplicate ID media command is undefined");
		}
		return $this->onDuplicateId;
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		Json::echoKeyValuePair('elements', $this->getElements(), $destroy);
		Json::echoKeyValuePair('insert_here', $this->getReferenceElementId(), $destroy);
		Json::echoKeyValuePair('where', $this->getInsertWhere(), $destroy);
		if($this->hasOnDuplicateIdCommand()){
			Json::echoKeyValuePair('onDuplicateId', $this->getOnDuplicateIdCommand(), $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public static final function getCommandId(): string{
		return "insert";
	}

	public function extractChildNodes(int $mode): ?array{
		return $this->getElements();
	}

	public static function extractAnyway():bool{
		return false;
	}

	public function resolve(){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function evaluate(?array $params = null){
		return $this->resolve();
	}

	public function incrementVariableName(int &$counter){
		$f = __METHOD__;
		$print = false;
		foreach($this->getElements() as $e){
			if($e instanceof IncrementVariableNameInterface){
				$e->incrementVariableName($counter);
			}elseif($print){
				$gottype = is_object($e) ? $e->getClass() : gettype($e);
				Debug::print("{$f} element is a \"{$gottype}\"");
			}
		}
	}
}
