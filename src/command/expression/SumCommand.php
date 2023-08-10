<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;

class SumCommand extends ExpressionCommand implements SQLInterface
{

	use ExpressionalTrait;

	public function __construct($e = null)
	{
		parent::__construct();
		if ($e !== null) {
			$this->setExpression($e);
		}
	}

	public static function getCommandId(): string
	{
		return "sum";
	}

	public function audit(): int
	{
		$f = __METHOD__; //SumCommand::getShortClass()."(".static::getShortClass().")->audit()";
		ErrorMessage::unimplemented($f);
	}

	public function evaluate(?array $params = null)
	{
		$sum = 0;
		foreach ($this->getParameters() as $p) {
			if ($p instanceof ValueReturningCommandInterface) {
				while ($p instanceof ValueReturningCommandInterface) {
					$p = $p->evaluate();
				}
			}
			$sum += $p;
		}
		return $sum;
	}

	public function toSQL(): string
	{
		$e = $this->getExpression();
		if ($e instanceof SQLInterface) {
			$e = $e->toSQL();
		}
		return "sum({$e})";
	}
}
