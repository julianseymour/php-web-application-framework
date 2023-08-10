<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait VisibilityTrait
{

	protected $visibility;

	public function setVisibility($v)
	{
		$f = __METHOD__; //"VisibilityTrait(".static::getShortClass().")->setVisibility()";
		if ($v === null) {
			return $this->visibility = null;
		} elseif (! is_string($v)) {
			Debug::error("{$f} visiblity must be a string");
		}
		$v = strtolower($v);
		switch ($v) {
			case VISIBILITY_VISIBLE:
			case VISIBLITY_INVISIBLE:
				break;
			default:
				Debug::error("{$f} invalid visibility \"{$v}\"");
		}
		return $this->visibility = $v;
	}

	public function hasVisibility()
	{
		return isset($this->visibility);
	}

	public function getVisibility()
	{
		$f = __METHOD__; //"VisibilityTrait(".static::getShortClass().")->getVisibility()";
		if (! $this->hasVisibility()) {
			Debug::error("{$f} visibility is undefined");
		}
		return $this->visibility;
	}
}
