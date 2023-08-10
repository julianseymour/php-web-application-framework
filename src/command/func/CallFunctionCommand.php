<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\func;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

class CallFunctionCommand extends InvokeFunctionCommand implements ValueReturningCommandInterface
{

	public static function getCommandId(): string
	{
		return "callFunction";
	}

	public function evaluate(?array $params = null)
	{
		return $this->toJavaScript();
	}
}
