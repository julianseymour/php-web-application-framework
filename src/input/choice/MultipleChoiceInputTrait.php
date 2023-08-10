<?php
namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait MultipleChoiceInputTrait
{

	protected $choiceGenerator;

	public function hasChoiceGenerator(): bool
	{
		return isset($this->choiceGenerator);
	}

	public function getChoiceGenerator()
	{
		$f = __METHOD__; //"MultipleChoiceInputTrait(".static::getShortClass().")->getChoiceGenerator()";
		if (! $this->hasChoiceGenerator()) {
			Debug::error("{$f} choice generator is undefined");
		}
		return $this->choiceGenerator;
	}

	public function setChoiceGenerator($gen)
	{
		if ($gen == null) {
			unset($this->choiceGenerator);
			return null;
		}
		return $this->choiceGenerator = $gen;
	}
}
