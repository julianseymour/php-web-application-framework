<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

use JulianSeymour\PHPWebApplicationFramework\common\PropertiesTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class Event extends Basic
{

	use PropertiesTrait;

	protected $listenerId;

	protected $eventType;

	protected $target;

	public function __construct($type, $properties = null)
	{
		$this->setEventType($type);
		if (! empty($properties)) {
			foreach ($properties as $key => $value) {
				$this->setProperty($key, $value);
			}
		}
	}

	public function setEventTimestamp($value = null)
	{
		if ($value === null) {
			$value = microtime(true);
		}
		return $this->setProperty("timestamp", $value);
	}

	public function getEventTimestamp()
	{
		return $this->getProperty("timestamp");
	}

	public function setListenerId($id)
	{
		if ($id === null) {
			unset($this->listenerId);
			return null;
		}
		return $this->listenerId = $id;
	}

	public function hasListenerId()
	{
		return isset($this->listenerId);
	}

	public function getListenerId()
	{
		$f = __METHOD__; //Event::getShortClass()."(".static::getShortClass().")->getListenerId()";
		if (! $this->hasListenerId()) {
			Debug::error("{$f} listener ID is undefined");
		}
		return $this->listenerId;
	}

	public function setEventType($type)
	{
		return $this->eventType = $type;
	}

	public function hasEventType()
	{
		return ! empty($this->eventType);
	}

	public function getEventType()
	{
		$f = __METHOD__; //Event::getShortClass()."(".static::getShortClass().")->getEventType()";
		if (! $this->hasEventType()) {
			Debug::error("{$f} event type string is undefined");
		}
		return $this->eventType;
	}

	public function setTarget($target)
	{
		return $this->target = $target;
	}

	public function getTarget()
	{
		$f = __METHOD__; //Event::getShortClass()."(".static::getShortClass().")->getTarget()";
		if (! $this->hasTarget()) {
			Debug::error("{$f} target is undefined");
		}
		return $this->target;
	}

	public function hasTarget()
	{
		return isset($this->target);
	}
}
