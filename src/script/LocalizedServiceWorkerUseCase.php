<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\php2string;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;

class LocalizedServiceWorkerUseCase extends JavaScriptFileUseCase{
	
	public function echoJavaScriptFileContents():void{
		(new LocaleJavaScriptConstantUseCase())->echoResponse();
		//self.importScripts('/script/'.concat(LOCALE).concat('/sw_bundle.js'));
		$import = new CallFunctionCommand(
			"self.importScripts",
			new ConcatenateCommand(
				'/script/',
				new GetDeclaredVariableCommand("LOCALE"),
				'/sw_bundle.js'
			)
		);
		echo $import->toJavaScript();
		//const serviceWorkerCacheName = 'swcache';
		$cache_name = DeclareVariableCommand::const("serviceWorkerCacheName", "swcache");
		echo $cache_name->toJavaScript();
		/*const serviceWorkerCacheContent = [
			'/style/bundle.css',
			'/script/'.concat(LOCALE).'/bundle.js'
		];*/
		$cache_content = DeclareVariableCommand::const("serviceWorkerCacheContent", [
			'/style/bundle.css',
			new ConcatenateCommand(
				'/script/',
				new GetDeclaredVariableCommand("LOCALE"),
				'/bundle.js'
			)
		]);
		echo $cache_content->toJavaScript()."\n";
		echo php2string(FRAMEWORK_INSTALL_DIRECTORY."/script/service-worker.js")."\n";
	}
}