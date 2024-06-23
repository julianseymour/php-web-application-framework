<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;

class ColumnAliasExpression extends Basic implements ReplicableInterface, SQLInterface{

	use ColumnNameTrait;
	use ReplicableTrait;
	
	protected $tableAlias;

	public function __construct(?string $tableAlias=null, ?string $columnName=null){
		parent::__construct();
		if(isset($tableAlias) && !empty($tableAlias)){
			$this->setTableAlias($tableAlias);
		}
		if(isset($columnName) && !empty($columnName)){
			$this->setColumnName($columnName);
		}
	}

	public function hasTableAlias(): bool{
		return isset($this->tableAlias);
	}

	public function getTableAlias(): string{
		$f = __METHOD__;
		if(!$this->hasTableAlias()){
			Debug::error("{$f} table alias is undefied");
		}
		return $this->tableAlias;
	}

	public function setTableAlias(?string $tableAlias): ?string{
		if($this->hasTableAlias()){
			$this->release($this->tableAlias);
		}
		return $this->tableAlias = $this->claim($tableAlias);
	}

	public function toSQL(): string{
		return back_quote($this->getTableAlias()) . "." . back_quote($this->getColumnName());
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->columnName, $deallocate);
		$this->release($this->tableAlias, $deallocate);
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasTableAlias()){
			$this->setTableAlias(replicate($that->getTableAlias()));
		}
		if($that->hasColumnName()){
			$this->setColumnName(replicate($that->getColumnName()));
		}
		return $ret;
	}
}