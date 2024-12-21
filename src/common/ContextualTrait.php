<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\event\DeallocateEvent;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;

trait ContextualTrait{
	
	protected $context;
	
	public function hasContext():bool{
		return isset($this->context);
	}
	
	public function getContext(){
		$f = __METHOD__;
		if(!$this->hasContext()){
			Debug::error("{$f} context is undefined for this ".$this->getDebugString());
		}
		return $this->context;
	}
	
	public function releaseContext(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasContext()){
			Debug::error("{$f} context is undefined as this ".$this->getDebugString());
		}
		$context = $this->context;
		if(SUPPLEMENTAL_GARBAGE_COLLECTION_ENABLED && $context instanceof Basic && !$context->getAllocatedFlag()){
			Debug::error("{$f} context is already deallocated for this ".$this->getDebugString());
		}
		unset($this->context);
		if($this instanceof HiddenInput && $this->getDebugFlag()){
			Debug::print("{$f} aboout to deallocate context for this ".$this->getDebugString());
		}
		$this->release($context, $deallocate);
	}
	
	/**
	 * This is only used for replication
	 * @param object $context
	 * @return NULL|object
	 */
	public function setContext($context){
		if($this->hasContext()){
			$this->releaseContext();
		}
		if($context instanceof Basic){
			$that = $this;
			$closure = function(DeallocateEvent $event, HitPointsInterface $target)use($that){
				$f = __METHOD__;
				$print = false;
				$target->removeEventListener($event);
				if($that->hasContext()){
					if($print){
						Debug::print("{$f} about to release context for this ".$that->getDebugString());
					}
					$that->releaseContext(false);
				}elseif($print){
					Debug::print("{$f} context is undefined for this ".$that->getDebugString());
				}
			};
			$context->addEventListener(EVENT_DEALLOCATE, $closure);
		}
		return $this->context = $this->claim($context);
	}
}
