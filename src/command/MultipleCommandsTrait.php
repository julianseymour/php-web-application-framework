<?php

namespace JulianSeymour\PHPWebApplicationFramework\command;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait MultipleCommandsTrait{
	
	use ArrayPropertyTrait;
	
	public function hasCommands():bool{
		return $this->hasArrayProperty("commands");
	}
	
	/**
	 * return an array representing a list of commands to execute after returning from callbacks
	 * executed with the automatic form submission system
	 *
	 * @return mixed[]
	 */
	public final function getCommands(){
		return $this->getProperty("commands");
	}
	
	public function pushCommand(...$commands){
		$f = __METHOD__;
		$print = false;
		if($print){
			$count = count($commands);
			Debug::print("{$f} pushing {$count} commands");
		}
		return $this->pushArrayProperty("commands", ...$commands);
	}
	
	public function setCommands(?array $commands):?array{
		return $this->setArrayProperty("commands", $commands);
	}
	
	public function withCommands(?array $commands):object{
		$this->setCommands($commands);
		return $this;
	}
	
	public function getCommandCount():int{
		return $this->getArrayPropertyCount("commands");
	}
}
