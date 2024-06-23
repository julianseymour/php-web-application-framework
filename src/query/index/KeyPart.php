<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionalTrait;
use JulianSeymour\PHPWebApplicationFramework\common\LengthTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\DirectionalityTrait;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;

class KeyPart extends Basic implements ReplicableInterface, SQLInterface{

	use ColumnNameTrait;
	use DirectionalityTrait;
	use ExpressionalTrait;
	use LengthTrait;
	use ReplicableTrait;

	public function __construct($columnName=null, $length = null){
		parent::__construct();
		if($columnName !== null){
			if($columnName instanceof ExpressionCommand){
				$this->setExpression($columnName);
			}else{
				$this->setColumnName($columnName, $length);
			}
		}
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasColumnName()){
			$this->setColumnName(replicate($that->getColumnName()));
		}
		if($that->hasDirectionality()){
			$this->setDirectionality(replicate($that->getDirectionality()));
		}
		if($that->hasExpression()){
			$this->setExpression(replicate($that->getExpression()));
		}
		if($that->hasLength()){
			$this->setLength(replicate($that->getLength()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$ds = $this->getDebugString();
		$this->release($this->columnName, $deallocate);
		$this->release($this->directionality, $deallocate);
		$this->release($this->expression, $deallocate);
		$this->release($this->lengthValue, $deallocate);
	}
	
	public function setColumnName(?string $columnName, ?int $length = null): ?string{
		$f = __METHOD__;
		if(!is_string($columnName)){
			Debug::error("{$f} column name must be a string");
		}elseif(empty($columnName)){
			Debug::error("{$f} column name cannot be empty string");
		}elseif($this->hasColumnName()){
			$this->release($this->columnName);
		}
		$this->release($this->lengthValue);
		if($length !== null){
			$this->setLength($length);
		}
		return $this->columnName = $this->claim($columnName);
	}

	public function toSQL(): string{
		$f = __METHOD__;
		if($this->hasColumnName()){
			$string = $this->getColumnName();
		}elseif($this->hasExpression()){
			$string = "(" . $this->getExpression() . ")";
		}else{
			Debug::error("{$f} neither of the above");
		}
		if($this->hasDirectionality()){
			$string .= " " . $this->getDirectionality();
		}
		return $string;
	}
}
