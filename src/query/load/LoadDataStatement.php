<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\load;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\implode_back_quotes;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\partition\MultiplePartitionNamesTrait;

class LoadDataStatement extends LoadStatement{

	use ExportOptionsTrait;
	use MultiplePartitionNamesTrait;

	protected $lineStartString;

	protected $lineTerminatorString;

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"optionallyEnclosed"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"optionallyEnclosed"
		]);
	}
	
	public function setLineStart(?string $s):?string{
		$f = __METHOD__;
		if(!is_string($s)){
			Debug::error("{$f} line start must be a string");
		}elseif($this->hasLineStart()){
			$this->release($this->lineStartString);
		}
		return $this->lineStartString = $this->claim($s);
	}

	public function hasLineStart():bool{
		return isset($this->lineStartString);
	}

	public function getLineStart():string{
		$f = __METHOD__;
		if(!$this->hasLineStart()){
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
		if(!is_string($s)){
			Debug::error("{$f} line terminator must be a string");
		}elseif($this->hasLineTerminator()){
			$this->release($this->lineTerminatorString);
		}
		return $this->lineTerminatorString = $this->claim($s);
	}

	public function hasLineTerminator():bool{
		return isset($this->lineTerminatorString);
	}

	public function getLineTerminator():string{
		$f = __METHOD__;
		if(!$this->hasLineTerminator()){
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
		if($this->hasPartitionNames()){
			$string .= " partition (" . implode(',', $this->getPartitionNames()) . ")";
		}
		// [CHARACTER SET charset_name]
		if($this->hasCharacterSet()){
			$string .= " character set " . $this->getCharacterSet();
		}
		if($this->hasExportOptions()){
			$string .= $this->getExportOptions();
		}
		// [LINES [STARTING BY 'string'] [TERMINATED BY 'string'] ]
		if($this->hasLineStart() || $this->hasLineTerminator()){
			$string .= " lines";
			if($this->hasLineStart()){
				$start = single_quote($this->getLineStart());
				$string .= " starting by {$start}";
				unset($start);
			}
			if($this->hasLineTerminator()){
				$term = single_quote($this->getLineTerminator());
				$string .= " terminated by {$term}";
				unset($term);
			}
		}
		// [IGNORE number {LINES | ROWS}]
		if($this->hasIgnoreRows()){
			$string .= " ignore " . $this->getIgnoreRows() . " rows";
		}
		// [(col_name_or_user_var [, col_name_or_user_var] ...)]
		if($this->hasColumnNames()){
			$string .= " (" . implode_back_quotes(',', $this->getColumnNames() . ")");
		}
		// [SET col_name={expr | DEFAULT} [, col_name={expr | DEFAULT}] ...]
		if($this->hasExpressions()){
			$string .= " set " . implode(',', $this->getExpressions());
		}
		return $string;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->columnTerminatorString, $deallocate);
		$this->release($this->enclosureCharacter, $deallocate);
		$this->release($this->escapeCharacter, $deallocate);
		$this->release($this->lineStartString, $deallocate);
		$this->release($this->lineTerminatorString, $deallocate);
	}
}
