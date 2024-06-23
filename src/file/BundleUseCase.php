<?php
namespace JulianSeymour\PHPWebApplicationFramework\file;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\php2string;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\workflow\SimpleWorkflow;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

abstract class BundleUseCase extends UseCase{

	protected $bundledFilenames;

	protected abstract function getBundledFilenames(?string $filename = null):?array;

	public function __construct($predecessor = null, $segments = null){
		parent::__construct($predecessor, $segments);
		$this->setFlag("skipAsyncRequestMethodConfig", true);
	}

	public static function getDefaultWorkflowClass(): string{
		return SimpleWorkflow::class;
	}

	protected function getLastModifiedTimestamp(){
		$f = __METHOD__;
		$filenames = $this->getBundledFilenames();
		$newest = 0;
		foreach($filenames as $fn){
			$ts = filemtime($fn);
			if($ts === false){
				Debug::error("{$f} file \"{$fn}\" does not exist");
			}
			if($ts > $newest){
				$newest = $ts;
			}
		}
		return $newest;
	}

	public function blockCrossOriginRequest(){
		$f = __METHOD__;
		Debug::warning("{$f} cross origin request blocked");
		exit();
	}

	public function setBundledFilenames($filenames){
		return $this->bundledFilenames = $filenames;
	}

	public function hasBundledFilenames(){
		return !empty($this->bundledFilenames);
	}

	public final function hasSwitchUseCase(int $status): bool{
		return false;
	}

	public final function dispatchCallbacks(){
		return SUCCESS;
	}

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}
	
	public function echoResponse(): void{
		$f = __METHOD__;
		try{
			$print = false;
			if(! hasInputParameter("filename", $this)){
				if($print){
					Debug::warning("{$f} filename parameter does not exist");
				}
				return;
			}
			$filename = getInputParameter("filename", $this);
			$cache = false;
			if(false && cache()->enabled() && FILE_CACHE_ENABLED){
				if(cache()->hasFile($filename)){
					if($print){
						Debug::print("{$f} cache hit");
					}
					echo cache()->getFile($filename);
					return;
				}else{
					if($print){
						Debug::print("{$f} cache miss");
					}
					$cache = true;
					ob_start();
				}
			}elseif($print){
				Debug::print("{$f} caching is disabled");
			}
			$filenames = $this->getBundledFilenames();
			foreach($filenames as $path){
				if($print){
					Debug::print("{$f} path to file: \"{$path}\"");
				}
				echo "\n/*{$path}*/\n";
				echo php2string($path);
			}
			if($cache){
				if($print){
					Debug::print("{$f} updating cache for file \"{$filename}\"");
				}
				$string = ob_get_clean();
				cache()->setFile($filename, $string, time() + 30 * 60);
				echo $string;
				unset($string);
			}
			return;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
