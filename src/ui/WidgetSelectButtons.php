<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use JulianSeymour\PHPWebApplicationFramework\input\RadioButtonInput;
use JulianSeymour\PHPWebApplicationFramework\input\choice\MultipleRadioButtons;

class WidgetSelectButtons extends MultipleRadioButtons
{

	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$radio_widget_none = new RadioButtonInput($mode);
		$radio_widget_none->setIdAttribute("widget-none");
		$radio_widget_none->setNameAttribute("radio_widget");
		$radio_widget_none->addClassAttribute("hidden");
		$radio_widget_none->setCheckedAttribute("checked");
		$this->appendChild($radio_widget_none);
	}
}