<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\settings;

use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;

class AccountSettingsLabel extends LabelElement
{

	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->setForAttribute("menu_slide-settings");
		$this->addClassAttribute("slide_menu_label", "background_color_1", "slide_select");
		$this->setInnerHTML(_("Settings"));
		return [];
	}
}