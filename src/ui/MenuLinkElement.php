<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui;

use JulianSeymour\PHPWebApplicationFramework\app\pwa\ProgressiveHyperlinkElement;

class MenuLinkElement extends ProgressiveHyperlinkElement{

	protected function getProgressiveHyperlinkFunction():string{
		return "Menu.loadHyperlink";
	}
}
