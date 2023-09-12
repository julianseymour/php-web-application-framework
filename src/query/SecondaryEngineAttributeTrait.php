<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait SecondaryEngineAttributeTrait
{

	use EngineAttributeTrait;

	protected $secondaryEngineAttributeString;

	public function setSecondaryEngineAttribute($attr)
	{
		return $this->secondaryEngineAttributeString = $attr;
	}

	public function hasSecondaryEngineAttribute()
	{
		return isset($this->secondaryEngineAttributeString);
	}

	public function getSecondaryEngineAttribute()
	{
		$f = __METHOD__; //"SecondaryEngineAttributeTrait(".static::getShortClass().")->getSecondaryEngineAttribute()";
		if(!$this->hasSecondaryEngineAttribute()) {
			Debug::error("{$f} secondary engine attribute is undefined");
		}elseif(!$this->hasEngineAttribute()) {
			Debug::error("{$f} please define primary engine attribute before asking for the secondary");
		}
		return $this->secondaryEngineAttributeString;
	}

	public function secondaryEngineAttribute($attr)
	{
		$this->setSecondaryEngineAttribute($attr);
		return $this;
	}
}
