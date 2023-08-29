<?php

namespace JulianSeymour\PHPWebApplicationFramework\core;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\debug;
use function JulianSeymour\PHPWebApplicationFramework\getExecutionTime;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\ApplicationRuntime;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use Exception;
use ReflectionClass;

abstract class Debug extends Basic{

	public static function error($what){
		$f = __METHOD__;
		try {
			error_log("\033[31mError: {$what}\033[0m", 0);
			Debug::limitExecutionDepth(512);
			Debug::resetDebugCounterStatic();
			if (isset($_SESSION['elements'])) {
				Debug::print("{$f} constructed {$_SESSION['elements']} elements");
			}
			Debug::resetElementConstructorCount();
			Debug::printStackTraceNoExit();
			if (app() != null && app()->hasDebugger()) {
				debug()->spew();
			}
			exit();
			return;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getPersistenceModeString(int $mode):string{
		switch($mode){
			case PERSISTENCE_MODE_ALIAS:
				return "Alias";
			case PERSISTENCE_MODE_COOKIE:
				return "Cookie";
			case PERSISTENCE_MODE_DATABASE:
				return "Database";
			case PERSISTENCE_MODE_EMBEDDED:
				return "Embedded";
			case PERSISTENCE_MODE_ENCRYPTED:
				return "Encrypted";
			case PERSISTENCE_MODE_INTERSECTION:
				return "Intersection table";
			case PERSISTENCE_MODE_SESSION:
				return "Session";
			case PERSISTENCE_MODE_VOLATILE:
				return "Volatile";
			default:
				return "Undefined";
		}
	}
	
	public static function printSession(){
		$f = __METHOD__;
		if (isset($_SESSION) && is_array($_SESSION) && ! empty($_SESSION)) {
			Debug::print("{$f}: about to print session");
			static::printArray($_SESSION);
		} else {
			Debug::print("{$f}: Session is empty");
		}
	}

	public static function printSessionHash(){
		static::printArrayHash($_SESSION);
	}

	public static function printStackTrace($msg = null){
		Debug::printStackTraceNoExit($msg);
		$a = app();
		if ($a instanceof ApplicationRuntime && $a->hasDebugger()) {
			debug()->spew();
		}
		exit();
	}

	public static function requireCacheKey($key = null)
	{
		$f = __METHOD__; //Debug::getShortClass() . "::requireCacheKey()";
		if ($key === null) {
			if (isset($_SESSION) && is_array($_SESSION) && array_key_exists($key, $_SESSION)) {
				$key = $_SESSION['requiredCacheKey'];
			} else {
				return;
			}
		}
		if (! cache()->hasAPCu($key)) {
			unset($_SESSION['requiredCacheKey']);
			Debug::error("{$f} key \"{$key}\" is undefined");
		}
		Debug::print("{$f} key \"{$key}\" is cached by APCu");
	}

	public static function setRequiredCacheKey($key)
	{
		// $f = __METHOD__; //Debug::getShortClass()."::setRequiredCacheKey()";
		return $_SESSION['requiredCacheKey'] = $key;
	}

	public static function getFunctionNestingLevel()
	{
		return count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 254)) - 1;
	}

	public static function checkForbiddenString($string)
	{
		$f = __METHOD__; //Debug::getShortClass() . "::checkForbiddenString()";
		if (! is_string($string)) {
			Debug::error("checkForbiddenString received something that is not a string");
		}
		if ($_SESSION['forbidden'] === $string) {
			Debug::warning("{$f} yep, it's forbidden");
			Debug::printStackTrace();
		} else {
			// Debug::print("{$f} not forbidden, carry on");
		}
		return $string;
	}

	public static function printStackTraceNoExit($msg = null)
	{
		if (! empty($msg)) {
			Debug::print($msg);
		}
		$trace = (new Exception())->getTraceAsString();
		$exploded = explode("\n", $trace);
		foreach ($exploded as $line) {
			Debug::print($line);
		}
	}

	public static function forbidString($string)
	{
		return $_SESSION['forbidden'] = $string;
	}

	public static function armTrap()
	{
		$f = __METHOD__; //Debug::getShortClass() . "::" . __METHOD__ . "()";
		$print = false;
		if ($print) {
			Debug::print("{$f} trap armed");
		}
		Debug::printStackTraceNoExit();
		$_SESSION['trap'] = 1;
	}

	public static function disarmTrap()
	{
		if (array_key_exists('trap', $_SESSION)) {
			unset($_SESSION['trap']);
		}
	}

	public static function isTrapArmed()
	{
		return isset($_SESSION) && is_array($_SESSION) && array_key_exists('trap', $_SESSION) && $_SESSION['trap'] === 1;
	}

	public static function printPost($msg = null)
	{
		if (isset($msg) && is_string($msg) && $msg != "") {
			Debug::print($msg);
		}
		Debug::printArray($_POST);
		Debug::printStackTrace();
	}

	public static function printGet($msg = null)
	{
		if (isset($msg) && is_string($msg) && $msg != "") {
			Debug::print($msg);
		}
		Debug::printArray($_GET);
		Debug::printStackTrace();
	}

	public static function debugPhpClassDeclaration($class)
	{
		$reflector = new ReflectionClass($class);
		$file = $reflector->getFileName();
		$line = $reflector->getStartLine();
		return "{$file}, line {$line}";
	}

	public static function printArray($arr, ?int $depth = null)
	{
		$f = __METHOD__; //Debug::getShortClass() . "::printArray()";
		try {
			if (! is_array($arr)) {
				Debug::warning("{$f} not an array");
				Debug::printStackTrace($arr);
			} elseif (empty($arr)) {
				Debug::warning("{$f} array is empty");
				Debug::printStackTraceNoExit();
				return;
			} elseif (is_int($depth) && $depth <= 0) {
				return;
			}
			foreach (array_keys($arr) as $key) {
				if (is_array($arr[$key])) {
					Debug::print("{$f} array {$key}[]:{");
					if (is_int($depth)) {
						Debug::printArray($arr[$key], $depth - 1);
					} else {
						Debug::printArray($arr[$key]);
					}
					Debug::print("}");
				} else {
					if (is_object($arr[$key])) {
						if (method_exists($arr[$key], "__toString")) {
							$value = $arr[$key]->__toString();
						} elseif (method_exists($arr[$key], "toArray")) {
							$value = json_encode($arr[$key]->toArray());
						} else {
							$value = "Non-stringifiable object";
						}
					} else {
						$value = $arr[$key];
					}
					Debug::print("{$f} key: \"{$key}\" : value \"{$value}\"");
				}
			}
			//Debug::printStackTraceNoExit();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function printArrayHorizontal($arr): void
	{
		$f = __METHOD__; //Debug::getShortClass() . "::printArrayHorizontal()";
		try {
			if (! is_array($arr)) {
				Debug::warning("{$f} not an array");
				Debug::printStackTrace($arr);
			} elseif (empty($arr)) {
				Debug::printStackTraceNoExit("{$f} array is empty");
				return;
			}
			$s = "";
			foreach ($arr as $value) {
				if (! empty($s)) {
					$s .= " ";
				}
				$s .= "{$value}";
			}
			Debug::print($s);
			// Debug::printStackTraceNoExit();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function fatal($f, $whut)
	{
		Debug::print($f, "\033[31mFatal error: \"{$whut}\" in {$f}\033[0m");
		exit();
	}

	public static function info($what)
	{
		Debug::print($what);
	}

	public static function warning($what){
		Debug::print("\033[33mWarning: {$what}\033[0m");
	}

	public static function executionTimeout($duration = 30){
		$f = __METHOD__;
		if (getExecutionTime() >= $duration) {
			Debug::error("{$f} execution time limit exceeded");
		}
	}

	public static function limitExecutionDepth($limit = 2500){
		$f = __METHOD__;
		$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit + 1);
		if (count($bt) >= $limit + 1) {
			$what = "{$f} exceed maximum execution depth {$limit}";
			error_log("\033[31mError: {$what}\033[0m", 0);
			Debug::printStackTrace();
			exit();
		}
	}

	public static function incrementDebugCounterStatic(){
		$f = __METHOD__;
		try {
			if (! isset($_SESSION) || ! is_int($_SESSION['debug_counter'])) {
				$value = 1;
			} else {
				$value = $_SESSION['debug_counter'] + 1;
			}
			$_SESSION['debug_counter'] = $value;
			if ($value > 288) {
				Debug::resetDebugCounterStatic();
				Debug::error("{$f} I think you've had enough pal");
			}
			Debug::print("{$f} {$value} iterations");
			return $value;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function decrementDebugCounterStatic(){
		$f = __METHOD__;
		try {
			$print = false;
			if (! isset($_SESSION) || ! is_int($_SESSION['debug_counter'])) {
				$value = 0;
			} else {
				$value = $_SESSION['debug_counter'] - 1;
			}
			if ($print) {
				Debug::print("{$f} {$value} iterations");
			}
			return $_SESSION['debug_counter'] = $value;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function resetElementConstructorCount(){
		$_SESSION['elements'] = 0;
	}

	public static function incrementElementConstructorCount(){
		$f = __METHOD__;
		$print = false;
		if (! isset($_SESSION) || ! is_array($_SESSION)) {
			if ($print) {
				Debug::print("{$f} session is undefined");
			}
			return;
		}
		if (! array_key_exists("elements", $_SESSION)) {
			Debug::resetElementConstructorCount();
		}
		$_SESSION['elements'] ++;
	}

	public static function resetDebugCounterStatic(){
		$_SESSION['debug_counter'] = 0;
	}

	public static function checkMemoryUsage(string $when = "", ?int $limit = null, $print = false){
		$f = __METHOD__;
		if ($limit === null) {
			$limit = function_exists("memprof_dump_callgrind") ? 124000000 : 96000000;
		}
		$mem1 = memory_get_usage();
		if ($mem1 >= $limit) {
			if (function_exists("memprof_dump_callgrind")) {
				$path = '/var/' . DOMAIN_BASE . '/memprof/dump.out';
				error_log("{$f} about to dump to file \"{$path}\"", 0);
				$stream = fopen($path, 'w+');
				if (is_bool($stream) && ! $stream) {
					error_log("{$f} unable to open file at {$path}, you probably forgot to change directory ownership to www-data", 0);
				} else {
					memprof_dump_callgrind($stream);
					error_log("{$f} file dumped", 0);
				}
			} elseif($print) {
				error_log("{$f} memprof is not enabled", 0);
			}
			error_log("{$f} memory overload {$mem1} {$when}", 0);
			Debug::printStackTrace();
		} elseif ($print) {
			error_log("{$f} memory usage: {$mem1} {$when}", 0);
		}
	}

	public static function print($what){
		$f = __METHOD__;
		try {
			if (is_array($what)) {
				return Debug::printArray($what);
			} elseif (is_object($what)) {
				if (! $what instanceof StringifiableInterface) {
					$class = get_class($what);
					Debug::warning("{$f} received a parameter that is a non-stringifiable object of class \"{$class}\"");
					return;
				}
			} elseif (is_bool($what)) {
				if ($what) {
					Debug::print("true");
				} else {
					Debug::print("false");
				}
			} elseif (! is_string($what) && ! is_int($what) && ! is_float($what)) {
				$type = gettype($what);
				Debug::error("{$f} received a parameter of type \"{$type}\"");
			} //
			if (defined("DEBUG_IP_ADDRESS") && $_SERVER['REMOTE_ADDR'] !== DEBUG_IP_ADDRESS) {
				return;
			}
			error_log($what, 0);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function printArray64($arr){
		$f = __METHOD__;
		if (empty($arr)) {
			Debug::print("{$f} array is empty");
		} else
			foreach ($arr as $k => $v) {
				if (is_array($v)) {
					Debug::print("{$f} key \"{$k}\" => nested array");
					Debug::printArray64($v);
					continue;
				}
				$b64 = base64_encode($v);
				Debug::print("{$f} key: \"{$k}\", value \"{$b64}\"");
			}
	}

	public static function printArrayHash($arr){
		$f = __METHOD__;
		if (empty($arr)) {
			Debug::print("{$f} array is empty");
		} else
			foreach ($arr as $k => $v) {
				if (is_array($v)) {
					Debug::print("{$f} key \"{$k}\" => nested array");
					Debug::printArrayHash($v);
					continue;
				}
				$hash = sha1($v);
				Debug::print("{$f} key: \"{$k}\", hash \"{$hash}\"");
			}
	}

	public static function requireDebugId($debug_id){
		return $_SESSION['debugId'] = $debug_id;
	}

	public static function validateDebugId($debug_id){
		$f = __METHOD__;
		if ($_SESSION['debugId'] !== $debug_id) {
			Debug::error("{$f} invalid debug ID \"{$debug_id}\", should be \"{$_SESSION['debugId']}\"");
		}
	}

	public static function springTrap($msg = null){
		$f = __METHOD__;
		if (Debug::isTrapArmed()) {
			Debug::disarmTrap();
			if (isset($msg)) {
				Debug::error($msg);
			}
			Debug::error("{$f} trapped");
		}
	}
}
