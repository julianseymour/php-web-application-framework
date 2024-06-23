<?php

namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait MultipleChoiceInputTrait{

	protected $choiceGenerator;

	public function hasChoiceGenerator(): bool{
		return isset($this->choiceGenerator);
	}

	public function getChoiceGenerator(){
		$f = __METHOD__;
		if(!$this->hasChoiceGenerator()){
			Debug::error("{$f} choice generator is undefined");
		}
		return $this->choiceGenerator;
	}

	public function setChoiceGenerator($gen){
		if($this->hasChoiceGenerator()){
			$this->release($this->choiceGenerator);
		}
		return $this->choiceGenerator = $this->claim($gen);
	}
}
