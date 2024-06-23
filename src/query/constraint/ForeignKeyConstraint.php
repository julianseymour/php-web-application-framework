<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\implode_back_quotes;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayKeyProviderInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\index\KeyPart;
use JulianSeymour\PHPWebApplicationFramework\query\index\KeyPartsTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\FullTableNameTrait;
use Exception;

class ForeignKeyConstraint extends Constraint implements ArrayKeyProviderInterface{

	use FullTableNameTrait;
	use ForeignKeyConstraintTrait;
	use IndexNameTrait;
	use KeyPartsTrait;
	use MultipleColumnNamesTrait;

	protected $matchType;

	public function __construct(?string $symbol=null, ?string $indexName=null, ?array $columnNames=null, ?string $databaseName=null, ?string $tableName=null, ?array $keyparts=null){
		parent::__construct($symbol);
		$this->requirePropertyType("columnNames", "s");
		$this->requirePropertyType("keyParts", KeyPart::class);
		if($indexName !== null){
			$this->setIndexName($indexName);
		}
		if($columnNames !== null){
			$this->setColumnNames($columnNames);
		}
		if($databaseName !== null){
			$this->setDatabaseName($databaseName);
		}
		if($tableName !== null){
			$this->setTableName($tableName);
		}
		if($keyparts !== null){
			$this->setKeyParts($keyparts);
		}
		// symbol, index name, columnNames, tableName, keyparts, matchType, onDelete, onUpdate
	}

	public function copy($that):int{
		$f = __METHOD__;
		$ret = parent::copy($that);
		if($that->hasDatabaseName()){
			$this->setDatabaseName(replicate($that->getDatabaseName()));
		}
		if($that->hasTableName()){
			$this->setTableName(replicate($that->getTableName()));
		}
		if($that->hasOnDelete()){
			$this->setOnDelete(replicate($that->getOnDelete()));
		}
		if($that->hasOnUpdate()){
			$this->setOnUpdate(replicate($that->getOnUpdate()));
		}
		if($that->hasIndexName()){
			$this->setIndexName(replicate($that->getIndexName()));
		}
		if($that->hasProperties()){
			$this->setProperties(replicate($that->getProperties()));
		}
		if($that->hasPropertyTypes()){
			$this->setPropertyTypes(replicate($that->getPropertyTypes()));
		}
		if($that->hasMatch()){
			$this->setMatch(replicate($that->getMatch()));
		}
		return $ret;
	}
	
	public function getArrayKey(int $count){
		return $this->getIndexName();
	}

	public function setMatch($type){
		$f = __METHOD__;
		if(!is_string($type)){
			Debug::error("{$f} match type must be a string");
		}elseif($this->hasMatch()){
			$this->release($this->matchType);
		}
		$type = strtolower($type);
		switch($type){
			case MATCH_FULL:
			case MATCH_PARTIAL:
			case MATCH_SIMPLE:
				break;
			default:
				Debug::error("{$f} invalid match type \"{$type}\"");
		}
		return $this->matchType = $this->claim($type);
	}

	public function hasMatch():bool{
		return isset($this->matchType);
	}

	public function getMatch(){
		$f = __METHOD__;
		if(!$this->hasMatch()){
			Debug::error("{$f} match type is undefined");
		}
		return $this->matchType;
	}

	public function match($type):ForeignKeyConstraint{
		$this->setMatch($type);
		return $this;
	}

	public function toSQL(): string{
		$f = __METHOD__;
		try{

			// reference_definition:
			// [CONSTRAINT [symbol]] FOREIGN KEY [index_name] (col_name,...) REFERENCES tbl_name (key_part,...) [MATCH FULL | MATCH PARTIAL | MATCH SIMPLE] [ON DELETE ref_opt] [ON UPDATE ref_opt]
			// reference_option:
			// RESTRICT | CASCADE | SET NULL | NO ACTION | SET DEFAULT

			$string = parent::toSQL() . "foreign key ";
			if($this->hasIndexName()){
				$string .= $this->getIndexName() . " ";
			}
			$columnNames = implode_back_quotes(',', $this->getColumnNames());
			$dbtable = "";
			if($this->hasDatabaseName()){
				$dbtable .= back_quote($this->getDatabaseName()) . ".";
			}
			$dbtable .= back_quote($this->getTableName());
			$keyparts = [];
			foreach($this->getKeyParts() as $kp){
				if($kp instanceof SQLInterface){
					$kp = $kp->toSQL();
				}
				array_push($keyparts, $kp);
			}
			$keyparts = implode_back_quotes(',', $keyparts);
			$string .= "({$columnNames}) references {$dbtable} ({$keyparts})";
			if($this->hasMatch()){
				$string .= " match " . $this->getMatch();
			}
			if($this->hasOnDelete()){
				$onDelete = $this->getOnDelete();
				$string .= " on delete {$onDelete}";
			}
			if($this->hasOnUpdate()){
				$onUpdate = $this->getOnUpdate();
				$string .= " on update {$onUpdate}";
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function dispose(bool $deallocate=false): void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->indexName, $deallocate);
		$this->release($this->matchType, $deallocate);
		$this->release($this->onDeleteReferenceOption, $deallocate);
		$this->release($this->onUpdateReferenceOption, $deallocate);
		if($this->hasPropertyTypes()){
			$this->release($this->propertyTypes, $deallocate);
		}
		$this->release($this->tableName, $deallocate);
	}
}