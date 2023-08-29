<?php

namespace JulianSeymour\PHPWebApplicationFramework\app;

use JulianSeymour\PHPWebApplicationFramework\event\AfterRespondEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeRespondEvent;

trait ResponseHooksTrait{
	
	public function beforeRespondHook(): int{
		$this->dispatchEvent(new BeforeRespondEvent());
		return SUCCESS;
	}
	
	public function afterRespondHook(): int{
		$this->dispatchEvent(new AfterRespondEvent());
		return SUCCESS;
	}
}