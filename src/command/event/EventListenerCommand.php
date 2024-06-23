<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\common\TypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class EventListenerCommand extends Command implements JavaScriptInterface{

	use TypeTrait;

	protected $eventTarget;

	protected $listener;

	public function __construct($eventTarget=null, $type=null, $listener=null){
		parent::__construct();
		if($eventTarget !== null){
			$this->setEventTarget($eventTarget);
		}
		if($type !== null){
			$this->setType($type);
		}
		if($listener !== null){
			$this->setListener($listener);
		}
	}

	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			'capture'
		]);
	}

	public function setCaptureFlag(bool $value = true): bool{
		return $this->setFlag("capture", $value);
	}

	public function getCaptureFlag(): bool{
		return $this->getFlag("capture");
	}

	public function setListener($listener){
		if($this->hasListener()){
			$this->release($this->listener);
		}
		return $this->listener = $this->claim($listener);
	}

	public function hasListener():bool{
		return isset($this->listener);
	}

	public function getListener(){
		$f = __METHOD__;
		if(!$this->hasListener()){
			Debug::error("{$f} event listener is undefined");
		}
		return $this->listener;
	}

	public function setEventTarget($target){
		if($this->hasEventTarget()){
			$this->release($this->eventTarget);
		}
		return $this->target = $this->claim($target);
	}

	public function hasEventTarget():bool{
		return isset($this->target);
	}

	public function getEventTarget(){
		$f = __METHOD__;
		if(!$this->hasEventTarget()){
			Debug::error("{$f} event target is undefined");
		}
		return $this->target;
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasType()){
			$this->setType(replicate($that->getType()));
		}
		if($that->hasListener()){
			$this->setListener(replicate($that->getListener()));
		}
		if($that->hasEventTarget()){
			$this->setEventTarget(replicate($that->getEventTarget()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->type, $deallocate);
		$this->release($this->listener, $deallocate);
		$this->release($this->eventTarget, $deallocate);
	}
}
