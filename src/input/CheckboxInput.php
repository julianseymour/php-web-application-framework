<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use Exception;

class CheckboxInput extends CheckedInput{

	public static function getTypeAttributeStatic(): string{
		return INPUT_TYPE_CHECKBOX;
	}

	public function setValueAttribute($value){
		$f = __METHOD__;
		try{
			if($value === 0 || $value === "off" || $value === false){
				// Debug::print("{$f} value is zero");
				if($this->hasCheckedAttribute()){
					$this->removeCheckedAttribute();
				}
			}elseif($value === 1 || $value === "on" || $value === true){
				// Debug::print("{$f} value is one");
				$this->check();
			}else{
				return parent::setValueAttribute($value);
			}
			return $value;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function configure(?AjaxForm $form=null): int{
		$f = __METHOD__;
		try{
			$print = false;
			$ret = parent::configure($form);
			$datum = $this->getContext();
			if($this->hasHiddenAttribute()){
				if($print){
					Debug::print("{$f} checkbox has a hidden attribute");
				}
			}elseif($datum->hasHumanReadableName()){
				$hrvn = $datum->getHumanReadableName();
				if(!$this->hasLabelString()){
					$this->setLabelString($hrvn);
				}
				$ls = $this->getLabelString();
				if(!$this instanceof FancyCheckbox){
					if(!$this->hasSuccessors()){
						$span1 = new SpanElement($this->getAllocationMode());
						$span1->setInnerHTML($ls);
						$this->pushSuccessor($span1);
					}
				}
			}
			// assign an ID attribute, as well as an event handler to ensure the ID remains unique after reindexing as a subordinate form input
			if($form instanceof AjaxForm && $form->hasIdAttribute() && !$this->hasIdAttribute()){
				$id = $form->getIdAttribute();
				$this->setIdAttribute(new ConcatenateCommand($id, "-", $this->getNameAttribute()));
				$box = $this;
				$closure = function ($event, $target) use ($box, $id, $print){
					$f = __METHOD__;
					$new_name = $box->getNameAttribute();
					if($print){
						Debug::print("{$f} name after subindexing is \"{$new_name}\"");
					}
					$new_name = str_replace('][', '-', $new_name);
					$new_name = str_replace('[', '_', $new_name);
					$new_name = str_replace(']', '', $new_name);
					if($print){
						Debug::print("{$f} ID suffix is \"{$new_name}\"");
					}
					$this->setIdAttribute(new ConcatenateCommand($id, "-", $new_name));
				};
				$this->addEventListener(EVENT_AFTER_SUBINDEX, $closure);
			}
			return $ret;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
