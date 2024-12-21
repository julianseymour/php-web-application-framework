<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\FormElement;

trait SuperiorFormTrait{
	
	protected $superiorForm;
	
	protected $superiorFormIndex;
	
	public function hasSuperiorForm(): bool{
		return isset($this->superiorForm) && $this->superiorForm instanceof FormElement;
	}
	
	public function setSuperiorForm($form){
		if($this->hasSuperiorForm()){
			$this->releaseSuperiorForm();
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			return $this->superiorForm = $form;
		}
		return $this->superiorForm = $this->claim($form);
	}
	
	public function setSuperiorFormIndex($column_name){
		if($this->hasSuperiorFormIndex()){
			$this->release($this->superiorFormIndex);
		}
		return $this->superiorFormIndex = $this->claim($column_name);
	}
	
	public function getSuperiorForm():AjaxForm{
		$f = __METHOD__;
		if(!$this->hasSuperiorForm()){
			Debug::error("{$f} superior form is undefined");
		}elseif(!$this->hasSuperiorFormIndex()){
			Debug::error("{$f} superior form index is undefined");
		}
		return $this->superiorForm;
	}
	
	public function hasSuperiorFormIndex(): bool{
		return !empty($this->superiorFormIndex);
	}
	
	public function getSuperiorFormIndex(): ?string{
		$f = __METHOD__;
		if(!$this->hasSuperiorForm()){
			Debug::error("{$f} superior form is undefined");
		}elseif(!$this->hasSuperiorFormIndex()){
			Debug::error("{$f} superior form index is undefined");
		}
		return $this->superiorFormIndex;
	}
	
	public function setNestedFlag(bool $value = true): bool{
		return $this->setFlag("nested", $value);
	}
	
	public function getNestedFlag(): bool{
		return $this->getFlag("nested");
	}
	
	public function nested(bool $value = true): AjaxForm{
		$this->setNestedFlag($value);
		return $this;
	}
	
	public function releaseSuperiorForm(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasSuperiorForm()){
			Debug::error("{$f} superior form is undefined");
		}
		$sf = $this->superiorForm;
		unset($this->superiorForm);
		if(!BACKWARDS_REFERENCES_ENABLED){
			unset($sf);
			return;
		}
		$this->release($sf, $deallocate);
	}
}

