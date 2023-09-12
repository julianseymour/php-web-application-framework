<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DirectionalityTrait
{

	protected $directionality;

	public function setDirectionality($directionality)
	{
		$f = __METHOD__; //"DirectionalityTrait(".static::getShortClass().")->setDirectionality()";
		if($directionality == null) {
			unset($this->directionality);
			return null;
		}elseif(!is_string($directionality)) {
			Debug::error("{$f} directionality is must be a string");
		}
		$directionality = strtolower($directionality);
		switch ($directionality) {
			case DIRECTION_ASCENDING:
			case DIRECTION_DESCENDING:
				return $this->directionality = $directionality;
			default:
				Debug::error("{$f} invalid directionality \"{$directionality}\"");
		}
	}

	public function hasDirectionality()
	{
		return isset($this->directionality);
	}

	public function getDirectionality()
	{
		$f = __METHOD__; //"DirectionalityTrait(".static::getShortClass().")->getDirectionality()";
		if(!$this->hasDirectionality()) {
			Debug::error("{$f} directionality is undefined");
		}
		return $this->directionality;
	}

	public function asc()
	{
		$this->setDirectionality(DIRECTION_ASCENDING);
		return $this;
	}

	public function desc()
	{
		$this->setDirectionality(DIRECTION_DESCENDING);
		return $this;
	}
}
