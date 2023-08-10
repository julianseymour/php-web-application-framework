<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;

use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

class UnsetUseCase extends AbstractUpdateUseCase
{

	protected function processForm(DataStructure $updated_object): int
	{
		$form = $this->getPredecessor()->getProcessedFormObject();
		$indices = array_keys($form->getFormDataIndices($updated_object));
		return $updated_object->unsetColumnValues($indices);
	}
}
