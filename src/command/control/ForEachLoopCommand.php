<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\IteratorTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class ForEachLoopCommand extends LoopCommand
{

	use IteratorTrait;

	protected $iteratedObject;

	public function __construct($iterator, $iteratee, ...$blocks)
	{
		parent::__construct(...$blocks);
		$this->setIterator($iterator);
		$this->setIteratedObject($iteratee);
	}

	public function setIteratedObject($o)
	{
		return $this->iteratedObject = $o;
	}

	public function hasIteratedObject()
	{
		return isset($this->iteratedObject);
	}

	public function getIteratedObject()
	{
		$f = __METHOD__;
		if (! $this->hasIteratedObject()) {
			Debug::error("{$f} iterated object is undefined");
		}
		return $this->iteratedObject;
	}

	public static function getCommandId(): string
	{
		return "for_in";
	}

	public function resolve()
	{
		$f = __METHOD__;
		try {
			$print = false;
			$iteratee = $this->getIteratedObject();
			while ($iteratee instanceof ValueReturningCommandInterface) {
				if ($print) {
					$class = $iteratee->getClass();
					Debug::print("{$f} iterated object is a value-returning media command of class \"{$class}\"");
				}
				$iteratee = $iteratee->evaluate();
			}
			if (! is_array($iteratee)) {
				if (is_object($iteratee)) {
					$gottype = $iteratee->getClass();
				} else {
					$gottype = gettype($iteratee);
				}
				Debug::error("{$f} the object sent as the second parameter for this class's cunstructor must resolve to an array; received parameter resolved to type \"{$gottype}\"");
			}
			if ($print) {
				$count = count($iteratee);
				Debug::print("{$f} iterating over {$count} objects");
			}
			if ($print) {
				$count = $this->getCodeBlockCount();
				Debug::print("{$f} {$count} code blocks");
			}
			$iterator = $this->getIterator();
			if ($print) {
				$ic = count($iteratee);
				Debug::print("{$f} iterating over {$ic} iterators");
				$debug_ids = [];
				foreach ($iteratee as $i) {
					$did = $i->getDebugId();
					if (array_key_exists($did, $debug_ids)) {
						Debug::error("{$f} object with debug ID \"{$did}\" has more than one instance");
					}
					$debug_ids[$did] = $i;
				}
				Debug::print("{$f} no collisions detected");
			}
			foreach ($iteratee as $i) {
				$iterator->setValue($i);
				$this->resolveCodeBlocks();
			}
			if ($print) {
				Debug::print("{$f} returning normally I guess");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__;
		try {
			$it = $this->getIterator();
			if ($it instanceof JavaScriptInterface) {
				$it = $it->toJavaScript();
			}
			$arr = $this->getIteratedObject();
			if ($arr instanceof JavaScriptInterface) {
				$arr = $arr->toJavaScript();
			}
			$s = "for({$it} in {$arr}){\n";
			foreach ($this->getCodeBlocks() as $b) {
				$s .= "\t" . $b->toJavaScript() . ";\n";
			}
			$s .= "}\n";
			return $s;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->iterator);
		unset($this->iteratedObject);
	}
}
