<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait EngineAttributeTrait
{

	protected $engineAttributeString;

	public function setEngineAttribute($attr)
	{
		return $this->engineAttributeString = $attr;
	}

	public function hasEngineAttribute()
	{
		return isset($this->engineAttributeString);
	}

	public function getEngineAttribute()
	{
		$f = __METHOD__; //"EngineAttributeTrait(".static::getShortClass().")->getEngineAttribute()";
		if(!$this->hasEngineAttribute()) {
			Debug::error("{$f} engine attribute is undefined");
		}
		return $this->engineAttributeString;
	}

	public function engineAttribute($attr)
	{
		$this->setEngineAttribute($attr);
		return $this;
	}
}