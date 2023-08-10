<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\load;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ExportOptionsTrait
{

	use FlagBearingTrait;

	public function setOptionallyEnclosedFlag($value = true)
	{
		return $this->setFlag("optionallyEnclosed", $value);
	}

	public function getOptionallyEnclosedFlag()
	{
		return $this->getFlag("optionallyEnclosed");
	}

	public function setColumnTerminator($s)
	{
		$f = __METHOD__; //"ExportOptionsTrait(".static::getShortClass().")->setColumnTerminator()";
		if ($s == null) {
			unset($this->columnTerminatorString);
			return null;
		} elseif (! is_string($s)) {
			Debug::error("{$f} column terminator must be a string");
		}
		return $this->columnTerminatorString = $s;
	}

	public function hasColumnTerminator()
	{
		return isset($this->columnTerminatorString);
	}

	public function getColumnTerminator()
	{
		$f = __METHOD__; //"ExportOptionsTrait(".static::getShortClass().")->getColumnTerminator()";
		if (! $this->hasColumnTerminator()) {
			Debug::error("{$f} column terminator is undefined");
		}
		return $this->columnTerminatorString;
	}

	public function columnsTerminatedBy($s)
	{
		$this->setColumnTerminator($s);
		return $this;
	}

	public function setEnclosureCharacter($c)
	{
		$f = __METHOD__; //"ExportOptionsTrait(".static::getShortClass().")->setEnclosureCharacter()";
		if ($c == null) {
			unset($this->enclosureCharacter);
			return null;
		} elseif (is_string($c)) {
			if (strlen($c) !== 1) {
				Debug::error("{$f} string length must be 1");
			}
		} elseif (is_int($c)) {
			Debug::error("{$f} to do: convert integers < 255 to char");
		}
		return $this->enclosureCharacter = $c;
	}

	public function hasEnclosureCharacter()
	{
		return isset($this->enclosureCharacter);
	}

	public function getEnclosureCharacter()
	{
		$f = __METHOD__; //"ExportOptionsTrait(".static::getShortClass().")->getEnclosureCharacter()";
		if (! $this->hasEnclosureCharacter()) {
			Debug::error("{$f} enclosure character is undefined");
		}
		return $this->enclosureCharacter;
	}

	public function enclosedBy($c)
	{
		$this->setEnclosureCharacter($c);
		return $this;
	}

	public function optionallyEnclosedBy($c)
	{
		$this->setOptionallyEnclosedFlag(true);
		return $this->enclosedBy($c);
	}

	public function setEscapeCharacter($c)
	{
		$f = __METHOD__; //"ExportOptionsTrait(".static::getShortClass().")->setEscapeCharacter()";
		if ($c == null) {
			unset($this->escapeCharacter);
			return null;
		} elseif (is_string($c)) {
			if (strlen($c) !== 1) {
				Debug::error("{$f} string length must be 1");
			}
		} elseif (is_int($c)) {
			Debug::error("{$f} to do: convert integers < 255 to char");
		}
		return $this->escapeCharacter = $c;
	}

	public function hasEscapeCharacter()
	{
		return isset($this->escapeCharacter);
	}

	public function getEscapeCharacter()
	{
		$f = __METHOD__; //"ExportOptionsTrait(".static::getShortClass().")->getEscapeCharacter()";
		if (! $this->hasEscapeCharacter()) {
			Debug::error("{$f} escape character is undefined");
		}
		return $this->escapeCharacter;
	}

	public function escapedBy($c)
	{
		$this->setEscapeCharacter($c);
		return $this;
	}

	public function hasExportOptions(): bool
	{
		return $this->hasColumnTerminator() || $this->hasEnclosureCharacter() || $this->hasEscapeCharacter();
	}

	public function fieldsTerminatedBy($terminator)
	{
		$this->setColumnTerminator($terminator);
		return $this;
	}

	public function getExportOptions(): string
	{
		$f = __METHOD__; //"ExportOptionsTrait(".static::getShortClass().")->getExportOptions()";
		if (! $this->hasExportOptions()) {
			Debug::error("{$f} export options are undefined");
		}
		// {FIELDS | COLUMNS}
		$string = " columns";
		// [TERMINATED BY 'string']
		if ($this->hasColumnTerminator()) {
			$term = escape_quotes($this->getColumnTerminator(), QUOTE_STYLE_SINGLE);
			$string .= " terminated by '{$term}'";
			unset($term);
		}
		// [[OPTIONALLY] ENCLOSED BY 'char']
		if ($this->hasEnclosureCharacter()) {
			if ($this->getOptionallyEnclosedFlag()) {
				$string .= " optionally";
			}
			$enclosure = escape_quotes($this->getEnclosureCharacter(), QUOTE_STYLE_SINGLE);
			$string .= " enclosed by '{$enclosure}'";
			unset($enclosure);
		}
		// [ESCAPED BY 'char']
		if ($this->hasEscapeCharacter()) {
			$esc = escape_quotes($this->getEscapeCharacter(), QUOTE_STYLE_SINGLE);
			$string .= " escaped by '{$esc}'";
			unset($esc);
		}
		return $string;
	}
}