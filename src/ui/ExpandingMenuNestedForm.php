<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\form\UniqueFormInterface;

abstract class ExpandingMenuNestedForm extends AjaxForm implements UniqueFormInterface
{

	public abstract static function getExpandingMenuLabelString($context);

	public abstract static function getExpandingMenuRadioButtonIdAttribute();

	public abstract static function getMaxHeightRequirement();

	public static function renderNestedFormElement($context)
	{
		return MenuExpandingFormWrapper::renderNestedFormElement($context, static::class);
	}

	public static function getMethodAttributeStatic(): ?string
	{
		return HTTP_REQUEST_METHOD_POST;
	}

	public static function bindNestedFormElement($context)
	{
		return MenuExpandingFormWrapper::bindNestedFormElement($context, static::class);
	}
}
