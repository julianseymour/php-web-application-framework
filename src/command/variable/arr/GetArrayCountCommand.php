<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable\arr;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class GetArrayCountCommand extends ArrayCommand implements ValueReturningCommandInterface
{

	public static function getCommandId(): string
	{
		return "length";
	}

	public function evaluate(?array $params = null)
	{
		$array = $this->getArray();
		while($array instanceof ValueReturningCommandInterface){
			$array = $array->evaluate();
		}
		return count($array);
	}

	public function toJavaScript(): string
	{
		$array = $this->getArray();
		if($array instanceof JavaScriptInterface){
			$array = $array->toJavaScript();
		}
		return "{$array}.length";
	}
}
