<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\NodeBearingCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;

class GetParentNodeCommand extends ElementCommand implements AllocationModeInterface, NodeBearingCommandInterface{

	use AllocationModeTrait;

	public function extractChildNodes(int $mode): ?array{
		return [
			$this->evaluate()
		];
	}

	public static function getCommandId(): string{
		return "parentNode";
	}

	public static function extractAnyway():bool{
		return false;
	}

	public function evaluate(?array $params = null){
		$element = $this->getElement();
		while($element instanceof ValueReturningCommandInterface){
			$element = $element->evaluate();
		}
		return $element->getParentNode();
	}

	public function toJavaScript(): string{
		$idcs = $this->getIdCommandString();
		if($idcs instanceof Command){
			$idcs = $idcs->toJavaScript();
		}
		return "{$idcs}.parentNode";
	}

	public function incrementVariableName(int &$counter){
		return $this->getElement()->incrementVariableName($counter);
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasAllocationMode()){
			$this->setAllocationMode(replicate($that->getAllocationMode()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->allocationMode, $deallocate);
	}
}
