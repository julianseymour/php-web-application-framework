<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\view;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\implode_back_quotes;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;

class CreateViewStatement extends ViewStatement{

	use NamedTrait;

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"replace"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"replace"
		]);
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->name, $deallocate);
	}
	
	public function setReplaceFlag(bool $value = true):bool{
		return $this->setFlag("replace", $value);
	}

	public function getReplaceFlag():bool{
		return $this->getFlag("replace");
	}

	public static function orReplace($name = null, $selectStatement = null):CreateViewStatement{
		$st = new CreateViewStatement($name, $selectStatement);
		$st->setReplaceFlag(true);
		return $st;
	}

	public function getQueryStatementString():string{
		// CREATE
		$string = "create ";
		// [OR REPLACE]
		if($this->getReplaceFlag()){
			$string .= "or replace ";
		}
		// [ALGORITHM = {UNDEFINED | MERGE | TEMPTABLE}]
		if($this->hasAlgorithm()){
			$string .= "algorithm = " . $this->getAlgorithm() . " ";
		}
		// [DEFINER = user]
		if($this->hasDefiner()){
			$string .= "definer = " . $this->getDefiner() . " ";
		}
		// [SQL SECURITY { DEFINER | INVOKER }]
		if($this->hasSQLSecurity()){
			$string .= "SQL security " . $this->getSQLSecurity() . " ";
		}
		// VIEW view_name
		$string .= "view ";
		if($this->hasDatabaseName()){
			$string .= back_quote($this->getDatabaseName()) . ".";
		}
		$string .= back_quote($this->getName()) . " ";
		// [(column_list)]
		if($this->hasColumnNames()){
			$string .= "(" . implode_back_quotes(',', $this->getColumnNames()) . ") ";
		}
		// AS select_statement
		$string .= "as " . $this->getSelectStatement();
		// [WITH [CASCADED | LOCAL] CHECK OPTION]
		if($this->hasCheckOption()){
			$check = $this->getCheckOption();
			$string .= " with {$check} ";
			if($check !== CHECK_OPTION_CHECK){
				$string .= "check ";
			}
			$string .= "option";
		}
		return $string;
	}
}
