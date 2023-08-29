<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\file\BundleUseCase;

class CommandJavaScriptClassesUseCase extends BundleUseCase{
	
	public function sendHeaders(Request $request):bool{
		$ret = JavaScriptFileUseCase::sendHeadersStatic($request);
		$newest = $this->getLastModifiedTimestamp();
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", $newest) . " GMT");
		return $ret;
	}
	
	protected function getBundledFilenames(?string $filename = null):?array{
		$f = __METHOD__;
		$print = false;
		$filenames = [];
		foreach(mods()->getClientCommandClasses() as $tnc){
			$fn = $tnc::getJavaScriptClassPath();
			array_push($filenames, $fn);
		}
		if($print){
			Debug::print("{$f} returning the following filenames:");
			Debug::printArray($filenames);
		}
		return $filenames;
	}
	
	public function getActionAttribute(): ?string{
		return "/script";
	}
	
	public function getUriSegmentParameterMap():?array{
		return [
			"action",
			"locale",
			"filename"
		];
	}
}
