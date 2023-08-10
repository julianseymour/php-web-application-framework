<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

class NumberInput extends NumericInput
{

	use ReadOnlyAttributeTrait;
	use RequiredAttributeTrait;

	public function getTypeAttribute(): string
	{
		return "number";
	}

	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_NUMBER;
	}

	/*
	 * public function configure(AjaxForm $form):int{
	 * $ret = parent::configure($form);
	 * if(!$this instanceof RangeInput){
	 * if($this->hasValueAttribute() && $this->getValueAttribute() == 0){
	 * $this->setValueAttribute("");
	 * }
	 * }
	 * return $ret;
	 * }
	 */
}
