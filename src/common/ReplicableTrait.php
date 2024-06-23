<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterReplicateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeReplicateEvent;

trait ReplicableTrait{

	use FlagBearingTrait;
	
	public abstract function copy($that):int;
	
	public function replicate(...$params):?ReplicableInterface{
		$f = __METHOD__;
		$status = $this->beforeReplicateHook();
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} before replicate hook returned error status \"{$err}\"");
			$this->setObjectStatus($status);
			return null;
		}
		$r = new static(...$params);
		$r->setReplicaFlag(true);
		$r->copy($this);
		$status = $this->afterReplicateHook($r);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} after replicate hook returned error status \"{$err}\"");
			$this->setObjectStatus($status);
			return null;
		}
		return $r;
	}

	public function setReplicaFlag(bool $value = true): bool{
		return $this->setFlag("replica", $value);
	}

	public function getReplicaFlag(): bool{
		return $this->getFlag("replica");
	}

	public function beforeReplicateHook(): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_REPLICATE)){
			$this->dispatchEvent(new BeforeReplicateEvent());
		}
		return SUCCESS;
	}

	public function afterReplicateHook(ReplicableInterface $replica): int{
		if($this->hasAnyEventListener(EVENT_AFTER_REPLICATE)){
			$this->dispatchEvent(new AfterReplicateEvent($replica));
		}
		return SUCCESS;
	}
	
	public function getReplica():?ReplicableInterface{
		return $this->replicate();
	}
}
