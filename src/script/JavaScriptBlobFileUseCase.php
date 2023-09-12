<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\file\BundleUseCase;

class JavaScriptBlobFileUseCase extends BundleUseCase{

	public function sendHeaders(Request $request):bool{
		$ret = JavaScriptFileUseCase::sendHeadersStatic($request);
		$newest = $this->getLastModifiedTimestamp();
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", $newest) . " GMT");
		return $ret;
	}
	
	protected function getBundledFilenames(?string $filename = null):?array{
		$f = __METHOD__;
		$print = false;
		$ret =  mods()->getJavaScriptFilePaths();
		if($print){
			Debug::print("{$f} returning the following:");
			Debug::printArray($ret);
		}
		return $ret;
	}
	
	public function getActionAttribute(): ?string{
		return "/script";
	}
	
	public function getUriSegmentParameterMap():?array{
		return [
			"action",
			"locale",
			//"filename"
		];
	}
	
	public function hasImplicitParameter(string $name):bool{
		switch($name){
			case "filename":
				return true;
			default:
				return parent::hasImplicitParameter($name);
		}
	}
	
	public function getImplicitParameter(string $name){
		switch($name){
			case "filename":
				return $this->getFilename();
			default:
				return parent::getImplicitParameter($name);
		}
	}
	
	public static function getFilename(): string{
		return "blob.js";
	}
}
