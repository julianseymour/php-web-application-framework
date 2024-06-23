<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

abstract class ExpressionCommand extends Command implements ValueReturningCommandInterface{

	protected $operator;
	
	public static function declareFlags(): array{
		return array_merge(parent::declareFlags(), [
			"negate"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"negate"
		]);
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasOperator()){
			$this->setOperator(replicate($that->getOperator()));
		}
		return $ret;
	}
	
	public function setNegateFlag(bool $value=true):bool{
		return $this->setFlag("negate", $value);
	}
	
	public function toggleNegateFlag():bool{
		return $this->setNegateFlag(!$this->getNegateFlag());
	}
	
	public function negate():ExpressionCommand{
		$this->toggleNegateFlag();
		return $this;
	}

	public function getNegateFlag(){
		return $this->getFlag("negate");
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->operator, $deallocate);
	}

	public function setOperator($operator){
		if($this->hasOperator()){
			$this->release($this->operator);
		}
		return $this->operator = $this->claim($operator);
	}

	public function hasOperator():bool{
		return isset($this->operator);
	}

	public function getOperator(){
		$f = __METHOD__;
		if(!$this->hasOperator()){
			Debug::error("{$f} operator is undefined");
		}elseif($this->getNegateFlag()){
			return static::negateOperator($this->operator);
		}
		return $this->operator;
	}
}
