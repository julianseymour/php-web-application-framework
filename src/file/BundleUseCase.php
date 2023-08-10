<?php
namespace JulianSeymour\PHPWebApplicationFramework\file;

use function JulianSeymour\PHPWebApplicationFramework\request;
use JulianSeymour\PHPWebApplicationFramework\app\workflow\SimpleWorkflow;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

abstract class BundleUseCase extends UseCase
{

	protected $bundledFilenames;

	protected abstract function getBundledFilenames();

	public function __construct($predecessor = null, $segments = null)
	{
		parent::__construct($predecessor, $segments);
		$this->setFlag("skipAsyncRequestMethodConfig", true);
	}

	public static function getDefaultWorkflowClass(): string
	{
		return SimpleWorkflow::class;
	}

	protected function getLastModifiedTimestamp()
	{
		$f = __METHOD__; //BundleUseCase::getShortClass()."(".static::getShortClass().")->getLastModifiedTimestamp()";
		$filenames = $this->getBundledFilenames();
		$newest = 0;
		foreach ($filenames as $fn) {
			$ts = filemtime($fn);
			if ($ts === false) {
				Debug::error("{$f} file \"{$fn}\" does not exist");
			}
			if ($ts > $newest) {
				$newest = $ts;
			}
		}
		return time(); // $newest;
	}

	public function blockCrossOriginRequest()
	{
		$f = __METHOD__; //BundleUseCase::getShortClass()."(".static::getShortClass().")->blockCrossOriginRequest()";
		Debug::warning("{$f} cross origin request blocked");
		//$this->handleRequest(request());
		// $this->echoResponse($this->getObjectStatus());
		exit();
	}

	public function setBundledFilenames($filenames)
	{
		return $this->bundledFilenames = $filenames;
	}

	public function hasBundledFilenames()
	{
		return ! empty($this->bundledFilenames);
	}

	public final function hasSwitchUseCase(int $status): bool
	{
		return false;
	}

	public final function dispatchCallbacks()
	{
		return SUCCESS;
	}

	public function isPageUpdatedAfterLogin(): bool
	{
		return false;
	}

	protected function getExecutePermissionClass()
	{
		return SUCCESS;
	}
}
