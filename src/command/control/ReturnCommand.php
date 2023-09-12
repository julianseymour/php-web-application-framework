<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use function JulianSeymour\PHPWebApplicationFramework\is_associative;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class ReturnCommand extends Command implements JavaScriptInterface, ServerExecutableCommandInterface, SQLInterface, ValueReturningCommandInterface
{

	protected $returnValue;

	public static function getCommandId(): string
	{
		return "return";
	}

	public function __construct($returnValue = null)
	{
		parent::__construct();
		if(isset($returnValue)) {
			$this->setReturnValue($returnValue);
		}
	}

	public function hasReturnValue()
	{
		return ! empty($this->returnValue);
	}

	public function setReturnValue($returnValue)
	{
		return $this->returnValue = $returnValue;
	}

	public function getReturnValue()
	{
		$f = __METHOD__; //ReturnCommand::getShortClass()."(".static::getShortClass().")->getReturnValue()";
		if(!$this->hasReturnValue()) {
			Debug::error("{$f} return value is undefined");
		}
		return $this->returnValue;
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //ReturnCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		$string = "return";
		if($this->hasReturnValue()) {
			$rv = $this->getReturnValue();
			if($rv instanceof JavaScriptInterface) {
				$rv = $rv->toJavaScript();
			}elseif(is_string($rv) || $rv instanceof StringifiableInterface) {
				$rv = single_quote($rv);
			}elseif(is_array($rv)) {
				$string .= " [";
				$i = 0;
				foreach($rv as $key => $value) {
					if($i ++ > 0) {
						$string .= ",";
					}
					$string .= " ";
					if(is_associative($rv)) {
						$string .= single_quote($key) . "=> ";
					}
					if($value instanceof JavaScriptInterface) {
						$value = $value->toJavaScript();
					}elseif(is_string($value)) {
						$value = single_quote($value);
					}
					$string .= $value;
				}
				$string .= "]";
				return $string;
				Debug::error("{$f} unimplemented: return an array value");
			}
			$string .= " " . $rv;
		}
		return $string;
	}

	public function toSQL(): string
	{
		$string = "return";
		if($this->hasReturnValue()) {
			$rv = $this->getReturnValue();
			if($rv instanceof SQLInterface) {
				$rv = $rv->toSQL();
			}elseif(is_string($rv) || $rv instanceof StringifiableInterface) {
				$rv = single_quote($rv);
			}
			$string .= " " . $rv;
		}
		$string .= ";\n";
		return $string;
	}

	public function resolve()
	{
		$f = __METHOD__; //ReturnCommand::getShortClass()."(".static::getShortClass().")->resolve()";
		try{
			$value = $this->getReturnValue();
			if($value instanceof ServerExecutableCommandInterface) {
				$value->resolve();
			}
			if($value instanceof ValueReturningCommandInterface) {
				$value = $value->evaluate();
			}
			return $value;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->returnValue);
	}

	public function evaluate(?array $params = null)
	{
		return $this->resolve();
	}
}
