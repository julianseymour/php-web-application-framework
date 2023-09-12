<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionalTrait;
use JulianSeymour\PHPWebApplicationFramework\common\LengthTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\DirectionalityTrait;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;

class KeyPart extends Basic implements SQLInterface{

	use ColumnNameTrait;
	use DirectionalityTrait;
	use ExpressionalTrait;
	use LengthTrait;

	public function __construct($columnName, $length = null){
		parent::__construct();
		if($columnName instanceof ExpressionCommand) {
			$this->setExpression($columnName);
		}else{
			$this->setColumnName($columnName, $length);
		}
	}

	public function setColumnName(?string $columnName, ?int $length = null): ?string{
		$f = __METHOD__;
		if($columnName == null) {
			unset($this->columnName);
			unset($this->lengthValue);
			return null;
		}elseif(!is_string($columnName)) {
			Debug::error("{$f} column name must be a string");
		}elseif(empty($columnName)) {
			Debug::error("{$f} column name cannot be empty string");
		}elseif($length !== null) {
			$this->setLength($length);
		}
		return $this->columnName = $columnName;
	}

	public function toSQL(): string{
		$f = __METHOD__;
		if($this->hasColumnName()) {
			$string = $this->getColumnName();
			/*
			 * if($this->hasLength()){
			 * $string .= " (".$this->getLength().")";
			 * }
			 */
			// XXX this was causing syntax errors
		}elseif($this->hasExpression()) {
			$string = "(" . $this->getExpression() . ")";
		}else{
			Debug::error("{$f} neither of the above");
		}
		if($this->hasDirectionality()) {
			$string .= " " . $this->getDirectionality();
		}
		return $string;
	}
}
