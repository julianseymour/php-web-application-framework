<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class DateIntervalInput extends CompoundInput
{

	protected $originalName;

	protected $intervalStartInput;

	protected $intervalEndInput;

	public function setOriginalName($name)
	{
		return $this->originalName = $name;
	}

	public function hasOriginalName()
	{
		return isset($this->originalName) && is_string($this->originalName) && !empty($this->originalName);
	}

	public function getOriginalName()
	{
		$f = __METHOD__; //DateIntervalInput::getShortClass()."(".static::getShortClass().")->getOriginalName()";
		if(!$this->hasOriginalName()){
			Debug::error("{$f} original name is undefined");
		}
		return $this->originalName;
	}

	public function hasNameAttribute(): bool
	{
		return $this->hasOriginalName();
	}

	public function getNameAttribute()
	{
		return $this->getOriginalName();
	}

	public function getColumnName(): string
	{
		return $this->getOriginalName();
	}

	public function setNameAttribute($name)
	{
		return $this->setOriginalName($name);
	}

	public function hasIntervalStartInput(): bool
	{
		return isset($this->intervalStartInput) && $this->intervalStartInput instanceof DateInput;
	}

	public function setIntervalStartInput($input)
	{
		return $this->intervalStartInput = $input;
	}

	public function getIntervalStartInput()
	{
		$f = __METHOD__; //DateIntervalInput::getShortClass()."(".static::getShortClass().")->getIntervalStartInput()";
		if(!$this->hasIntervalStartInput()){
			$mode = $this->getAllocationMode();
			$context = $this->getContext();
			$start = new DateInput($mode, $context->getComponent("start"));
			return $this->setIntervalStartInput($start);
		}
		return $this->intervalStartInput;
	}

	public function hasIntervalEndInput()
	{
		return isset($this->intervalEndInput) && $this->intervalEndInput instanceof DateInput;
	}

	public function setIntervalEndInput($input)
	{
		return $this->intervalEndInput = $input;
	}

	public function getIntervalEndInput()
	{
		$f = __METHOD__; //DateIntervalInput::getShortClass()."(".static::getShortClass().")->getIntervalEndInput()";
		if(!$this->hasIntervalEndInput()){
			$mode = $this->getAllocationMode();
			$context = $this->getContext();
			$end = new DateInput($mode, $context->getComponent("end"));
			return $this->setIntervalEndInput($end);
		}
		return $this->intervalEndInput;
	}

	public function generateComponents()
	{
		$f = __METHOD__; //DateIntervalInput::getShortClass()."(".static::getShortClass().")->generateComponents()";
		$start = $this->getIntervalStartInput();
		$end = $this->getIntervalEndInput();
		return [
			$start->getNameAttribute() => $start,
			$end->getNameAttribute() => $end
		];
	}
}
