<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class FloatPrecisionCommand extends StringTransformationCommand
{

	protected $precision;

	public function __construct($subject, $precision)
	{
		parent::__construct($subject);
		$this->setPrecision($precision);
	}

	public function setPrecision($precision)
	{
		return $this->precision = $precision;
	}

	public function hasPrecision()
	{
		return isset($this->precision);
	}

	public function getPrecision()
	{
		$f = __METHOD__; //FloatPrecisionCommand::getShortClass()."(".static::getShortClass().")->getPrecision()";
		if(!$this->hasPrecision()) {
			Debug::error("{$f} precision is undefined");
		}
		return $this->precision;
	}

	public static function getCommandId(): string
	{
		$f = __METHOD__; //FloatPrecisionCommand::getShortClass()."(".static::getShortClass().")->getCommandId()";
		return "toFixed";
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //FloatPrecisionCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		$subject = $this->getSubject();
		if($subject instanceof JavaScriptInterface) {
			$subject = $subject->toJavaScript();
		}
		$precision = $this->getPrecision();
		if($precision instanceof JavaScriptInterface) {
			$precision = $precision->toJavaScript();
		}
		return "{$subject}.toFixed({$precision})";
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //FloatPrecisionCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		$subject = $this->getSubject();
		while ($subject instanceof ValueReturningCommandInterface) {
			$subject = $subject->evaluate();
		}
		if(!is_numeric($subject)) {
			Debug::error("{$f} subject must be numeric");
		}
		$precision = $this->getPrecision();
		while ($precision instanceof ValueReturningCommandInterface) {
			$precision = $precision->evaluate();
		}
		if(!is_int($precision)) {
			Debug::error("{$f} precision must be an integer");
		}elseif($precision < 0) {
			Debug::error("{$f} precision must be a nonnegative integer");
		}
		return number_format((float) $subject, $precision, '.', '');
	}
}
