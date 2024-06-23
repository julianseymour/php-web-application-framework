<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\common\PropertiesTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class Event extends Basic{

	use PropertiesTrait;

	protected $listenerId;

	public $defaultPrevented;
	
	protected $eventType;

	protected $target;

	public function __construct(?string $event_type=null, ?array $properties = null){
		parent::__construct();
		$this->defaultPrevented = false;
		$this->disableClaim();
		if($event_type !== null){
			$this->setEventType($event_type);
		}
		if($properties !== null && is_array($properties) && !empty($properties)){
			foreach($properties as $key => $value){
				$this->setProperty($key, $value);
			}
		}
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasListenerId()){
			$this->setListenerId(replicate($that->getListenerId()));
		}
		if($that->hasEventType()){
			$this->setEventType(replicate($that->getEventType()));
		}
		if($that->hasTarget()){
			$this->setTarget($that->getTarget());
		}
		$this->copyProperties($that);
		return $ret;
	}
	
	public function dispose(bool $deallocate=false):void{
		if(!$this->getDisableClaimFlag()){
			if($this->hasTarget()){
				$this->release($this->target);
			}
			$this->release($this->listenerId, $deallocate);
			$this->release($this->eventType, $deallocate);
			if($this->hasProperties()){
				$this->releaseProperties($deallocate);
			}
			$this->release($this->propertyTypes, $deallocate);
		}else{
			if($this->hasTarget()){
				unset($this->target);
			}
			unset($this->listenerId);
			unset($this->eventType);
			unset($this->properties);
			unset($this->propertyTypes);
		}
		parent::dispose($deallocate);
	}
	
	public function setEventTimestamp($value = null){
		if($value === null){
			$value = microtime(true);
		}
		return $this->setProperty("timestamp", $value);
	}

	public function getEventTimestamp(){
		return $this->getProperty("timestamp");
	}

	public function setListenerId($id){
		if($this->getDisableClaimFlag()){
			return $this->listenerId = $id;
		}
		if($this->hasListenerId()){
			$this->release($this->listenerId);
		}
		return $this->listenerId = $this->claim($id);
	}

	public function hasListenerId():bool{
		return isset($this->listenerId);
	}

	public function getListenerId(){
		$f = __METHOD__;
		if(!$this->hasListenerId()){
			Debug::error("{$f} listener ID is undefined");
		}
		return $this->listenerId;
	}

	public function setEventType($type){
		if($this->getDisableClaimFlag()){
			return $this->eventType = $type;
		}
		if($this->hasEventType()){
			$this->release($this->eventType);
		}
		return $this->eventType = $this->claim($type);
	}

	public function hasEventType():bool{
		return !empty($this->eventType);
	}

	public function getEventType(){
		$f = __METHOD__;
		if(!$this->hasEventType()){
			Debug::error("{$f} event type string is undefined");
		}
		return $this->eventType;
	}

	public function setTarget($target){
		if($this->getDisableClaimFlag()){
			return $this->target = $target;
		}
		if($this->hasTarget()){
			$this->release($this->target);
		}
		return $this->target = $this->claim($target);
	}

	public function getTarget(){
		$f = __METHOD__;
		if(!$this->hasTarget()){
			Debug::error("{$f} target is undefined");
		}
		return $this->target;
	}

	public function hasTarget():bool{
		return isset($this->target);
	}
	
	public function preventDefault(bool $value=true){
		if(!$this->getProperties('cancelable')){
			return;
		}
		$this->defaultPrevented = $value;
	}
}
