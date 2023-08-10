<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ParametricTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class ConstructorCommand extends Command implements JavaScriptInterface, ValueReturningCommandInterface
{

	use ParametricTrait;

	protected $objectClass;

	protected $constructedObject;

	public function __construct($objectClass, ...$params)
	{
		$f = __METHOD__; //ConstructorCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct();
		if ($objectClass == null) {
			Debug::error("{$f} object class is null");
		}
		$this->setObjectClass($objectClass);
		if (isset($params) && count($params) > 0) {
			$arr = [];
			foreach ($params as $param) {
				array_push($arr, $param);
			}
			$this->setParameters($arr);
		}
		// Debug::printStackTraceNoExit("{$f} my debug ID is \"{$this->debugId}\"");
	}

	public function setObjectClass($objectClass)
	{
		$f = __METHOD__; //ConstructorCommand::getShortClass()."(".static::getShortClass().")->setObjectClass()";
		return $this->objectClass = $objectClass;
	}

	public function hasObjectClass()
	{
		return isset($this->objectClass); // && class_exists($this->objectClass);
	}

	public function getObjectClass()
	{
		$f = __METHOD__; //ConstructorCommand::getShortClass()."(".static::getShortClass().")->getObjectClass()";
		if (! $this->hasObjectClass()) {
			Debug::error("{$f} object class is undefined");
		}
		return $this->objectClass;
	}

	public static function getCommandId(): string
	{
		return "construct";
	}

	public function hasConstructedObject()
	{
		$oc = $this->getObjectClass();
		return isset($this->constructedObject) && $this->constructedObject instanceof $oc;
	}

	public function getConstructedObject()
	{
		$f = __METHOD__; //ConstructorCommand::getShortClass()."(".static::getShortClass().")->getConstructedObject()";
		if (! $this->hasConstructedObject()) {
			Debug::error("{$f} constructed object is undefined");
		}
		return $this->constructedObject;
	}

	public function setConstructedObject($rv)
	{
		return $this->constructedObject = $rv;
	}

	public function evaluate(?array $params = null)
	{
		if (! empty($params)) {
			$this->setParameters($params);
		}
		return $this->resolve();
	}

	public function resolve()
	{
		$f = __METHOD__; //ConstructorCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		$print = false;
		if ($this->hasConstructedObject()) {
			if ($print) {
				Debug::print("{$f} constructed object is already defined");
			}
			return $this->getConstructedObject();
		}
		$objectClass = $this->getObjectClass();
		if ($objectClass instanceof ValueReturningCommandInterface) {
			while ($objectClass instanceof ValueReturningCommandInterface) {
				$objectClass = $objectClass->evaluate();
			}
		}
		if (! is_string($objectClass)) {
			Debug::error("{$f} object class is not a string");
		} elseif (! class_exists($objectClass)) {
			Debug::error("{$f} class \"{$objectClass}\" does not exist");
		}
		$params = $this->hasParameters() ? $this->getParameters() : [];
		$ret = new $objectClass(...$params);
		if ($print) {
			$ret_did = $ret->getDebugId();
			$my_did = $this->getDebugId();
			Debug::print("{$f} constructed object of class {$objectClass} with debug ID \"{$ret_did}\"; my debug ID is \"{$my_did}\"");
		}
		return $this->setConstructedObject($ret);
	}

	public function toJavaScript(): string
	{
		$objectClass = get_short_class($this->getObjectClass());
		if ($this->hasParameters()) {
			$params = $this->getParameterString(true);
		} elseif (is_a($objectClass, DataStructure::class, true)) {
			$params = "null, context.getResponseText()";
		} else {
			$params = "";
		}
		return "new {$objectClass}({$params})";
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->constructedObject);
		unset($this->objectClass);
	}
}
