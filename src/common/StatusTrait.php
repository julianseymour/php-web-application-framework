<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait StatusTrait{

	protected $status;

	public function getObjectStatus(): int{
		return $this->hasObjectStatus() ? $this->status : STATUS_UNINITIALIZED;
	}

	public function setObjectStatus(?int $status):?int{
		$f = __METHOD__;
		if($status === null){
			unset($this->status);
			return null;
		}elseif(!is_int($status)){
			$gottype = is_object($status) ? $status->getClass() : gettype($status);
			Debug::error("{$f} status code is a {$gottype}, but must be an integer");
		}
		return $this->status = $status;
	}

	public function isUninitialized(): bool{
		return ! $this->hasObjectStatus();
	}

	public function isInitialized(): bool{
		return ! $this->isUninitialized();
	}

	public function isNotFound(): bool{
		return $this->getObjectStatus() === ERROR_NOT_FOUND;
	}

	public function succeed()
	{
		$this->setObjectStatus(SUCCESS);
		return $this;
	}

	public function hasObjectStatus(): bool{
		$f = __METHOD__;
		$print = false;
		if($print && isset($this->status) && is_int($this->status)) {
			Debug::print("{$f} yes, this object has a status code");
		}
		return isset($this->status) && is_int($this->status);
	}

	public function ejectObjectStatus(): ?int{
		if($this->hasObjectStatus()) {
			$status = $this->getObjectStatus();
			unset($this->status);
		}else{
			$status = null;
		}
		return $status;
	}
}
