<?php

namespace JulianSeymour\PHPWebApplicationFramework\app;

use JulianSeymour\PHPWebApplicationFramework\event\AfterRespondEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeRespondEvent;

trait ResponseHooksTrait{
	
	public function beforeRespondHook(): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_RESPOND)){
			$this->dispatchEvent(new BeforeRespondEvent());
		}
		return SUCCESS;
	}
	
	public function afterRespondHook(): int{
		if($this->hasAnyEventListener(EVENT_AFTER_RESPOND)){
			$this->dispatchEvent(new AfterRespondEvent());
		}
		return SUCCESS;
	}
}