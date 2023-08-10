<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\str;

use function JulianSeymour\PHPWebApplicationFramework\ends_with;
use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\starts_with;
use function JulianSeymour\PHPWebApplicationFramework\str_contains;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class ConcatenateCommand extends Command implements JavaScriptInterface, StringifiableInterface, ValueReturningCommandInterface
{

	public static function getCommandId(): string
	{
		return "concat";
	}

	public function __construct($s1, ...$more)
	{
		$f = __METHOD__; //ConcatenateCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct();
		$strings = [
			$s1
		];
		if (! empty($more)) {
			foreach ($more as $s) {
				array_push($strings, $s);
			}
		}
		$this->setStrings($strings);
		if (! $this->hasStrings()) {
			Debug::error("{$f} strings are mandatory");
		}
	}

	public static function declareFlags(): array
	{
		return array_merge(parent::declareFlags(), [
			"reserved"
		]);
	}

	public function getStringAtOffset(int $offset)
	{
		return $this->getArrayPropertyValueAtOffset("strings", $offset);
	}

	public function starts_with($needle)
	{
		return starts_with($this->evaluate(), $needle);
	}

	public function contains($needle)
	{
		return str_contains($this->evaluate(), $needle);
	}

	public function ends_with($needle){
		return ends_with($this->evaluate(), $needle);
	}

	public function setStrings($strings)
	{
		return $this->setArrayProperty("strings", $strings);
		// return $this->s/trings = $strings;
	}

	public function getStrings()
	{
		$f = __METHOD__; //ConcatenateCommand::getShortClass()."(".static::getShortClass().")->getStrings()";
		return $this->getProperty("strings");
		if (! $this->hasStrings()) {
			if (! $this->getAllocatedFlag()) {
				Debug::error("{$f} debug ID is undefined -- this command was already deallocated");
			}
			Debug::error("{$f} strings undefined for object with debug ID \"{$this->debugId}\"");
		}
		// return $this->s/trings;
	}

	public function getStringCount()
	{
		return $this->getArrayPropertyCount("strings");
	}

	public function hasStrings()
	{
		return $this->hasArrayProperty("strings"); // !empty($this->s/trings) && count($this->s/trings) > 1;
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //ConcatenateCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		try {
			$print = false;
			$q = $this->getQuoteStyle();
			$strings = $this->getStrings();
			$string = $strings[0];
			if ($string instanceof JavaScriptInterface) {
				$string = $string->toJavaScript();
			} elseif (is_string($string) || $string instanceof StringifiableInterface) {
				$string = escape_quotes($string, $q);
				$string = "{$q}{$string}{$q}";
			}
			for ($i = 1; $i < count($strings); $i ++) {
				$s = $strings[$i];
				if ($s instanceof JavaScriptInterface) {
					$s = $s->toJavaScript();
				} elseif (is_string($s) || $s instanceof StringifiableInterface) {
					$s = escape_quotes($s, $q);
					$s = "{$q}{$s}{$q}";
				}
				$string .= ".concat({$s})";
				if ($print) {
					Debug::print("{$f} after concatenating string {$i}, we have \"{$string}\"");
				}
			}
			return $string;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function evaluate(?array $params = null)
	{
		$strings = $this->getStrings();
		$value = "";
		foreach ($strings as $s) {
			if ($s instanceof ValueReturningCommandInterface) {
				while ($s instanceof ValueReturningCommandInterface) {
					$s = $s->evaluate();
				}
			}
			$value .= $s;
		}
		return $value;
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		$f = __METHOD__; //ConcatenateCommand::getShortClass()."(".static::getShortClass().")->echoInnerJson()";
		Json::echoKeyValuePair('strings', $this->getStrings());
		parent::echoInnerJson($destroy);
	}

	public function replicate()
	{
		$class = static::class;
		$replica = new $class(...$this->getStrings());
		return $replica;
	}

	public function __toString(): string
	{
		return $this->evaluate();
	}
}
