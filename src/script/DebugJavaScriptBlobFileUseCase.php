<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\file\BundleUseCase;

class DebugJavaScriptBlobFileUseCase extends BundleUseCase{
	
	public function sendHeaders(Request $request):bool{
		$ret = JavaScriptFileUseCase::sendHeadersStatic($request);
		$newest = $this->getLastModifiedTimestamp();
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", $newest) . " GMT");
		return $ret;
	}
	
	protected function getBundledFilenames(?string $filename = null):?array{
		return mods()->getDebugJavaScriptFilePaths();
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
