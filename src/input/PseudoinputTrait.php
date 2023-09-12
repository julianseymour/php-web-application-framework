<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\element\FormElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;

trait PseudoinputTrait{

	public function setForm(?FormElement $form):?FormElement{
		if($form == null){
			unset($this->form);
			return null;
		}
		return $this->form = $form;
	}

	public function hasForm(): bool{
		return isset($this->form) && $this->form instanceof FormElement;
	}

	public function getForm(){
		if(!$this->hasForm()) {
			return null;
		}
		return $this->form;
	}

	public function configure(AjaxForm $form): int{
		return SUCCESS;
	}

	public function subindexNameAttribute($super_index){
		ErrorMessage::unimplemented(__METHOD__);
	}
}