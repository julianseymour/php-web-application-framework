<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class NestedSetDatum extends UnsignedIntegerDatum
{

	protected $handedness;

	public function __construct($name)
	{
		parent::__construct($name, 64);
	}

	public function setHandedness($hand)
	{
		$f = __METHOD__; //NestedSetDatum::getShortClass()."(".static::getShortClass().")->setHandedNess()";
		if ($hand == null) {
			unset($this->handedness);
			return null;
		}
		ErrorMessage::unimplemented($f);
		return $this->handedness = $hand;
	}

	public function hasHandedness()
	{
		return isset($this->handedness);
	}

	public function getHandedness()
	{
		$f = __METHOD__; //NestedSetDatum::getShortClass()."(".static::getShortClass().")->getHandedness()";
		if (! $this->hasHandedness()) {
			Debug::error("{$f} handedness is undefined");
		}
		return $this->handedness;
	}

	public function withHandedness($hand)
	{
		$this->setHandedness($hand);
		return $this;
	}
}
