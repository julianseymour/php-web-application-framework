<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;

class ServiceWorkerJavaScriptBundleUseCase extends LocalizedJavaScriptFileUseCase{
	
	public function echoJavaScriptFileContents():void{
		$files = [
			"constants.js",
			"application.js",
			"sw_blob.js",
			'notifications.js',
			"formdata.js"
		];
		foreach($files as $fn){
			$uc = JavaScriptFileRouter::getUseCaseFromFilename($fn);
			$uc->echoResponse();
			deallocate($uc);
		}
	}
	
	public static function getFilename(): string{
		return "sw_bundle.js";
	}
}
