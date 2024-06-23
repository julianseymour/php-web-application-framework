<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\command\element\GetInnerHTMLCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetInnerHTMLCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait InnerHTMLTrait{
	
	protected $innerHTML;
	
	public function hasInnerHTML():bool{
		return isset($this->innerHTML);
	}
	
	public function getInnerHTML(){
		$f = __METHOD__;
		if(!$this->hasInnerHTML()){
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} innerHTML is undefined. eclared {$decl}");
		}
		return $this->innerHTML;
	}
	
	public function setInnerHTML($innerHTML){
		if($this->hasInnerHTML()){
			$this->release($this->innerHTML);
		}
		return $this->innerHTML = $this->claim($innerHTML);
	}
	
	public function getInnerHTMLCommand():GetInnerHTMLCommand{
		return new GetInnerHTMLCommand($this);
	}
	
	public function withInnerHTML($innerHTML){
		$this->setInnerHTML($innerHTML);
		return $this;
	}
	
	public function setInnerHTMLCommand($innerHTML): SetInnerHTMLCommand{
		return new SetInnerHTMLCommand($this, $innerHTML);
	}
}
