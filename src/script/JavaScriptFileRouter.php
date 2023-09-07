<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\Router;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class JavaScriptFileRouter extends Router{
	
	public function getUriSegmentParameterMap():?array{
		return [
			"action",
			"locale",
			"filename"
		];
	}
	
	public static function getUseCaseFromFilename(string $filename):UseCase{
		$f = __METHOD__;
		switch($filename){
			case "application.js":
				return new ApplicationJavaScriptClassUseCase();
			case "blob.js":
				return new JavaScriptBlobFileUseCase();
			case "bundle.js":
				return new JavaScriptBundleUseCase();
			case 'commands.js':
				return new CommandJavaScriptClassesUseCase();
			case "constants.js":
				return new JavaScriptConstantsUseCase();
			case "debug.js":
				return new DebugJavaScriptBlobFileUseCase();
			case "formdata.js":
				return new FormDataJavaScriptFunctionsUseCase();
			case "invokeable.js":
				return new InvokeableFunctionsJavaScriptConstantUseCase();
			case "locale.js":
				return new LocaleJavaScriptConstantUseCase();
			case "notifications.js":
				return new TypedNotificationJavaScriptClassesUseCase();
			case "observers.js":
				return new LegalIntersetionObserversJavaScriptConstantUseCase();
			case "sw_blob.js":
				return new ServiceWorkerBlobFileUseCase();
			case "sw_bundle.js":
				return new ServiceWorkerJavaScriptBundleUseCase();
			case "template.js":
				return new JavaScriptTemplateFunctionsUseCase();
			case "validators.js":
				return new ValidatorJavaScriptClassesUseCase();
			case "widgets.js":
				return new WidgetLabelIdsJavaScriptConstantUseCase();
			default:
				Debug::error("{$f} invalid script file \"{$filename}\"");
		}
	}
	
	public function getUseCase(Request $request): UseCase{
		$f = __METHOD__;
		if(!$this->hasInputParameter("filename")){
			Debug::error("{$f} filaname is unavailable. Request URI is {$_SERVER['REQUEST_URI']}");
		}
		$filename = $this->getInputParameter("filename");
		return static::getUseCaseFromFilename($filename);
	}
}