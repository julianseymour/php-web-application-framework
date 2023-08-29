<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\load;

use function JulianSeymour\PHPWebApplicationFramework\implode_back_quotes;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\partition\MultiplePartitionNamesTrait;

class LoadDataStatement extends LoadStatement{

	use ExportOptionsTrait;
	use MultiplePartitionNamesTrait;

	protected $columnTerminatorString;

	protected $enclosureCharacter;

	protected $escapeCharacter;

	protected $lineStartString;

	protected $lineTerminatorString;

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"optionallyEnclosed"
		]);
	}

	public function setLineStart(?string $s):?string{
		$f = __METHOD__;
		if ($s == null) {
			unset($this->lineStartString);
			return null;
		} elseif (! is_string($s)) {
			Debug::error("{$f} line start must be a string");
		}
		return $this->lineStartString = $s;
	}

	public function hasLineStart():bool{
		return isset($this->lineStartString);
	}

	public function getLineStart():string{
		$f = __METHOD__;
		if (! $this->hasLineStart()) {
			Debug::error("{$f} line start is undefined");
		}
		return $this->lineStartString;
	}

	public function linesStartingBy(?string $s):LoadDataStatement{
		$this->setLineStart($s);
		return $this;
	}

	public function setLineTerminator(?string $s):?string{
		$f = __METHOD__;
		if ($s == null) {
			unset($this->lineTerminatorString);
			return null;
		} elseif (! is_string($s)) {
			Debug::error("{$f} line terminator must be a string");
		}
		return $this->lineTerminatorString = $s;
	}

	public function hasLineTerminator():bool{
		return isset($this->lineTerminatorString);
	}

	public function getLineTerminator():string{
		$f = __METHOD__;
		if (! $this->hasLineTerminator()) {
			Debug::error("{$f} line start is undefined");
		}
		return $this->lineTerminatorString;
	}

	public function linesTerminatedBy(?string $s):LoadDataStatement{
		$this->setLineTerminator($s);
		return $this;
	}

	public function getQueryStatementString():string{
		// LOAD DATA
		$string = "load data " . parent::getQueryStatementString();
		// [PARTITION (partition_name [, partition_name] ...)]
		if ($this->hasPartitionNames()) {
			$string .= " partition (" . implode(',', $this->getPartitionNames()) . ")";
		}
		// [CHARACTER SET charset_name]
		if ($this->hasCharacterSet()) {
			$string .= " character set " . $this->getCharacterSet();
		}
		if ($this->hasExportOptions()) {
			$string .= $this->getExportOptions();
		}
		// [LINES [STARTING BY 'string'] [TERMINATED BY 'string'] ]
		if ($this->hasLineStart() || $this->hasLineTerminator()) {
			$string .= " lines";
			if ($this->hasLineStart()) {
				$start = single_quote($this->getLineStart());
				$string .= " starting by {$start}";
				unset($start);
			}
			if ($this->hasLineTerminator()) {
				$term = single_quote($this->getLineTerminator());
				$string .= " terminated by {$term}";
				unset($term);
			}
		}
		// [IGNORE number {LINES | ROWS}]
		if ($this->hasIgnoreRows()) {
			$string .= " ignore " . $this->getIgnoreRows() . " rows";
		}
		// [(col_name_or_user_var [, col_name_or_user_var] ...)]
		if ($this->hasColumnNames()) {
			$string .= " (" . implode_back_quotes(',', $this->getColumnNames() . ")");
		}
		// [SET col_name={expr | DEFAULT} [, col_name={expr | DEFAULT}] ...]
		if ($this->hasExpressions()) {
			$string .= " set " . implode(',', $this->getExpressions());
		}
		return $string;
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->columnTerminatorString);
		unset($this->enclosureCharacter);
		unset($this->escapeCharacter);
		unset($this->lineStartString);
		unset($this->lineTerminatorString);
	}
}
