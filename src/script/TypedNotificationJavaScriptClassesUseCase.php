<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\file\BundleUseCase;

class TypedNotificationJavaScriptClassesUseCase extends BundleUseCase{
	
	public function sendHeaders(Request $request):bool{
		$ret = JavaScriptFileUseCase::sendHeadersStatic($request);
		$newest = $this->getLastModifiedTimestamp();
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", $newest) . " GMT");
		return $ret;
	}
	
	protected function getBundledFilenames(?string $filename = null):?array{
		$filenames = [];
		foreach(mods()->getTypedNotificationClasses() as $tnc){
			array_push($filenames, $tnc::getJavaScriptClassPath());
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
