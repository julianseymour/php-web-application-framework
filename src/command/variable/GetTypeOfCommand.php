<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ValuedTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class GetTypeOfCommand extends Command implements JavaScriptInterface, ValueReturningCommandInterface
{

	use ValuedTrait;

	public function __construct($value)
	{
		parent::__construct();
		$this->setValue($value);
	}

	public function toJavaScript(): string
	{
		$value = $this->getValue();
		if ($value instanceof JavaScriptInterface) {
			$value = $value->toJavaScript();
		}
		return "typeof {$value}";
	}

	public static function getCommandId(): string
	{
		return "gettypeof";
	}

	public function evaluate(?array $params = null)
	{
		$value = $this->getValue();
		while ($value instanceof ValueReturningCommandInterface) {
			$value = $value->evaluate();
		}
		return is_object($value) ? $value->getClass() : gettype($value);
	}
}
