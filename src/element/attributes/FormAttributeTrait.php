<?php

namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

use function JulianSeymour\PHPWebApplicationFramework\backwards_ref_enabled;
use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\FormElement;
use JulianSeymour\PHPWebApplicationFramework\form\FormTrait;

trait FormAttributeTrait{

	use FlagBearingTrait;
	use FormTrait;

	public function setUseFormAttributeFlag(bool $value=true):bool{
		if($value === true && $this->hasForm()){
			$form = $this->getForm();
			if($form->hasIdAttribute()){
				$this->setFormAttribute($form->getIdAttribute());
			}
		}
		return $this->setFlag("useFormAttribute", $value);
	}

	public function getUseFormAttributeFlag():bool{
		return $this->getFlag("useFormAttribute");
	}

	public function hasFormAttribute():bool{
		return $this->hasAttribute("form");
	}

	public function getFormAttribute(){
		$f = __METHOD__;
		if(!$this->hasFormAttribute()){
			Debug::error("{$f} form attribute is undefined");
		}
		return $this->getAttribute("form");
	}

	public function setFormAttribute($value){
		return $this->setAttribute("form", $value);
	}

	public function setForm(?FormElement $form):?FormElement{
		if($this->hasForm()){
			$this->releaseForm();
		}
		if($this->getUseFormAttributeFlag() && $form->hasIdAttribute()){
			$this->setFormAttribute($form->getIdAttribute());
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			return $this->form = $form;
		}
		return $this->form = $this->claim($form);
	}
}
