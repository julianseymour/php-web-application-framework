<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;


use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\request;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

class UpdateUseCase extends AbstractUpdateUseCase
{

	protected function processForm(DataStructure $updated_object): int
	{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::print("{$f} this is a regular update -- processing form");
		}
		$form = $this->getPredecessor()->getProcessedFormObject();
		$files = null;
		if (request()->hasRepackedIncomingFiles()) {
			$files = request()->getRepackedIncomingFiles();
		}
		$post = getInputParameters();
		if ($print) {
			Debug::print("{$f} about to process the following parameters:");
			// Debug::printArray($post);
		}
		return $updated_object->processForm($form, $post, $files);
	}
}
