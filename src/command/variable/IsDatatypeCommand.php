<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ValuedTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class IsDatatypeCommand extends ExpressionCommand implements JavaScriptInterface{

	use ValuedTrait;

	public abstract static function is_type($value);

	public function __construct($value=null){
		parent::__construct();
		if($value !== null){
			$this->setValue($value);
		}
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasValue()){
			$this->setValue(replicate($that->getValue()));
		}
		return $ret;
	}
	
	public function evaluate(?array $params = null){
		$value = $this->getValue();
		while($value instanceof ValueReturningCommandInterface){
			$value = $value->evaluate();
		}
		$ret = static::is_type($value);
		if($this->getNegateFlag()){
			return ! $ret;
		}
		return $ret;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->value, $deallocate);
	}
}
