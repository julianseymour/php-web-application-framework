<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ValuedTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class SetColumnValueCommand extends ColumnValueCommand implements ServerExecutableCommandInterface
{

	use ValuedTrait;

	public function __construct($context, $vn, $value)
	{
		parent::__construct($context, $vn);
		$this->setValue($value);
	}

	public function resolve()
	{
		$value = $this->getValue();
		while ($value instanceof ValueReturningCommandInterface) {
			$value = $value->evaluate();
		}
		$ds = $this->getDataStructure();
		while($ds instanceof ValueReturningCommandInterface){
			$ds = $ds->evaluate();
		}
		$cn = $this->getColumnName();
		while($cn instanceof ValueReturningCommandInterface){
			$cn = $cn->evaluate();
		}
		return $ds->setColumnValue($cn, $value);
	}

	public static function getCommandId(): string
	{
		return "setColumnValue";
	}

	public function evaluate(?array $params = null)
	{
		return $this->resolve();
	}

	public function toJavaScript(): string
	{
		$e = $this->getIdCommandString();
		if ($e instanceof JavaScriptInterface) {
			$e = $e->toJavaScript();
		}
		$vn = $this->getColumnName();
		if ($vn instanceof JavaScriptInterface) {
			$vn = $vn->toJavaScript();
		} elseif (is_string($vn) || $vn instanceof StringifiableInterface) {
			$vn = single_quote($vn);
		}
		$value = $this->getValue();
		if ($value === null) {
			$value = 'null';
		} elseif ($value instanceof JavaScriptInterface) {
			$value = $value->toJavaScript();
		} elseif (is_string($value) || $value instanceof StringifiableInterface) {
			if ($value === "") {
				$value = '""';
			} else {
				$value = single_quote($value);
			}
		}
		$command = static::getCommandId();
		return "{$e}.{$command}({$vn}, {$value})";
	}
}
