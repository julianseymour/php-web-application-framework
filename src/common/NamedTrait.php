<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait NamedTrait
{

	protected $name;

	public function setName($name)
	{
		$f = __METHOD__; //"NamedTrait(".static::getShortClass().")->setName()";
		if ($name == null) {
			unset($this->name);
			return null;
		} elseif (! is_string($name) && ! $name instanceof ValueReturningCommandInterface) {
			Debug::error("{$f} name must be a string or value-returning command");
		}
		return $this->name = $name;
	}

	public function hasName()
	{
		return isset($this->name) && ! empty($this->name);
	}

	public function getName()
	{ // note to self: if you declare a return type of string for a function, and the function returns somerhing that has a __toString() method, the function will return its string conversion
		$f = __METHOD__; //"NamedTrait(".static::getShortClass().")->getName()";
		if (! $this->hasName()) {
			Debug::error("{$f} name is undefined");
		}
		return $this->name;
	}

	public function named($name)
	{
		$this->setName($name);
		return $this;
	}
}
