<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionalTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class ControlStatementCommand extends Command implements JavaScriptInterface, ServerExecutableCommandInterface{

	use ExpressionalTrait;

	public function __construct($expr = null){
		parent::__construct();
		if(isset($expr)){
			$this->setExpression($expr);
		}
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasExpression()){
			$this->setExpression(replicate($that->getExpression()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false): void{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		parent::dispose($deallocate);
		if($this->hasExpression()){
			if($print){
				Debug::print("{$f} about to release expression ".$this->expression->getDebugString());
			}
			$this->release($this->expression, $deallocate);
		}elseif($print){
			Debug::print("{$f} no expression to release for ".$this->getDebugString());
		}
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		if($this->hasExpression()){
			Json::echoKeyValuePair('expression', $this->expression, $destroy);
		}
		parent::echoInnerJson($destroy);
	}
}
