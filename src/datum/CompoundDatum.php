<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

abstract class CompoundDatum extends Datum
{

	protected $components;

	public abstract function generateComponents();

	public function __construct($name)
	{
		parent::__construct($name);
		$this->components = $this->generateComponents();
	}

	public function getComponents()
	{
		return $this->components;
	}

	public function hasComponent($component_name)
	{
		return is_array($this->components) && array_key_exists($component_name, $this->components) && is_object($this->components[$component_name]);
	}

	public function getComponent($component_name)
	{
		$f = __METHOD__; //CompoundDatum::getShortClass()."(".static::getShortClass().")->getComponent({$component_name})";
		if (! $this->hasComponent($component_name)) {
			Debug::error("{$f} component \"{$component_name}\" is undefined");
		}
		return $this->components[$component_name];
	}

	public function parseValueFromSuperglobalArray($value)
	{
		$f = __METHOD__; //CompoundDatum::getShortClass()."(".static::getShortClass().")->parseValueFromSuperglobalArray()";
		ErrorMessage::unimplemented($f);
	}

	public function processInput($input)
	{
		$f = __METHOD__; //CompoundDatum::getShortClass()."(".static::getShortClass().")->processInput()";
		$vn = $this->getColumnName();
		foreach ($this->getComponents() as $component_name => $component) {
			$component_input = $input->getComponent($component_name);
			$status = $component->processInput($component_input);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				$cvn = $component->getColumnName();
				Debug::print("{$f} processInput for index \"{$cvn}\" returned error status \"{$err}\"");
			}
		}
		return SUCCESS;
	}

	public static function getTypeSpecifier()
	{
		$f = __METHOD__; //CompoundDatum::getShortClass()."(".static::getShortClass().")::getTypeSpecifier()";
		ErrorMessage::unimplemented($f);
	}

	public function getConstructorParams(): ?array
	{
		return [
			$this->getColumnName()
		];
	}

	public function parseValueFromQueryResult($raw)
	{
		$f = __METHOD__; //CompoundDatum::getShortClass()."(".static::getShortClass().")->parseValueFromQueryResult()";
		ErrorMessage::unimplemented($f);
	}

	public function getComponentValue($index)
	{
		$f = __METHOD__; //CompoundDatum::getShortClass()."(".static::getShortClass().")->getComponentValue({$index})";
		if (! $this->hasComponent($index)) {
			Debug::error("{$f} component \"{$index}\" does not exist");
		}
		return $this->getComponent($index)->getValue();
	}

	public function setComponentValue($index, $value)
	{
		$f = __METHOD__; //CompoundDatum::getShortClass()."(".static::getShortClass().")->setComponentValue({$index}, ~)";
		if (! $this->hasComponent($index)) {
			Debug::error("{$f} component at index \"{$index}\" does not exist");
		}
		return $this->getComponent($index)->setValue($value);
	}

	public function hasComponentValue($index)
	{
		return $this->hasComponent($index) && $this->getComponent($index)->hasValue();
	}
}
