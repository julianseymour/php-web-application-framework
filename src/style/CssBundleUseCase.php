<?php

namespace JulianSeymour\PHPWebApplicationFramework\style;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\php2string;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\file\BundleUseCase;
use Exception;

class CssBundleUseCase extends BundleUseCase{

	public function getUriSegmentParameterMap(): ?array{
		return [
			'action',
			'filename'
		];
	}

	public function getActionAttribute(): ?string{
		return '/style';
	}

	protected function getBundledFilenames(?string $filename = null):?array{
		$f = __METHOD__;
		try {
			if ($this->hasBundledFilenames()) {
				return $this->bundledFilenames;
			}
			$filenames = mods()->getCascadingStyleSheetFilepaths();
			return $this->setBundledFilenames($filenames);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function sendHeaders(Request $request): bool{
		$request = request();
		if(!$request->hasInputParameter("filename", $this)){
			$this->setObjectStatus(ERROR_FILE_NOT_FOUND);
			return parent::sendHeaders($request);
		}
		header("Content-Type: text/css", true);
		header("X-Content-Type-Options: nosniff");
		$newest = $this->getLastModifiedTimestamp();
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", $newest) . " GMT");
		// parent::sendHeaders();
		return false;
	}
}
