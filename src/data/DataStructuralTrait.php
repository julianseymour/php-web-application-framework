<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\event\DeallocateEvent;
use JulianSeymour\PHPWebApplicationFramework\common\HitPointsInterface;

trait DataStructuralTrait{

	/**
	 * the DataStructure that hosts this datum as one of its columns
	 *
	 * @var DataStructure
	 */
	protected $dataStructure;

	/**
	 *
	 * @param DataStructure $obj
	 * @return DataStructure
	 */
	public function setDataStructure($obj){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($this->hasDataStructure()){
			if($print){
				Debug::print("{$f} data structure was already assigned, releasing it now");
			}
			$this->releaseDataStructure();
		}elseif($print){
			Debug::print("{$f} assigning data structure for the first time");
		}
		if($obj instanceof HitPointsInterface){
			if($print){
				Debug::print("{$f} data structure is a HitPointsInterface");
			}
			$this->addDataStructureDeallocateListener($obj);
		}elseif($print){
			Debug::print("{$f} received something that is not a HitPointsInterface");
		}
		return $this->dataStructure = $this->claim($obj);
	}

	protected function addDataStructureDeallocateListener(HitPointsInterface $ds){
		$f = __METHOD__;
		$print = false;
		$that = $this;
		$closure = function(DeallocateEvent $event, HitPointsInterface $target) use ($that, $f, $print){
			if($print){
				Debug::print("{$f} inside the closure for relasing data structure before it is deallocated");
			}
			$target->removeEventListener($event);
			if($that->hasDataStructure()){
				if($print){
					Debug::print("{$f} about to release data structure");
				}
				$that->releaseDataStructure(false);
			}elseif($print){
				Debug::print("{$f} data structure is undefined");
			}
		};
		$ds->addEventListener(EVENT_DEALLOCATE, $closure);
	}
	
	public function releaseDataStructure(bool $deallocate=false){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(!$this->hasDataStructure()){
			Debug::error("{$f} data structure is undefined for this ".$this->getDebugString());
		}elseif($print){
			Debug::print("{$f} about to release data structure for this ".$this->getDebugString().". Data structure is ".$this->dataStructure->getDebugString());
		}
		$ds = $this->dataStructure;
		unset($this->dataStructure);
		$this->release($ds, $deallocate);
	}
	
	/**
	 *
	 * @return DataStructure
	 */
	public function getDataStructure(){
		$f = __METHOD__;
		if(!$this->hasDataStructure()){
			if($this instanceof Datum){
				$column_name = $this->getName();
				Debug::error("{$f} data structure is undefined for column \"{$column_name}\"");
			}
			Debug::error("{$f} data structure is undefined");
		}
		return $this->dataStructure;
	}

	public function hasDataStructure():bool{
		return isset($this->dataStructure);
	}
}
