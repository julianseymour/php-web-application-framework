<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait CallbackTrait
{

	protected $callback;

	public function setCallback($callback)
	{
		return $this->callback = $callback;
	}

	public function getCallback()
	{
		$f = __METHOD__; //"CallbackTrait(".static::getShortClass().")->getCallback()";
		if(!$this->hasCallback()) {
			Debug::error("{$f} callback is undefined");
		}
		return $this->callback;
	}

	public function hasCallback()
	{
		return isset($this->callback);
	}
}
