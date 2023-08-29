<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;


use function JulianSeymour\PHPWebApplicationFramework\is_associative;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\image\ImageData;

trait FlagBearingTrait
{

	/**
	 * variable for packing binary flags into a single integer to save space
	 *
	 * @var int
	 */
	protected $flags;

	/**
	 * associative array for storing undeclared flags; wastes memory
	 *
	 * @var array
	 */
	protected $undeclaredFlags;

	/**
	 * return an integer-indexed list of flag names in the order they are packed
	 *
	 * @return array
	 */
	public abstract static function declareFlags();

	private static function getBinaryFlag(string $name)
	{
		$f = __METHOD__; //"FlagBearingTrait(".static::getShortClass().")::getBinaryFlag()";
		$print = false;
		$declarations = static::declareFlags();
		$exp = array_search($name, $declarations);
		if (false === $exp) {
			if (empty($declarations)) {
				Debug::print("{$f} flag declarations are empty");
			} elseif ($print) {
				Debug::printArray($declarations);
				Debug::print("{$f} invalid flag name \"{$name}\"");
			}
			return - 1;
		}
		return pow(2, $exp);
	}

	public function clearFlags()
	{
		$f = __METHOD__; //f(FlagBearingTrait::class);
		$print = false;
		if ($print) {
			foreach (static::declareFlags() as $flag) {
				if ($this->getFlag($flag)) {
					Debug::print("{$f} clearing flag \"{$flag}\"");
				}
			}
		}
		unset($this->flags);
		unset($this->undeclaredFlags);
	}

	public function getFlag(string $name): bool{
		$f = __METHOD__;
		$print = false;
		$binary = static::getBinaryFlag($name);
		if ($binary < 0) {
			if ($print) {
				Debug::print("{$f} flag \"{$name}\" is undeclared");
			}
			return $this->getUndeclaredFlag($name);
		} elseif (! isset($this->flags) || ! is_int($this->flags)) {
			if ($print) {
				Debug::print("{$f} flags are undefined; returning false");
			}
			return false;
		} elseif ($print) {
			Debug::print("{$f} int value of flag \"{$name}\" is {$binary}");
			$after = $binary & $this->flags;
			Debug::print("{$f} once applied to flags, value is {$after}");
			if ($after === $binary) {
				Debug::print("{$f} yes, {$after} === {$binary}");
			} else {
				Debug::print("{$f} no, {$after} !== {$binary}");
			}
		}
		return ($binary & $this->flags) === $binary;
	}

	private function getUndeclaredFlag(string $name): bool{
		$f = __METHOD__;
		$print = false;
		if (! UNDECLARED_FLAGS_ENABLED) {
			Debug::error("{$f} undeclared flag \"{$name}\"");
		}
		if (! isset($this->undeclaredFlags) || ! is_array($this->undeclaredFlags)) {
			if ($print) {
				Debug::print("{$f} flags is not an array");
			}
			return false;
		}
		return array_key_exists($name, $this->undeclaredFlags) && $this->undeclaredFlags[$name] === true;
	}

	public function toggleFlag(string $name){
		if (! isset($this->flags) || ! is_int($this->flags)) {
			return $this->setFlag($name, true);
		}
		$this->flags = $this->flags ^ static::getBinaryFlag($name);
		return $this->getFlag($name);
	}

	public function setFlags(array $flags){
		$f = __METHOD__;
		if (! is_array($flags)) {
			Debug::error("{$f} oi fuck off");
		}
		foreach ($flags as $name => $value) {
			if (is_int($name) && is_string($value)) {
				$this->setFlag($value, true);
			} elseif (is_string($name)) {
				$this->setFlag($name, $value);
			}
		}
	}

	private function setUndeclaredFlag(string $name, bool $value): bool{
		$f = __METHOD__;
		if (! UNDECLARED_FLAGS_ENABLED) {
			Debug::error("{$f} undeclared flag \"{$name}\"");
		}
		$value = boolval($value);
		if (! isset($this->undeclaredFlags) || ! is_array($this->undeclaredFlags)) {
			$this->undeclaredFlags = [];
		}
		return $this->undeclaredFlags[$name] = $value;
	}

	public function setFlag(string $name, bool $value = true): bool{ // value must be mixed because foreign key columns accept arrays
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::printStackTraceNoExit("{$f} entered");
		}
		$binary = static::getBinaryFlag($name);
		if ($binary < 0) {
			return $this->setUndeclaredFlag($name, $value);
		}
		if (! isset($this->flags) || ! is_int($this->flags)) {
			$this->flags = 0;
		}
		if ($print) {
			Debug::print("{$f} binary version of flag is {$binary}");
		}
		if ($value) {
			if ($print) {
				Debug::print("{$f} before setting flag \"{$name}\", flags value is {$this->flags}");
			}
			$this->flags = $this->flags | $binary;
			if (! $this->getFlag($name)) {
				Debug::error("{$f} flag is undefined in value {$this->flags} immediately after setting it");
			}
			return true;
		}
		$this->flags = $this->flags & ~ $binary;
		return false;
	}

	public function hasFlags(): bool
	{
		return isset($this->flags) && is_int($this->flags) && $this->flags > 0;
	}

	public function withFlag(string $name, bool $value = true): object
	{
		$this->setFlag($name, $value);
		return $this;
	}

	public function withFlags(array $keyvalues): object
	{
		$f = __METHOD__; //"FlagBearingTrait(".static::getShortClass().")->withFlags()";
		if (is_array($keyvalues) && ! empty($keyvalues) && is_associative($keyvalues)) {
			foreach ($keyvalues as $key => $value) {
				$this->setFlag($key, $value);
			}
		} else {
			Debug::error("{$f} received a parameter that is either non-array, empty or non-associative");
		}
		return $this;
	}
}
