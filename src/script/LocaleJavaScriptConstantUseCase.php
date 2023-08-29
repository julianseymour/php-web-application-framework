<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;

class LocaleJavaScriptConstantUseCase extends JavaScriptFileUseCase{
	
	public function echoJavaScriptFileContents():void{
		echo CommandBuilder::const('LOCALE', $this->getInputParameter("locale"))->toJavaScript().";\n";
	}
}
