<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\backwards_ref_enabled;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\FormElement;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseFormEvent;

trait FormTrait{

	protected $form;

	public function hasForm(): bool{
		return isset($this->form) && $this->form instanceof FormElement;
	}

	/**
	 *
	 * @return FormElement
	 */
	public function getForm():FormElement{
		$f = __METHOD__;
		if(!$this->hasForm()){
			Debug::error("{$f} form is undefined");
		}
		return $this->form;
	}

	public function setForm(?FormElement $form):?FormElement{
		if($this->hasForm()){
			$this->releaseForm();
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			return $this->form = $form;
		}
		return $this->form = $this->claim($form);
	}
	
	public function releaseForm(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->getAllocatedFlag()){
			Debug::error("{$f} you need to call releaseForm before the parent function in this class's redeclaration of dispose");
		}elseif(!$this->hasForm()){
			Debug::error("{$f} form is undefined for this ".$this->getDebugString());
		}
		$form = $this->getForm();
		unset($this->form);
		if($this->hasAnyEventListener(EVENT_RELEASE_FORM)){
			$this->dispatchEvent(new ReleaseFormEvent($form, $deallocate));
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			unset($form);
			return;
		}
		$this->release($form, false); //$deallocate);
	}
}
