<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\pwa\ProgressiveHyperlinkElement;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;

class MenuLinkElement extends ProgressiveHyperlinkElement{

	protected function getProgressiveHyperlinkFunction():string{
		return "Menu.loadHyperlink";
	}
}
