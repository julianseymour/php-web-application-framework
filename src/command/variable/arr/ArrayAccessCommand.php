<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable\arr;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class ArrayAccessCommand extends ArrayCommand implements ValueReturningCommandInterface
{

	protected $offset;

	public function __construct($array, $offset)
	{
		parent::__construct($array);
		$this->setOffset($offset);
	}

	public function setOffset($offset)
	{
		if ($offset === null) {
			unset($this->offset);
			return null;
		}
		return $this->offset = $offset;
	}

	public function hasOffset()
	{
		return isset($this->offset);
	}

	public function getOffset()
	{
		$f = __METHOD__; //ArrayAccessCommand::getShortClass()."(".static::getShortClass().")->getOffset()";
		if (! $this->hasOffset()) {
			Debug::error("{$f} offset is undefined");
		}
		return $this->offset;
	}

	public static function getCommandId(): string
	{
		return "[]";
	}

	public function evaluate(?array $params = null)
	{
		$array = $this->getArray();
		while ($array instanceof ValueReturningCommandInterface) {
			$array = $array->evaluate();
		}
		$offset = $this->getOffset();
		while ($offset instanceof ValueReturningCommandInterface) {
			$offset = $offset->evaluate();
		}
		return $array[$offset];
	}

	public function toJavaScript(): string
	{
		$array = $this->getArray();
		if ($array instanceof JavaScriptInterface) {
			$array = $array->toJavaScript();
		}
		$offset = $this->getOffset();
		if ($offset instanceof JavaScriptInterface) {
			$offset = $offset->toJavaScript();
		}
		return "{$array}[{$offset}]";
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->offset);
	}
}
