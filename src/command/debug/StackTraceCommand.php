<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\debug;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class StackTraceCommand extends LogCommand
{

	public static function getCommandId(): string
	{
		return "console.trace";
	}

	public function toJavaScript(): string
	{
		$s = "";
		if($this->hasMessage()){
			$msg = $this->getMessage();
			if($msg instanceof JavaScriptInterface){
				$msg = $msg->toJavaScript();
			}elseif(is_string($msg) || $msg instanceof StringifiableInterface){
				$msg = single_quote($msg);
			}
			$s .= $msg;
		}
		$s .= $this->getCommandId() . "()";
		return $s;
	}

	public function resolve()
	{
		if($this->hasMessage()){
			$msg = $this->getMessage();
			while($msg instanceof ValueReturningCommandInterface){
				$msg = $msg->evaluate();
			}
		}else{
			$msg = null;
		}
		Debug::printStackTraceNoExit($msg);
	}
}
