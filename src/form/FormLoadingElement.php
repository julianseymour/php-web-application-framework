<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use Exception;

class FormLoadingElement extends DivElement{
	
	public function generateChildNodes():?array{
		$f = __METHOD__;
		try{
			$form = $this->getContext();
			$print = false && $form->getDebugFlag();
			$this->addClassAttribute("load_container");
			$this->setTemplateFlag($form->getTemplateFlag());
			if(!$form->hasIdAttribute()){
				if(!$form->hasAttribute("temp_id")){
					Debug::error("{$f} you must assign a media command or string literal template ID attribute to the form in order for the loading container to template its own ID attribute. Form is ".$form->getDebugString());
				}
				$tida = $form->getAttribute("temp_id");
				if($print){
					Debug::print("{$f} temp ID attribute \"{$tida}\"");
				}
			}else{
				$tida = $form->getIdAttribute();
				if($print){
					if($tida instanceof Command){
						Debug::print("{$f} ID attribute is a command that cannot be evaluated right now");
					}else{
						Debug::print("{$f} regular ID attribute \"{$tida}\"");
					}
				}
			}
			$concat = new ConcatenateCommand('load_', $tida);
			$this->setIdAttribute($concat);
			if(!$form->getTemplateFlag()){
				deallocate($concat);
			}
			$this->setAllowEmptyInnerHTML(true);
			return [];
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function releaseContext(bool $deallocate=false){
		if(BACKWARDS_REFERENCES_ENABLED){
			parent::releaseContext($deallocate);
		}
		unset($this->context);
	}
	
	public function setContext($context){
		if(BACKWARDS_REFERENCES_ENABLED){
			return parent::setContext($context);
		}
		return $this->context = $context;
	}
}

