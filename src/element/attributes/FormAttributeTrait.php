<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\attributes;

use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\FormElement;
use JulianSeymour\PHPWebApplicationFramework\form\FormTrait;

trait FormAttributeTrait
{

	use FlagBearingTrait;
	use FormTrait;

	public function setUseFormAttributeFlag($value)
	{
		if ($value === true && $this->hasForm()) {
			$form = $this->getForm();
			if ($form->hasIdAttribute()) {
				$this->setFormAttribute($form->getIdAttribute());
			}
		}
		return $this->setFlag("useFormAttribute", $value);
	}

	public function getUseFormAttributeFlag()
	{
		return $this->getFlag("useFormAttribute");
	}

	public function hasFormAttribute()
	{
		return $this->hasAttribute("form");
	}

	public function getFormAttribute()
	{
		$f = __METHOD__; //"FormAttributeTrait(".static::getShortClass().")->getFormAttribute()";
		if (! $this->hasFormAttribute()) {
			Debug::error("{$f} form attribute is undefined");
		}
		return $this->getAttribute("form");
	}

	public function setFormAttribute($value)
	{
		return $this->setAttribute("form", $value);
	}

	public function setForm(?FormElement $form):?FormElement
	{
		if ($form === null) {
			unset($this->form);
			return null;
		} elseif ($this->getUseFormAttributeFlag() && $form->hasIdAttribute()) {
			$this->setFormAttribute($form->getIdAttribute());
		}
		return $this->form = $form;
	}
}
