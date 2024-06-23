<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\SymbolicTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;

abstract class Constraint extends Basic implements ReplicableInterface, SQLInterface{

	use ReplicableTrait;
	use SymbolicTrait;

	public function __construct($symbol = null){
		parent::__construct();
		if($symbol !== null){
			$this->setSymbol($symbol);
		}
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasSymbol()){
			$this->setSymbol(replicate($that->getSymbol()));
		}
		return $ret;
	}
	
	public function toSQL(): string{
		if($this->hasSymbol()){
			return "constraint " . $this->getSymbol() . " ";
		}
		return "";
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->symbol, $deallocate);
	}
}
