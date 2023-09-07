<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\app\workflow\SimpleWorkflow;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

abstract class JavaScriptFileUseCase extends UseCase{
	
	public abstract function echoJavaScriptFileContents():void;
	
	public function sendHeaders(Request $request): bool{
		if(!$request->hasInputParameter("filename", $this)){
			$this->setObjectStatus(ERROR_FILE_NOT_FOUND);
			return parent::sendHeaders($request);
		}
		return static::sendHeadersStatic($request);
	}
	
	public static function sendHeadersStatic(Request $request):bool{
		$f = __METHOD__;
		$print = false;
		iF($print){
			Debug::print("{$f} entered");
		}
		header("Content-Type: application/x-javascript; charset=utf-8", true);
		header("X-Content-Type-Options: nosniff");
		return false;
	}
	
	protected function getExecutePermissionClass(){
		return SUCCESS;
	}
	
	public static function getDefaultWorkflowClass():string{
		return SimpleWorkflow::class;
	}
	
	public final function echoResponse(): void{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			if (! hasInputParameter("filename", $this)) {
				if($print){
					Debug::warning("{$f} filename parameter does not exist");
				}
				return;
			}
			$filename = getInputParameter("filename", $this);
			$cache = false;
			if (cache()->enabled() && JAVASCRIPT_CACHE_ENABLED) {
				$locale = $this->getInputParameter("locale");
				$cache_key = "{$locale}:{$filename}";
				if (cache()->hasFile($cache_key)) {
					if ($print) {
						Debug::print("{$f} cache hit");
					}
					echo cache()->getFile($cache_key);
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
			$this->echoJavaScriptFileContents();
			if ($cache) {
				if ($print) {
					Debug::print("{$f} updating cache for file \"{$cache_key}\"");
				}
				$string = ob_get_clean();
				cache()->setFile($cache_key, $string, time() + 30 * 60);
				echo $string;
				unset($string);
			}
			return;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
