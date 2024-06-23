<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\get_file_line;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\event\AddEventListenerCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

trait EventListeningTrait{

	protected $eventListeners;

	protected $eventLog;
	
	protected $removedEventListeners;
	
	protected $suppressedEventTypes;
	
	public function suppressEventType(...$types){
		$f = __METHOD__;
		if(!isset($types) || count($types) === 0){
			Debug::error("{$f} received empty parameter");
		}
		if(!isset($this->suppressedEventTypes) || !is_array($this->suppressedEventTypes)){
			$this->suppressedEventTypes = [];
		}
		foreach($types as $type){
			if(array_key_exists($type, $this->suppressedEventTypes)){
				$this->suppressedEventTypes[$type]++;
			}else{
				$this->suppressedEventTypes[$type] = 1;
			}
		}
	}
	
	public function allowEventType(...$types){
		$f = __METHOD__;
		if(!isset($types) || count($types) === 0){
			Debug::error("{$f} received empty parameter");
		}
		foreach($types as $type){
			if(!array_key_exists($type, $this->suppressedEventTypes)){
				Debug::error("{$f} we are not supressing events of type {$type}");
			}
		}
		if($this->suppressedEventTypes[$type] === 1){
			unset($this->suppressedEventTypes[$type]);
		}else{
			$this->suppressedEventTypes[$type]--;
		}
	}
	
	public function addEventListener(string $event_str, $closure, ?string $index = null){
		$f = __METHOD__;
		try{
			$print = false;
			if(!is_string($event_str)){
				Debug::error("{$f} event type must be a string");
			}elseif(!isset($this->eventListeners) || !is_array($this->eventListeners)){
				$this->eventListeners = [];
			}
			if(!array_key_exists($event_str, $this->eventListeners)){
				$this->eventListeners[$event_str] = [];
			}
			if($index == null){
				array_push($this->eventListeners[$event_str], $closure);
				return array_search($closure, $this->eventListeners[$event_str]);
			}
			$this->eventListeners[$event_str][$index] = $closure;
			if($print){
				$did = $this->getDebugId();
				Debug::print("{$f} assigned an event listener for \"{$event_str}\" to index \"{$index}\" for object with debug ID \"{$did}\"");
			}
			return $index;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function addEventListenerCommand(string $type, $listener):AddEventListenerCommand{
		return new AddEventListenerCommand($this, $type, $listener);
	}

	public function hasSuppressedEventType(string $type):bool{
		return isset($this->suppressedEventTypes) && is_array($this->suppressedEventTypes) && array_key_exists($type, $this->suppressedEventTypes);
	}
	
	protected function logEvent(...$s){
		if(!isset($this->eventLog) || !is_array($this->eventLog)){
			$this->eventLog = [];
		}
		return array_push($this->eventLog, ...$s);
	}
	
	public function hasEventLog():bool{
		return isset($this->eventLog) && is_array($this->eventLog) && !empty($this->eventLog);
	}
	
	public function debugPrintEventLog():void{
		$f = __METHOD__;
		if(!app()->getFlag("debug")){
			Debug::print("{$f} ApplicationRuntime does not have debug flag set");
			return;
		}
		Debug::print("Event log for ".$this->getDebugString());
		foreach($this->eventLog as $num => $s){
			Debug::print("#{$num}: {$s}");
		}
	}
	
	public function dispatchEvent($event, ...$params){
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if($print){
				//Debug::printStackTraceNoExit("{$f} entered for ".$this->getDebugString());
			}
			if(!$this->getAllocatedFlag()){
				Debug::error("{$f} don't call events on deallocated objects. This is a ".$this->getDebugString());
			}elseif(is_string($event)){
				$event_str = $event;
			}elseif($event instanceof Event){
				$event->setEventTimestamp(microtime(true));
				$event_str = $event->getEventType();
			}else{
				Debug::error("{$f} event type is neither string nor Event");
			}
			if(!$this->hasAnyEventListener($event_str)){
				if($print){
					Debug::print("{$f} no event listeners assigned to event type \"{$event_str}\". Event is ".$event->getDebugString());
				}
				if(defined("DISABLE_UNLISTENED_EVENTS") && DISABLE_UNLISTENED_EVENTS === true){
					Debug::error("{$f} events without listeners are disabled to reduce the amount of objects being instantiated");
				}
				deallocate($event);
				return false;
			}elseif($this->hasSuppressedEventType($event_str)){
				if($print){
					Debug::print("{$f} events of type \"{$event_str}\" are being suppressed for this ".$this->getDebugString());
				}
				deallocate($event);
				return;
			}elseif($print){
				Debug::print("{$f} about to fire a {$event_str} event on this ".$this->getDebugString());
			}
			$pa = [];
			if(isset($params)){
				foreach($params as $p){
					array_push($pa, $p);
				}
			}
			foreach($this->eventListeners[$event_str] as $listener_id => $closure){
				if(!$this->hasEventListener($event_str, $listener_id)){
					if($print){
						Debug::print("{$f} we no longer have a {$event_str} listener with index {$listener_id}, it must have been removed mid-loop");
					}
					continue;
				}elseif($print){
					Debug::print("{$f} {$event_str} listener with ID {$listener_id}");
				}
				if(is_string($event)){
					if(app()->getFlag("debug")){
						$this->logEvent("{$event_str} event {$listener_id} without object");
					}
					$closure($this, ...$pa);
				}elseif($event instanceof Event){
					if(!$event->defaultPrevented){
						if($print){
							Debug::print("{$f} about to dispatch event with listener ID {$listener_id}");
						}
						$event->setListenerId($listener_id);
						if($print){
							Debug::print("{$f} about to dispatch {$event_str} event with listener ID {$listener_id}");
						}
						if(app()->getFlag("debug")){
							$this->logEvent("{$event_str} event {$listener_id} instantiated ".$event->getDebugString());
						}
						$closure($event, $event->setTarget($this), ...$pa);
						$event->setListenerId(null);
					}elseif($print){
						Debug::print("{$f} default prevented");
					}
				}else{
					$gottype = is_object($event) ? get_short_class($event) : gettype($event);
					Debug::error("{$f} event is a {$gottype}");
				}
			}
			if($print){
				Debug::print("{$f} finished executing all closures assigned to event type \"{$event_str}\"");
			}
			deallocate($event);
			return true;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasAnyEventListener(string $event_str):bool{
		if(!isset($this->eventListeners) || !is_array($this->eventListeners) || empty($this->eventListeners)){
			return false;
		}elseif(!array_key_exists($event_str, $this->eventListeners)){
			return false;
		}elseif(
			!isset($this->eventListeners[$event_str]) || 
			!is_array($this->eventListeners[$event_str]) || 
			empty($this->eventListeners[$event_str])
		){
			return false;
		}
		return true;
	}
	
	public function hasEventListener(string $event_str, string $index):bool{
		$f = __METHOD__;
		try{
			if(!$this->hasAnyEventListener($event_str)){
				return false;
			}elseif(!array_key_exists($index, $this->eventListeners[$event_str])){
				return false;
			}
			return true;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasRemovedEventListener($event_str, $index):bool{
		return isset($this->removedEventListeners) && 
		is_array($this->removedEventListeners) && 
		array_key_exists($event_str, $this->removedEventListeners) && 
		is_array($this->removedEventListeners[$event_str]) && 
		array_key_exists($index, $this->removedEventListeners[$event_str]);
	}
	
	public function removeEventListener($event_str, ?string $index = null, ?string $claimant = null){
		$f = __METHOD__;
		try{
			$print = false;
			if(!isset($this->eventListeners) || !is_array($this->eventListeners)){
				Debug::error("{$f} eventListener array is not an array for this ".$this->getDebugString());
			}elseif($index === null && $event_str instanceof Event){
				return $this->removeEventListener($event_str->getEventType(), $event_str->getListenerId(), $claimant);
			}elseif(!$this->hasEventListener($event_str, $index)){
				if($this->hasRemovedEventListener($event_str, $index)){
					$err = "{$f} {$event_str} event listener assigned to index \"{$index}\" for this ".$this->getDebugString()." was already removed ".$this->removedEventListeners[$event_str][$index];
					if($claimant){
						$err .= ". Current claimant attempting to remove it is {$claimant}.";
					}
					Debug::error($err);
				}
				Debug::error("{$f} no event {$event_str} listener assigned to index \"{$index}\" for this ".$this->getDebugString());
			}elseif($print){
				Debug::print("{$f} about to remove event \"{$event_str}\" at \"{$index}\" from this ".$this->getDebugString());
			}
			// remove $this->eventListeners[$event_str][$index] from existence --- i.e., don't event leave an array index
			$this->eventListeners[$event_str][$index] = null;
			unset($this->eventListeners[$event_str][$index]);
			if(empty($this->eventListeners[$event_str])){
				$this->eventListeners[$event_str] = null;
				unset($this->eventListeners[$event_str]);
				if(empty($this->eventListeners)){
					$this->eventListeners = null;
					unset($this->eventListeners);
				}
			}
			if($print){
				if(isset($this->eventListeners) && is_array($this->eventListeners) && !empty($this->eventListeners) && array_key_exists($event_str, $this->eventListeners)){
					Debug::print("{$f} after removing {$event_str} listener with index {$index}, we have the following keys:");
					Debug::printArray($this->eventListeners[$event_str]);
					if(array_key_exists($index, $this->eventListeners[$event_str])){
						Debug::error("{$f} no, unset does not remove array keys");
					}
				}else{
					Debug::print("{$f} after removing {$event_str} with listener ID {$index}, there are no more {$event_str} listeners");
				}
			}
			if($this->getDebugFlag()){
				if(!isset($this->removedEventListeners) || !is_array($this->removedEventListeners)){
					$this->removedEventListeners = [];
				}
				if(
					!isset($this->removedEventListeners[$event_str]) || 
					!is_array($this->removedEventListeners[$event_str])
				){
					$this->removedEventListeners[$event_str] = [];
				}
				$line = get_file_line(["removeEventListener", "dispatchEvent", "release", "deallocate", "{closure}"], 14);
				if($claimant !== null){
					$line .= " by claimant {$claimant}";
				}
				$this->removedEventListeners[$event_str][$index] = $line;
				if(!$this->hasRemovedEventListener($event_str, $index)){
					Debug::warning("{$f} assigning removed event listener strings is broken");
					Debug::printArray($this->removedEventListeners);
					Debug::printStackTrace();
				}
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
