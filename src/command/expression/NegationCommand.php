<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class NegationCommand extends Command implements JavaScriptInterface, ServerExecutableCommandInterface
{

	use ExpressionalTrait;

	public function __construct($ex)
	{
		parent::__construct();
		if ($ex !== null) {
			$this->setExpression($ex);
		}
	}

	public static function getCommandId(): string
	{
		return "not";
	}

	public function evaluate(?array $params = null)
	{
		$expr = $this->getExpression();
		while ($expr instanceof ValueReturningCommandInterface) {
			$expr = $expr->evaluate($params);
		}
		return !boolval($expr);
	}

	public function resolve()
	{
		return $this->evaluate();
	}

	public function toJavaScript(): string
	{
		$expr = $this->getExpression();
		if ($expr instanceof JavaScriptInterface) {
			$expr = $expr->toJavaScript();
		}
		return "!{$expr}";
	}

	public function negate()
	{
		return $this->getExpression();
	}
}
