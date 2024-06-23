<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class StringTransformationCommand extends Command 
implements JavaScriptInterface, StringifiableInterface, ValueReturningCommandInterface{

	protected $subject;

	public function __construct($subject=null){
		$f = __METHOD__;
		parent::__construct();
		if($subject !== null){
			$this->setSubject($subject);
			if(!$this->hasSubject()){
				Debug::error("{$f} after setting string ID it is still undefined");
			}
		}
	}

	public function setSubject($subject){
		$f = __METHOD__;
		$print = false;
		if($this->hasSubject()){
			$this->release($this->subject);
		}
		if($print){
			if(is_string($subject)){
				Debug::print("{$f} setting subject to \"{$subject}\"");
			}else{
				Debug::print("{$f} subject is not a string");
			}
		}
		return $this->subject = $this->claim($subject);
	}

	public function hasSubject():bool{
		return isset($this->subject);
	}

	public function getSubject(){
		$f = __METHOD__;
		if(!$this->hasSubject()){
			Debug::error("{$f} subject \"{$this->subject}\" is undefined");
		}
		return $this->subject;
	}
	
	public function toJavaScript(): string{
		$subject = $this->getSubject();
		if($subject instanceof JavaScriptInterface){
			$subject = $subject->toJavaScript();
		}
		$command = $this->getCommandId();
		return "({$subject}).{$command}()";
	}

	public final function __toString(): string{
		return $this->evaluate();
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasSubject()){
			$this->setSubject(replicate($that->getSubject()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->subject, $deallocate);
	}
}
