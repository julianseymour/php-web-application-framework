<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;


use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

trait MultipleInputsTrait
{

	protected $inputs;

	public function hasInputs(): bool
	{
		return isset($this->inputs) && is_array($this->inputs) && ! empty($this->inputs);
	}

	public function getInputs(): array
	{
		$f = __METHOD__;
		if(!$this->hasInputs()) {
			Debug::error("{$f} inputs are undefined");
		}
		return $this->inputs;
	}

	public function setInputs(?array $inputs): ?array
	{
		if($inputs === null) {
			unset($this->inputs);
			return null;
		}
		return $this->inputs = $inputs;
	}

	public function hasInput(string $name): bool
	{
		return $this->hasInputs() && array_key_exists($name, $this->inputs);
	}

	/**
	 *
	 * @param string $field
	 * @return InputElement
	 */
	public function getInput($field)
	{
		$f = __METHOD__;
		try{
			if(!$this->hasInput($field)) {
				Debug::warning("{$f} input with index \"{$field}\" is undefined");
				Debug::printArray($this->inputs);
				Debug::printStackTrace();
			}
			return $this->inputs[$field];
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}