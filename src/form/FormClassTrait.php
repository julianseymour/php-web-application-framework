<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait FormClassTrait{
	
	protected $formClass;
	
	public function setFormClass(?string $class):?string{
		$f = __METHOD__;
		if(is_string($class)){
			if(!class_exists($class)){
				Debug::error("{$f} class \"{$class}\" does not exist");
			}elseif(!is_a($class, AjaxForm::class, true)){
				Debug::error("{$f} class is not a form");
			}
		}
		if($this->hasFormClass()){
			$this->release($this->formClass);
		}
		return $this->formClass = $this->claim($class);
	}
	
	public function hasFormClass():bool{
		return isset($this->formClass);
	}
	
	public function getFormClass():string{
		$f = __METHOD__;
		if(!$this->hasFormClass()){
			Debug::error("{$f} form class is undefined");
		}
		return $this->formClass;
	}
}