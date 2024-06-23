<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\IncrementVariableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\command\NodeBearingCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class NodeBearingForEachLoopCommand extends ForEachLoopCommand implements AllocationModeInterface, NodeBearingCommandInterface{

	use AllocationModeTrait;

	public function extractChildNodes(int $mode): ?array{
		$f = __METHOD__;
		try{
			$iteratee = $this->getIteratedObject();
			while($iteratee instanceof ValueReturningCommandInterface){
				$iteratee = $iteratee->evaluate();
			}
			$count = count($iteratee);
			Debug::print("{$f} iterating over \"{$count}\" objects");
			$chilren = [];
			$blocks = $this->getCodeBlocks();
			$iterator = $this->getIterator();
			foreach($iteratee as $i){
				$iterator->setValue($i);
				foreach($blocks as $b){
					if(is_object($b)){
						$bc = $b->getClass();
						Debug::print("{$f} code block is a \"{$bc}\"");
						if($b instanceof NodeBearingCommandInterface){
							Debug::print("{$f} code block {$bc} is a node-bearing media command; extracting its nodes");
							$chilren = array_merge($chilren, $b->extractChildNodes($mode));
						}elseif($b instanceof ServerExecutableCommandInterface){
							Debug::print("{$f} code block {$bc} is a resolvent media command; resolving it now");
							$b->resolve();
						}else{
							Debug::error("{$f} code block {$bc} is not recognized by this function");
						}
					}else{
						$gottype = gettype($b);
						Debug::error("{$f} code block is a \"{$gottype}\"");
					}
				}
			}
			$count = count($chilren);
			Debug::print("{$f} returning \"{$count}\" child nodes");
			return $chilren;
		}catch(Exception $x){
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
		foreach($this->getCodeBlocks() as $b){
			if($b instanceof IncrementVariableNameInterface){
				$b->incrementVariableName($counter);
			}
		}
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->allocationMode, $deallocate);
	}
}
