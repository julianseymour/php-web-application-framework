<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;


use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

class RegenerateUseCase extends AbstractUpdateUseCase
{

	protected function processForm(DataStructure $updated_object): int
	{
		$f = __METHOD__;
		$print = false;
		if($print) {
			Debug::print("({$f} about to regenerate indices");
		}
		$form = $this->getPredecessor()->getProcessedFormObject();
		$indices = array_keys($form->getFormDataIndices($updated_object));
		return $updated_object->regenerateColumns($indices);
	}
}
