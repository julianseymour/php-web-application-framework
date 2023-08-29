<?php
namespace JulianSeymour\PHPWebApplicationFramework\json;

use function JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\DisposableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

abstract class Json
{

	/**
	 * Equivalent to echo(json_encode($value)) but saves memory with EchoJsonInterface
	 *
	 * @param mixed $value
	 * @param bool $destroy
	 * @param bool $comma
	 */
	public static function echo($value, bool $destroy = false, bool $comma = true){
		$f = __METHOD__; //"Json::echo()";
		try {
			$print = false;
			if (is_object($value)) {
				if ($print) {
					Debug::print("{$f} value is an object");
				}
				if ($value instanceof EchoJsonInterface) {
					if (! $value->getAllocatedFlag()) {
						$class = $value->getClass();
						if (app()->getFlag("debug")) {
							$did = $value->getDebugId();
							$decl = $value->hasDeclarationLine() ? $value->getDeclarationLine() : "unknown";
							Debug::error("{$f} {$class} with debug ID \"{$did}\" declared {$decl} has already been deallocated");
						} else {
							Debug::error("{$f} object of class \"{$class}\" has already been deallocated");
						}
					}
					$value->echoJson($destroy);
				} else {
					echo json_encode($value);
				}
				if ($destroy && $value instanceof DisposableInterface) {
					$value->dispose();
				}
			} elseif (is_string($value)) {
				if ($print) {
					Debug::print("{$f} value is a string");
				}
				echo json_encode($value);
			} elseif (is_array($value)) {
				if ($print) {
					Debug::print("{$f} value is an array");
				}
				if (empty($value)) {
					if($print){
						Debug::print("{$f} no empty arrays please");
					}
					echo "{}";
					if ($comma) {
						echo ",";
					}
					return;
				}
				$keys = array_keys($value);
				$key0 = $keys[0];
				echo is_int($key0) ? "[" : "{";
				// copied and pasted from //Json::echoArray($value, $destroy, $comma);
				$i = 0;
				foreach ($value as $key => $subvalue) {
					if (is_object($subvalue) && $subvalue instanceof EchoJsonInterface && $subvalue->skipJson()) {
						continue;
					} elseif ($i ++ > 0) {
						echo ",";
					}
					if (is_int($key)) {
						Json::echo($subvalue, $destroy, false);
					} elseif (is_string($key)) {
						Json::echoKeyValuePair($key, $subvalue, $destroy, false);
					} else {
						Debug::error("{$f} key is neither numeric or string");
					}
				}
				/*if ($comma) {
					echo ",";
				}*///What is this doing up here
				echo is_int($key0) ? "]" : "}";
				if ($comma) {
					echo ",";
				}
			} elseif (is_bool($value)) {
				if ($print) {
					Debug::print("{$f} value is boolean");
				}
				echo $value ? "true" : "false";
			} elseif (is_int($value) || is_float($value) || is_double($value)) {
				if ($print) {
					Debug::print("{$f} value is numeric");
				}
				echo $value;
			} elseif ($value === null) {
				if ($print) {
					Debug::print("{$f} value is null");
				}
				echo "null";
			} else {
				$gottype = gettype($value);
				Debug::error("{$f} value of type \"{$gottype}\" is not an object, number, boolean, null, string or array");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * echo a key-value pair directly to Json
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @param bool $destroy
	 * @param bool $comma
	 */
	public static function echoKeyValuePair($key, $value, bool $destroy = false, bool $comma = true){
		$f = __METHOD__; //"Json::echoKeyValuePair()";
		try {
			if(is_array($value) && empty($value)){
				Debug::warning("{$f} empty array received for key \"{$key}\"");
			}
			//do not elseif
			if (is_string($key)) {
				Json::echo($key, $destroy, false);
				echo ":";
			} elseif (! is_int($key)) {
				Debug::error("{$f} array keys must be numeric or string");
			}
			Json::echo($value, $destroy, false);
			if ($comma) {
				echo ",";
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * echo an array directly to Json without encoding it
	 *
	 * @param array $arr
	 *        	: array to echo
	 * @param boolean $destroy
	 *        	: if true, destroy the array after we're done with it
	 * @param boolean $comma
	 *        	: if true, echo a trailing comma afterward
	 */
	public static function echoArray(array $arr, bool $destroy = false, bool $comma = true){
		$f = __METHOD__; //"Json::echoArray()";
		try {
			Debug::error("{$f} disabled to reduce execution depth");
			$i = 0;
			foreach ($arr as $key => $subvalue) {
				if (is_object($subvalue) && $subvalue instanceof EchoJsonInterface && $subvalue->skipJson()) {
					continue;
				} elseif ($i ++ > 0) {
					echo ",";
				}
				if (is_int($key)) {
					Json::echo($subvalue, $destroy, false);
				} elseif (is_string($key)) {
					Json::echoKeyValuePair($key, $subvalue, $destroy, false);
				} else {
					Debug::error("{$f} key is neither numeric or string");
				}
			}
			if ($comma) {
				echo ",";
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
