<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class SetSourceAttributeCommand extends ElementCommand implements ServerExecutableCommandInterface{

	protected $src;

	public function __construct($element=null, $src=null){
		parent::__construct($element);
		if($src !== null){
			$this->setSourceAttribute($src);
		}
	}

	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->src, $deallocate);
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasSourceAttribute()){
			$this->setSourceAttribute(replicate($that->getSourceAttribute()));
		}
		return $ret;
	}
	
	public function hasSourceAttribute():bool{
		return isset($this->src);
	}

	public function getSourceAttribute(){
		$f = __METHOD__;
		if(!$this->hasSourceAttribute()){
			Debug::error("{$f} source attribute is undefined");
		}
		return $this->src;
	}

	public function setSourceAttribute($src){
		if($this->hasSourceAttribute()){
			$this->release($this->src);
		}
		return $this->src = $this->claim($src);
	}

	public function toJavaScript(): string{
		$id = $this->getIdCommandString();
		if($id instanceof JavaScriptInterface){
			$id = $id->toJavaScript();
		}
		$src = $this->getSourceAttribute();
		if($src instanceof JavaScriptInterface){
			$src = $src->toJavaScript();
		}
		return "{$id}.src = {$src}";
	}

	public static function getCommandId(): string{
		return "setSource";
	}

	public function resolve(){
		$element = $this->getElement();
		while($element instanceof ValueReturningCommandInterface){
			$element = $element->evaluate();
		}
		$src = $this->getSourceAttribute();
		while($src instanceof ValueReturningCommandInterface){
			$src = $src->evaluate();
		}
		$element->setAttribute("src", $src);
	}
}
