<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\DisposableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class PromiseSignature extends Basic implements DisposableInterface, JavaScriptInterface{

	protected $fulfillmentHandler;

	protected $rejectionHandler;

	public function __construct(?JavaScriptFunction $fulfilled=null, ?JavaScriptFunction $rejected = null){
		parent::__construct();
		if($fulfilled !== null){
			$this->setFulfillmentHandler($fulfilled);
		}
		if($rejected !== null){
			$this->setRejectionHandler($rejected);
		}
	}

	public function setFulfillmentHandler(?JavaScriptFunction $fulfilled): ?JavaScriptFunction{
		$f = __METHOD__;
		if($this->hasFulfillmentHandler()){
			$this->release($this->fulfillmentHandler);
		}
		if(!$fulfilled instanceof JavaScriptFunction){
			Debug::error("{$f} fulfillment handler must be an instanceof JavaScriptFunction");
		}
		return $this->fulfillmentHandler = $this->claim($fulfilled);
	}

	public function hasFulfillmentHandler():bool{
		return isset($this->fulfillmentHandler) && $this->fulfillmentHandler instanceof JavaScriptFunction;
	}

	public function getFulfillmentHandler(){
		$f = __METHOD__;
		if(!$this->hasFulfillmentHandler()){
			Debug::error("{$f} fulfillment handler is undefined");
		}
		return $this->fulfillmentHandler;
	}

	public function setRejectionHandler(?JavaScriptFunction $rh): ?JavaScriptFunction{
		$f = __METHOD__;
		if($this->hasRejectionHandler()){
			$this->release($this->rejectionHandler);
		}
		if($rh !== null && if(!$rh instanceof JavaScriptFunction){
			Debug::error("{$f} fulfillment handler must be an instanceof JavaScriptFunction");
		}
		return $this->rejectionHandler = $this->claim($rh);
	}

	public function hasRejectionHandler():bool{
		return isset($this->rejectionHandler) && $this->rejectionHandler instanceof JavaScriptFunction;
	}

	public function getRejectionHandler(){
		$f = __METHOD__;
		if(!$this->hasRejectionHandler()){
			Debug::error("{$f} rejection handler is undefined");
		}
		return $this->rejectionHandler;
	}

	function toJavaScript(): string{
		$string = $this->getFulfillmentHandler()->toJavaScript();
		if($this->hasRejectionHandler()){
			$string .= ", " . $this->getRejectionHandler()->toJavaScript();
		}
		return $string;
	}

	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->fulfillmentHandler, $deallocate);
		$this->release($this->rejectionHandler, $deallocate);
	}
}
