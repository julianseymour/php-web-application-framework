<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait TypeTrait
{

	protected $type;

	public function setType($type)
	{
		if ($type == null) {
			unset($this->type);
			return null;
		}
		return $this->type = $type;
	}

	public function hasType()
	{
		return isset($this->type);
	}

	public function getType()
	{
		$f = __METHOD__; //"TypeTrait(".static::getShortClass().")->getType()";
		if (! $this->hasType()) {
			Debug::error("{$f} type is undefined");
		}
		return $this->type;
	}

	public function withType($type): object
	{
		$this->setType($type);
		return $this;
	}
}
