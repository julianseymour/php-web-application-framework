<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\FormElement;

trait FormTrait{

	protected $form;

	public function hasForm(): bool{
		return isset($this->form) && $this->form instanceof FormElement;
	}

	/**
	 *
	 * @return FormElement
	 */
	public function getForm(): FormElement{
		$f = __METHOD__; //"FormTrait(".static::getShortClass().")->getForm()";
		if (! $this->hasForm()) {
			Debug::error("{$f} form is undefined");
		}
		return $this->form;
	}

	public function setForm(?FormElement $form):?FormElement{
		if ($form === null) {
			unset($this->form);
			return null;
		}
		return $this->form = $form;
	}
}
