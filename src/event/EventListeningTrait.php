<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use JulianSeymour\PHPWebApplicationFramework\command\event\AddEventListenerCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

trait EventListeningTrait{

	protected $eventListeners;

	public function addEventListener(string $event_str, $closure, string $index = null){
		$f = __METHOD__;
		try{
			$print = false;
			if(!is_string($event_str)) {
				Debug::error("{$f} event type must be a string");
			}elseif(!is_array($this->eventListeners)) {
				$this->eventListeners = [];
			}
			if(! array_key_exists($event_str, $this->eventListeners)) {
				$this->eventListeners[$event_str] = [];
			}
			if($index == null) {
				array_push($this->eventListeners[$event_str], $closure);
				return array_search($closure, $this->eventListeners[$event_str]);
			}
			$this->eventListeners[$event_str][$index] = $closure;
			if($print) {
				$did = $this->getDebugId();
				Debug::print("{$f} assigned an event listener for \"{$event_str}\" to index \"{$index}\" for object with debug ID \"{$did}\"");
			}
			return $index;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function addEventListenerCommand($type, $listener): AddEventListenerCommand{
		return new AddEventListenerCommand($this, $type, $listener);
	}

	public function dispatchEvent($event, ...$params){
		$f = __METHOD__;
		try{
			$print = false;
			if(is_string($event)) {
				$event_str = $event;
			}elseif($event instanceof Event) {
				$event->setEventTimestamp(microtime(true));
				$event_str = $event->getEventType();
			}else{
				Debug::error("{$f} event type is neither string nor Event");
			}
			if(! isset($this->eventListeners) || ! is_array($this->eventListeners) || ! array_key_exists($event_str, $this->eventListeners)) {
				if($print) {
					Debug::print("{$f} no event listeners assigned to event type \"{$event_str}\"");
				}
				return false;
			}
			$pa = [];
			if(isset($params)) {
				foreach($params as $p) {
					array_push($pa, $p);
				}
			}
			foreach($this->eventListeners[$event_str] as $listener_id => $closure) {
				if(is_string($event)) {
					$closure($this, ...$pa);
				}elseif($event instanceof Event) {
					$event->setListenerId($listener_id);
					$closure($event, $event->setTarget($this), ...$pa);
					$event->setListenerId(null);
				}
			}
			if($print) {
				Debug::print("{$f} finished executing all closures assigned to event type \"{$event_str}\"");
			}
			return true;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function hasEventListener(string $event_str, string $index):bool{
		$f = __METHOD__;
		try{
			if(! isset($this->eventListeners) || ! is_array($this->eventListeners) || empty($this->eventListeners)) {
				return false;
			}elseif(! array_key_exists($event_str, $this->eventListeners)) {
				return false;
			}elseif(! array_key_exists($index, $this->eventListeners[$event_str])) {
				return false;
			}
			return true;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function removeEventListener($event_str, ?string $index = null){
		$f = __METHOD__;
		try{
			$print = false;
			if(!is_array($this->eventListeners)) {
				Debug::error("{$f} eventListener array is not an array");
			}elseif($event_str instanceof Event) {
				return $this->removeEventListener($event_str->getEventType(), $event_str->getListenerId());
			}elseif(!$this->hasEventListener($event_str, $index)) {
				Debug::error("{$f} no event {$event_str} listener assigned to index \"{$index}\"");
			} /*
			   * if(!array_key_exists($event_str, $this->eventListeners)){
			   * Debug::error("{$f} no event listeners assigned for event type \"{$event_str}\"");
			   * }elseif(!array_key_exists($index, $this->eventListeners[$event_str])){
			   * Debug::error("{$f} no event {$event_str} listener assigned to index \"{$index}\"");
			   * }
			   */
			// remove $this->eventListeners[$event_str][$index] from existence --- i.e., don't event leave an array index
			if($print) {
				$did = $this->getDebugId();
				Debug::print("{$f} about to remove event \"{$event_str}\" at \"{$index}\" for object with debug ID \"{$did}\"");
			}
			unset($this->eventListeners[$event_str][$index]); // = array_remove_key($this->eventListeners[$event_str], $index);
			if(empty($this->eventListeners)) {
				unset($this->eventListeners);
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
