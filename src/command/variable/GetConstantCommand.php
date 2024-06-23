<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class GetConstantCommand extends Command 
implements JavaScriptInterface, ValueReturningCommandInterface{
	
	use NamedTrait;
	
	public function __construct($name=null){
		parent::__construct();
		if($name !== null){
			$this->setName($name);
		}
	}
	
	public static function getCommandId(){
		return "constant";
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		$name = $this->getName();
		if(!defined($name)){
			Debug::error("{$f} constant \"{$name}\" is undefined");
		}
		return constant($name);
	}
	
	public function toJavaScript():string{
		return $this->getName();
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->name, $deallocate);
	}
}
