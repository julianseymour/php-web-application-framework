<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use JulianSeymour\PHPWebApplicationFramework\command\CodeBlocksTrait;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ParentScopedTrait;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ScopedCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\variable\ScopedTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class LoopCommand extends Command implements JavaScriptInterface, ScopedCommandInterface, ServerExecutableCommandInterface{

	use CodeBlocksTrait;
	use ParentScopedTrait;
	use ScopedTrait;
	
	public function __construct(...$blocks){
		parent::__construct();
		if(isset($blocks) && count($blocks) > 0){
			$this->setCodeBlocks($blocks);
		}
	}
	
	public function dispose(bool $deallocate=false):void{
		if($this->hasScope()){
			$this->releaseScope($deallocate);
		}
		parent::dispose($deallocate);
		unset($this->parentScope);
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasScope()){
			$this->setScope($that->getScope());
		}
		if($that->hasParentScope()){
			$this->setParentScope($that->getParentScope());
		}
		return $ret;
	}
}
