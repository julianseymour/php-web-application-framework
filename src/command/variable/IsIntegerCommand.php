<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class IsIntegerCommand extends IsDatatypeCommand
{

	public static function getCommandId(): string
	{
		return "is_int";
	}

	public static function is_type($value)
	{
		return is_int($value);
	}

	public function toJavaScript(): string
	{
		$v = $this->getValue();
		if($v instanceof JavaScriptInterface) {
			$v = $v->toJavaScript();
		}elseif(is_string($v) || $v instanceof StringifiableInterface) {
			$v = single_quote($v);
		}
		$s = "Number.isInteger({$v})";
		if($this->isNegated()) {
			return "!{$s}";
		}
		return $s;
	}
}
