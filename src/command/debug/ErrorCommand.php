<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\debug;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class ErrorCommand extends LogCommand
{

	public static function getCommandId(): string
	{
		return "console.error";
	}

	public function resolve()
	{
		$f = __METHOD__; //ErrorCommand::getShortClass()."(".static::getShortClass().")->resolve()";
		$msg = $this->getMessage();
		while($msg instanceof ValueReturningCommandInterface){
			$msg = $msg->evaluate();
		}
		Debug::error($msg);
	}
}
