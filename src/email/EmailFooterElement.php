<?php
namespace JulianSeymour\PHPWebApplicationFramework\email;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;

class EmailFooterElement extends DivElement
{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		$f = __METHOD__;
		parent::__construct($mode, $context);
		$user = $this->getContext()->getRecipient();
		// $theme = $user->getTheme();
		// $theme_class = mods()->getThemeClass($theme);
		// $theme_data = new $theme_class();
		$this->setStyleProperties([
			"width" => "100%",
			"background-color" => "#000", // $theme_data->getBackgroundColor1(),
			"color" => "#e7e7e7", // $theme_data->getTextColor1(),
			"padding-top" => "8px",
			"padding-bottom" => "8px"
		]);
		$this->setInnerHTML(substitute(_("Copyright %1% %2%"), date('Y'), WEBSITE_NAME));
	}
}
