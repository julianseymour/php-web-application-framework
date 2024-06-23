<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\PrecisionTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class FloatPrecisionCommand extends StringTransformationCommand{

	use PrecisionTrait;
	
	public function __construct($subject=null, $precision=null){
		parent::__construct($subject);
		if($precision !== null){
			$this->setPrecision($precision);
		}
	}

	public static function getCommandId(): string{
		$f = __METHOD__;
		return "toFixed";
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		$subject = $this->getSubject();
		if($subject instanceof JavaScriptInterface){
			$subject = $subject->toJavaScript();
		}
		$precision = $this->getPrecision();
		if($precision instanceof JavaScriptInterface){
			$precision = $precision->toJavaScript();
		}
		return "{$subject}.toFixed({$precision})";
	}

	public function evaluate(?array $params = null){
		$f = __METHOD__;
		$subject = $this->getSubject();
		while($subject instanceof ValueReturningCommandInterface){
			$subject = $subject->evaluate();
		}
		if(!is_numeric($subject)){
			Debug::error("{$f} subject must be numeric");
		}
		$precision = $this->getPrecision();
		while($precision instanceof ValueReturningCommandInterface){
			$precision = $precision->evaluate();
		}
		if(!is_int($precision)){
			Debug::error("{$f} precision must be an integer");
		}elseif($precision < 0){
			Debug::error("{$f} precision must be a nonnegative integer");
		}
		return number_format((float) $subject, $precision, '.', '');
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasPrecision()){
			$this->setPrecition(replicate($that->getPrecision()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->precision, $deallocate);
	}
}
