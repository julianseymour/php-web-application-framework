<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable\arr;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class ArrayCommand extends Command implements JavaScriptInterface
{

	protected $array;

	public function __construct($array)
	{
		parent::__construct($array);
		$this->setArray($array);
	}

	public function setArray($array)
	{
		if ($array === null) {
			unset($this->array);
			return null;
		}
		return $this->array = $array;
	}

	public function hasArray()
	{
		return isset($this->array);
	}

	public function getArray()
	{
		$f = __METHOD__; //ArrayCommand::getShortClass()."(".static::getShortClass().")->getArray()";
		if (! $this->hasArray()) {
			Debug::error("{$f} array is undefined");
		}
		return $this->array;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->array);
	}
}
