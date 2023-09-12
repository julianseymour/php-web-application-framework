<?php
namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\command\NodeBearingCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

/**
 * This class only exists to overridde override dynamic button generation with a template-compatible
 * method.
 * For example, the StateUpdateForm, or any form that changes insert/update/delete depending on context
 */
abstract class GenerateFormButtonsCommand extends ElementCommand implements AllocationModeInterface, NodeBearingCommandInterface{

	use AllocationModeTrait;

	public function extractChildNodes(int $mode): ?array
	{
		$f = __METHOD__;
		$form = $this->getElement();
		$names = $form->getDirectives();
		$buttons = [];
		if(!empty($names)) {
			foreach($names as $name) {
				$generated = $form->generateButtons($name);
				if(!is_array($generated)) {
					Debug::error("{$f} not an array");
				}
				$buttons = array_merge($buttons, $generated);
			}
		}
		return $buttons;
	}

	public function evaluate(?array $params = null)
	{
		return $this->extractChildNodes($this->getAllocationMode());
	}

	public function incrementVariableName(int &$counter)
	{
		return null;
	}
}
