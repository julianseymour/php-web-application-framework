<?php

namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

use JulianSeymour\PHPWebApplicationFramework\command\element\SetLabelHTMLForCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

/**
 * LabelElement and OutputElement
 *
 * @author j
 *        
 */
trait ForAttributeTrait{

	use FormAttributeTrait;

	public function setForAttribute($for){
		/*$f = __METHOD__;
		$print = false;
		if($for instanceof ConcatenateCommand){
			$did = $for->getDebugId();
			if($for->getFlag("reserved")){
				Debug::error("{$f} command with debug ID \"{$did}\" was already reserved");
			}elseif($print){
				Debug::print("{$f} reserving command with debug ID \"{$did}\"");
			}
			$for->setFlag("reserved", true);
			if($print){
				Debug::print("{$f} setting for attribute to a concatenate command for label with debug ID \"{$this->debugId}\"");
			}
		}elseif(is_string($for)){
			//
		}*/
		return $this->setAttribute("for", $for);
	}

	public function hasForAttribute(): bool{
		return $this->hasAttribute("for");
	}

	public function getForAttribute(){
		$f = __METHOD__;
		if(!$this->hasForAttribute()){
			Debug::error("{$f} for attribute is undefined");
		}
		return $this->getAttribute("for");
	}

	public function setForAttributeCommand($for): SetLabelHTMLForCommand{
		return new SetLabelHTMLForCommand($this, $for);
	}
}
