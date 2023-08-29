<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class JavaScriptBundleUseCase extends JavaScriptFileUseCase{

	public function echoJavaScriptFileContents():void{
		$f = __METHOD__;
		$print = false;
		$files = [
			"constants.js",
			"locale.js",
			"blob.js",
			'notifications.js',
			"commands.js",
			"validators.js",
			"application.js",
			"formdata.js",
			"template.js",
			"invokeable.js",
			"observers.js",
			"widgets.js"
		];
		foreach($files as $fn){
			if($print){
				Debug::print("{$f} about to echo use case from filename  \"{$fn}\"");
			}
			echo "//{$fn}:\n";
			JavaScriptFileRouter::getUseCaseFromFilename($fn)->echoResponse();
		}
	}
}

