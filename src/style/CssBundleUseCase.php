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

class CssBundleUseCase extends BundleUseCase
{

	public function getUriSegmentParameterMap(): ?array
	{
		return [
			'action',
			'filename'
		];
	}

	public function echoResponse(): void
	{
		$f = __METHOD__;
		try {
			$print = false;
			if (! hasInputParameter("filename", $this)) {
				if($print){
					Debug::warning("{$f} filename parameter does not exist");
				}
				parent::echoResponse();
				return;
			}
			$filename = getInputParameter("filename", $this);
			switch ($filename) {
				case "bundle.css":
					$cache = false;
					if (cache()->enabled() && CSS_CACHE_ENABLED) {
						if (cache()->hasFile($filename)) {
							if ($print) {
								Debug::print("{$f} cache hit");
							}
							echo cache()->getFile('bundle.css');
							return;
						} else {
							if ($print) {
								Debug::print("{$f} cache miss");
							}
							$cache = true;
							ob_start();
						}
					} elseif ($print) {
						Debug::print("{$f} caching is disabled");
					}
					$this->echoCssBundle();
					if ($cache) {
						if ($print) {
							Debug::print("{$f} updating cache for file \"{$filename}\"");
						}
						$string = ob_get_clean();
						cache()->setFile($filename, $string, time() + 30 * 60);
						echo $string;
						unset($string);
					}
					return;
				default:
					Debug::error("{$f} invalid request segment \"{$filename}\"");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function echoCssBundle()
	{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::printStackTraceNoExit("{$f} entered");
			}
			$filenames = $this->getBundledFilenames();
			foreach ($filenames as $path) {
				if ($print) {
					Debug::print("{$f} path to CSS file: \"{$path}\"");
				}
				echo "\n/*{$path}*/\n";
				echo php2string($path);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getActionAttribute(): ?string
	{
		return '/style';
	}

	public function getUseCaseId()
	{
		return USE_CASE_BUNDLE_CSS;
	}

	public function getBundledFilenames()
	{
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

	public function sendHeaders(Request $request): bool
	{
		$request = request();
		if(!$request->hasInputParameter("filename", $this)){
			$this->setObjectstatus(ERROR_FILE_NOT_FOUND);
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
