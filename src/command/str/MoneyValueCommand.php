<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\common\SymbolicTrait;

class MoneyValueCommand extends StringTransformationCommand{

	use SymbolicTrait;

	public function __construct($symbol=null, $subject=null){
		parent::__construct($subject);
		if($symbol !== null){
			$this->setSymbol($symbol);
		}
	}

	public static function getCommandId(): string{
		return "MoneyValue";
	}

	public function evaluate(?array $params = null){
		$concat = new ConcatenateCommand(
			$this->getSymbol(), 
			new FloatPrecisionCommand($this->getSubject(), 2)
		);
		return $concat->evaluate();
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasSymbol()){
			$this->setSymbol(replicate($that->getSymbol()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->symbol, $deallocate);
	}
}
