<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;

class ParseIntegerCommand extends CallFunctionCommand implements ValueReturningCommandInterface
{

	public function __construct($param)
	{
		parent::__construct(static::getCommandId(), $param);
	}

	public static function getCommandId(): string
	{
		return "parseInt";
	}

	public function evaluate(?array $params = null)
	{
		$parameters = $this->getParameters();
		$param = $parameters[0];
		while ($param instanceof ValueReturningCommandInterface) {
			$param = $param->evaluate();
		}
		return intval($param);
	}
}
