<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\IncrementVariableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\command\NodeBearingCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class NodeBearingIfCommand extends IfCommand implements NodeBearingCommandInterface{

	use AllocationModeTrait;

	public function extractChildNodes(int $mode): ?array{
		$f = __METHOD__;
		try{
			$decl = $this->getDeclarationLine();
			$print = false && $this->getDebugFlag();
			if($print){
				Debug::print("{$f} declared {$decl}");
			}
			$children = [];
			if($this->evaluate()){ // getExpression()->evaluate()){
				if($print){
					Debug::print("{$f} conditional satisfied");
				}
				if($this->hasThenCommands()){
					foreach($this->getThenCommands() as $then){
						$tc = $then->getClass();
						if($then instanceof NodeBearingCommandInterface){
							if($print){
								Debug::print("{$f} then command is a node-bearing \"{$tc}\"");
							}
							$merge_me = $then->extractChildNodes($mode);
							if(!is_array($merge_me)){
								Debug::error("{$f} merge_me is not an array");
							}
							$children = array_merge($children, $merge_me);
						}elseif($then instanceof ServerExecutableCommandInterface){
							if($print){
								Debug::print("{$f} then command is a resolvent \"{$tc}\"");
							}
							$then->resolve();
						}elseif($then instanceof ValueReturningCommandInterface){
							return [
								$then->evaluate()
							];
						}else{
							Debug::error("{$f} then command is an unrecognized \"{$tc}\"");
						}
					}
				}
			}elseif($this->hasElseCommands()){
				if($print){
					Debug::print("{$f} condition failed, and we have else commands");
				}
				foreach($this->getElseCommands() as $else){
					$ec = $else->getClass();
					if($else instanceof NodeBearingCommandInterface){
						if($print){
							Debug::print("{$f} else command is a node-bearing \"{$ec}\"");
						}
						$merge_me = $else->extractChildNodes($mode);
						if(!is_array($merge_me)){
							Debug::error("{$f} merge_me is not an array");
						}
						$children = array_merge($children, $merge_me);
					}elseif($else instanceof ServerExecutableCommandInterface){
						if($print){
							Debug::print("{$f} else command is a resolvent \"{$ec}\"");
						}
						$else->resolve();
					}elseif($else instanceof ValueReturningCommandInterface){
						return [
							$else->evaluate()
						];
					}else{
						Debug::error("{$f} else command is an unrecognized \"{$ec}\"");
					}
				}
			}elseif($print){
				Debug::print("{$f} condition failed, and there are no else commands");
			}
			if($print){
				$count = count($children);
				Debug::print("{$f} returning {$count} child nodes with the following classes:");
				foreach($children as $child){
					Debug::print($child->getClass());
				}
			}
			return $children;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function extractAnyway(){
		return false;
	}

	public function incrementVariableName(int &$counter){
		if($this->hasThenCommands()){
			foreach($this->getThenCommands() as $then){
				if($then instanceof IncrementVariableNameInterface){
					$then->incrementVariableName($counter);
				}
			}
		}
		if($this->hasElseCommands()){
			foreach($this->getElseCommands() as $else){
				if($else instanceof IncrementVariableNameInterface){
					$else->incrementVariableName($counter);
				}
			}
		}
	}
}
