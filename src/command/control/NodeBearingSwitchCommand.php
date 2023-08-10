<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\IncrementVariableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\command\NodeBearingCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;

class NodeBearingSwitchCommand extends SwitchCommand implements AllocationModeInterface, NodeBearingCommandInterface{

	use AllocationModeTrait;

	public function extractChildNodes(int $mode): ?array{
		$f = __METHOD__;
		try {
			$children = [];
			$commands = $this->getEvaluatedCommands();
			foreach ($commands as $c) {
				$children = array_merge($children, $c->extractChildNodes($mode));
			}
			return $children;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function extractAnyway(){
		return false;
	}

	public function evaluate(?array $params = null){
		return $this->extractChildNodes($this->getAllocationMode());
	}

	public function incrementVariableName(int &$counter){
		foreach ($this->getCases() as $blocks) {
			foreach ($blocks as $block) {
				if ($block instanceof IncrementVariableNameInterface) {
					$block->incrementVariableName($counter);
				}
			}
		}
		if ($this->hasDefault()) {
			$default = $this->getDefault();
			if ($default instanceof IncrementVariableNameInterface) {
				$default->incrementVariableName($counter);
			}
		}
	}
}
