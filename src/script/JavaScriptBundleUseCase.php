<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class JavaScriptBundleUseCase extends LocalizedJavaScriptFileUseCase{

	public function echoJavaScriptFileContents():void{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
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
			$uc = JavaScriptFileRouter::getUseCaseFromFilename($fn);
			if($print){
				Debug::print("{$f} filename \"{$fn}\" gives us a use case of class ".$uc->getShortClass());
			}
			$uc->echoResponse();
			deallocate($uc);
		}
	}
	
	public static function getFilename(): string{
		return "bundle.js";
	}
}

