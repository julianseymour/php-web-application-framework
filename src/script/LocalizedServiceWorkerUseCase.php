<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\php2string;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class LocalizedServiceWorkerUseCase extends JavaScriptFileUseCase{
	
	public function getImplicitParameter(string $name){
		$f = __METHOD__;
		switch($name){
			case "filename":
				return "service-worker.js";
			default:
				Debug::error("{$f} undefined implicit parameter \"{$name}\"");
		}
	}
	
	public function hasImplicitParameter(string $name):bool{
		return $name === "filename";
	}
	
	public function getActionAttribute():string{
		return "/service-worker.js";
	}
	
	public function echoJavaScriptFileContents():void{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if($print){
			Debug::print("{$f} entered");
		}
		echo "try{\n";
		LocaleJavaScriptConstantUseCase::echoJavaScriptFileContentsStatic($this->getInputParameter("locale"));
		$import = new CallFunctionCommand(
			"self.importScripts",
			new ConcatenateCommand(
				'/script/',
				new GetDeclaredVariableCommand("LOCALE"),
				'/sw_bundle.js'
			)
		);
		echo $import->toJavaScript().";\n";
		//const serviceWorkerCacheName = 'swcache';
		$cache_name = DeclareVariableCommand::const("serviceWorkerCacheName", "swcache");
		echo $cache_name->toJavaScript().";\n";
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
		echo $cache_content->toJavaScript().";\n";
		echo php2string(FRAMEWORK_INSTALL_DIRECTORY."/script/service-worker.js")."\n";
		echo "}catch(x){console.error(x.toString()); console.trace(); }\n";
	}
}