<?php
namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\array_remove_keys;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use Exception;

class Registry extends Basic
{

	/**
	 * contains unique keys referencing every object that gets loaded
	 *
	 * @var array
	 */
	protected $globalKeyMap;

	public function deregister(...$keys)
	{
		$f = __METHOD__; //Registry::getShortClass()."(".static::getShortClass().")->deregister()";
		if (is_array($this->globalKeyMap)) {
			$this->globalKeyMap = array_remove_keys($this->globalKeyMap, ...$keys); // $this->globalKeyMap[$key] = null;
			return SUCCESS;
		}
		Debug::warning("{$f} nothing mappped");
		return ERROR_ALREADY_DELETED;
	}

	public function update($key, $object)
	{
		$f = __METHOD__; //Registry::getShortClass()."(".static::getShortClass().")->update()";
		$print = false;
		if (! $object->isRegistrable()) {
			Debug::error("{$f} object is not registrable");
		} elseif (! is_int($key) && ! is_string($key)) {
			$gottype = gettype($key);
			Debug::error("{$f} key is a \"{$gottype}\"");
		} elseif (empty($key)) {
			Debug::error("{$f} empty key");
		} elseif (! isset($object)) {
			Debug::error("{$f} object is null; use deregister({$key}) to unmap");
		} elseif (! is_array($this->globalKeyMap)) {
			$this->globalKeyMap = [];
		}
		if ($print) {
			$dsc = $object->getClass();
			Debug::printStackTraceNoExit("{$f} mapping a {$dsc} to key \"{$key}\"");
		}
		return $this->globalKeyMap[$key] = $object;
	}

	public function setGlobalKeyMap(&$map)
	{
		$f = __METHOD__; //Registry::getShortClass()."(".static::getShortClass().")->setGlobalKeyMap()";
		Debug::error("{$f} entered");
		return $this->globalKeyMap = $map;
	}

	public function register($object)
	{
		$key = $object->getIdentifierValue();
		return $this->registerObjectToKey($key, $object);
	}

	public function registerObjectToKey($key, $object)
	{
		$f = __METHOD__; //Registry::getShortClass()."(".static::getShortClass().")->registerObjectToKey()";
		try {
			$print = false;
			if (empty($key)) {
				Debug::error("{$f} empty key");
			} elseif (! isset($object)) {
				Debug::error("{$f} object is null; use deregister({$key}) to unmap");
			} elseif (! is_array($this->globalKeyMap)) {
				$this->globalKeyMap = [];
			} elseif (array_key_exists($key, $this->globalKeyMap)) {
				$decl1 = $this->globalKeyMap[$key]->getDeclarationLine();
				$did1 = $this->globalKeyMap[$key]->getDebugId();
				$decl2 = $object->getDeclarationLine();
				$did2 = $object->getDebugId();
				Debug::error("{$f} something with debug ID {$did1} was already mapped to key \"{$key}\", and it was declared {$decl1}. The new object has debug ID {$did2} and was declared {$decl2}");
			}
			if ($print) {
				$dsc = $object->getClass();
				Debug::printStackTraceNoExit("{$f} mapping a {$dsc} to key \"{$key}\"");
			}
			return $this->globalKeyMap[$key] = $object;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasObjectRegisteredToKey($key): bool
	{
		$f = __METHOD__; //Registry::getShortClass()."(".static::getShortClass().")->hasObjectRegisteredToKey()";
		$print = false;
		if (! is_int($key) && ! is_string($key)) {
			$gottype = gettype($key);
			Debug::error("{$f} key is a \"{$gottype}\"");
		} elseif ($print) {
			if (isset($this->globalKeyMap)) {
				if (is_array($this->globalKeyMap)) {
					if (array_key_exists($key, $this->globalKeyMap)) {
						if ($this->globalKeyMap[$key] !== null) {
							Debug::print("{$f} value mapped to key \"{$key}\" is not null");
						} else {
							Debug::print("{$f} value mapped to key \"{$key}\" is null");
						}
					} else {
						Debug::print("{$f} key \"{$key}\" is not mapped to anything");
					}
				} else {
					Debug::print("{$f} global key map is not an array");
				}
			} else {
				Debug::print("{$f} global key map is undefined");
			}
		}
		return isset($this->globalKeyMap) && is_array($this->globalKeyMap) && array_key_exists($key, $this->globalKeyMap) && $this->globalKeyMap[$key] !== null;
	}

	public function has($key): bool{
		return $this->hasObjectRegisteredToKey($key);
	}

	public function getGlobalKeyMap(){
		return $this->globalKeyMap;
	}

	public function getRegisteredObjectFromKey($key): ?DataStructure{
		$f = __METHOD__;
		try {
			$print = false;
			if ($key === "N/A") {
				Debug::error("{$f} not applicable");
			} elseif ($this->hasObjectRegisteredToKey($key)) {
				return $this->globalKeyMap[$key];
			}
			if($print){
				Debug::warning("{$f} key \"{$key}\" is not mapped");
			}
			return null;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function get($key): ?DataStructure{
		return $this->getRegisteredObjectFromKey($key);
	}

	public function hasGlobalKeyMap(): bool{
		return isset($this->globalKeyMap) && is_array($this->globalKeyMap);
	}
}
