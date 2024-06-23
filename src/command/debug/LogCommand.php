<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\debug;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class LogCommand extends Command implements JavaScriptInterface, ServerExecutableCommandInterface{

	protected $message;

	public function __construct($msg = null){
		parent::__construct();
		$this->setEscapeType(ESCAPE_TYPE_STRING);
		if(isset($msg)){
			$this->setMessage($msg);
		}
	}

	public function setMessage($msg){
		if($this->hasMessage()){
			$this->release($this->message);
		}
		return $this->message = $this->claim($msg);
	}

	public function hasMessage():bool{
		return isset($this->message);
	}

	public function getMessage(){
		$f = __METHOD__;
		if(!$this->hasMessage()){
			Debug::error("{$f} message is undefined");
		}
		return $this->message;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->message, $deallocate);
	}

	public function echoInnerJson(bool $destroy = false): void{
		Json::echoKeyValuePair("i", $this->getMessage(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public static function getCommandId(): string{
		return "console.log";
	}

	public function resolve(){
		$msg = $this->getMessage();
		while($msg instanceof ValueReturningCommandInterface){
			$msg = $msg->evaluate();
		}
		Debug::print($msg);
	}

	public function toJavaScript(): string{
		$msg = $this->getMessage();
		if($msg instanceof JavaScriptInterface){
			$msg = $msg->toJavaScript();
		}elseif(is_string($msg) || $msg instanceof StringifiableInterface){
			$msg = single_quote($msg);
		}
		$cmd = $this->getCommandId();
		return "{$cmd}({$msg})";
	}

	public function __toString(): string{
		return $this->toJavaScript();
	}
}
