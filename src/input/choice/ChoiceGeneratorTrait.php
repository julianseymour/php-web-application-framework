<?php
namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use Closure;

trait ChoiceGeneratorTrait{

	protected $choiceGenerator;

	public function setChoiceGenerator($generator){
		if ($generator === null) {
			unset($this->choiceGenerator);
			return null;
		}
		return $this->choiceGenerator = $generator;
	}

	public function hasChoiceGenerator(): bool{
		return isset($this->choiceGenerator);
	}

	public function getChoiceGenerator(){
		$f = __METHOD__;
		if (! $this->hasChoiceGenerator()) {
			if ($this instanceof Datum) {
				$cn = $this->getColumnName();
				$dsc = $this->getDataStructureClass();
				Debug::error("{$f} choice generator is undefined for column \"{$cn}\"; data structure class is \"{$dsc}\"");
			}
			Debug::error("{$f} choice generator is undefined");
		}
		return $this->choiceGenerator;
	}

	public function generateChoices($context): ?array{
		$f = __METHOD__;
		$generator = $this->getChoiceGenerator();
		if (is_object($generator) && $generator instanceof ChoiceGeneratorInterface) {
			return $generator->generateChoices($context);
		} elseif ($generator instanceof Closure) {
			return $generator($context);
		} elseif (is_string($generator) && class_exists($generator)) {
			return $generator::generateChoicesStatic($context);
		}
		Debug::error("{$f} none of the above");
	}
}
