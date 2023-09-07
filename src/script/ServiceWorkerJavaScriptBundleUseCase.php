<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

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
			JavaScriptFileRouter::getUseCaseFromFilename($fn)->echoResponse();
		}
	}
}
