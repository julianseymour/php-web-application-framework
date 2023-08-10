<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ValuedTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class IsDatatypeCommand extends ExpressionCommand implements JavaScriptInterface
{

	use ValuedTrait;

	public abstract static function is_type($value);

	public function __construct($value)
	{
		parent::__construct();
		$this->setValue($value);
	}

	public function evaluate(?array $params = null)
	{
		$value = $this->getValue();
		while ($value instanceof ValueReturningCommandInterface) {
			$value = $value->evaluate();
		}
		$ret = static::is_type($value);
		if ($this->isNegated()) {
			return ! $ret;
		}
		return $ret;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->value);
	}
}
