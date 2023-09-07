<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use Exception;

class WidgetLabelIdsJavaScriptConstantUseCase extends LocalizedJavaScriptFileUseCase{
	
	public function echoJavaScriptFileContents():void{
		$f = __METHOD__;
		try {
			$labels = [];
			foreach (mods()->getWidgetClasses() as $class) {
				$wid = $class::getWidgetLabelId();
				if ($wid == null) {
					continue;
				}
				array_push($labels, $wid);
			}
			$cmd = CommandBuilder::const("widgetLabelIds", $labels);
			echo $cmd->toJavaScript().";\n";
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}