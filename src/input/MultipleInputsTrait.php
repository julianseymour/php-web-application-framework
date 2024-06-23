<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseInputEvent;
use Exception;

trait MultipleInputsTrait{

	protected $inputs;

	public function hasInputs():bool{
		return isset($this->inputs) && is_array($this->inputs) && !empty($this->inputs);
	}

	public function getInputs(): array{
		$f = __METHOD__;
		if(!$this->hasInputs()){
			Debug::error("{$f} inputs are undefined");
		}
		return $this->inputs;
	}

	public function setInputs(?array $inputs): ?array{
		if($this->hasInputs()){
			$this->releaseInputs();
		}
		return $this->inputs = $this->claim($inputs);
	}

	public function releaseInputs(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasInputs()){
			Debug::error("{$f} no inputs to release");
		}
		foreach(array_keys($this->getInputs()) as $index){
			$this->releaseInput($index, $deallocate);
		}
	}
	
	public function hasInput(string $name): bool{
		return $this->hasInputs() && array_key_exists($name, $this->inputs);
	}

	public function releaseInput($index, bool $deallocate=false){
		$f = __METHOD__;
		$print = false;
		if(!$this->hasInput($index)){
			Debug::warning("{$f} input {$index} is undefined. The following is a valid list of indices:");
			Debug::printArray($this->inputs);
			Debug::printStackTrace();
		}
		$input = $this->getInput($index);
		unset($this->inputs[$index]);
		if(empty($this->inputs)){
			unset($this->inputs);
		}
		if($this->hasAnyEventListener(EVENT_RELEASE_INPUT)){
			$this->dispatchEvent(new ReleaseInputEvent($index, $input, $deallocate));
		}
		if($print){
			Debug::print("{$f} about to release input {$index}, which is a ".$input->getDebugString());
		}
		$this->release($input, $deallocate);
	}
	
	/**
	 *
	 * @param string $field
	 * @return InputElement
	 */
	public function getInput($field){
		$f = __METHOD__;
		try{
			if(!$this->hasInput($field)){
				Debug::warning("{$f} input with index \"{$field}\" is undefined");
				Debug::printArray($this->inputs);
				Debug::printStackTrace();
			}
			return $this->inputs[$field];
		}catch(Exception $x){
			x($f, $x);
		}
	}
}