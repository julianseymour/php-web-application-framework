<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\CollatedTrait;
use JulianSeymour\PHPWebApplicationFramework\query\CommentTrait;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\SecondaryEngineAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\PrimaryKeyFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\UniqueFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\VisibilityTrait;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\CheckConstraint;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\ConstrainableTrait;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\PrimaryKeyConstraint;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\UniqueConstraint;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexDefinition;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\query\index\KeyPart;
use JulianSeymour\PHPWebApplicationFramework\validate\ValidationClosureTrait;
use Exception;

trait ColumnDefinitionTrait{
	
	use AbstractColumnDefinitionTrait;
	use CollatedTrait;
	use CommentTrait;
	use ConstrainableTrait;
	use IndexNameTrait;
	use IndexTypeTrait;
	use NamedTrait;
	use PrimaryKeyFlagBearingTrait;
	use ReplicableTrait;
	use SecondaryEngineAttributeTrait;
	use UniqueFlagBearingTrait;
	use ValidationClosureTrait;
	use VisibilityTrait;
	
	abstract static function getTypeSpecifier():string;
	abstract static function parseString(string $string);
	abstract function getColumnTypeString():string;
	abstract static function validateStatic($value): int;
	
	/**
	 * specifies COLUMN_FORMAT part of declaration string
	 *
	 * @var string
	 */
	protected $columnFormatType;
	
	/**
	 * Used in create table query generation.
	 * Not to be confused with $persistenceMode.
	 * Only applies to datums with $persistenceMode === PERSISTENCE_MODE_DATABASE
	 */
	protected $databaseStorageType;
	
	/**
	 * expression for generating values of datums with GENERATED ALWAYS AS in their declaration string
	 *
	 * @var ExpressionCommand
	 */
	protected $generatedAlwaysAsExpression;
	
	/**
	 * needed to make aliased columns searchable
	 */
	protected $referenceColumn;
	
	protected $referenceColumnName;
	
	
	
	public function setGeneratedAlwaysAsExpression($expression){
		if($this->hasGeneratedAlwaysAsExpression()){
			$this->release($this->generatedAlwaysAsExpression);
		}
		return $this->generatedAlwaysAsExpression = $this->claim($expression);
	}
	
	public function hasGeneratedAlwaysAsExpression():bool{
		return isset($this->generatedAlwaysAsExpression);
	}
	
	public function getGeneratedAlwaysAsExpression(){
		$f = __METHOD__;
		if(!$this->hasGeneratedAlwaysAsExpression()){
			Debug::error("{$f} generated always as expression is undefined");
		}
		return $this->generatedAlwaysAsExpression;
	}
	
	public function generatedAlwaysAs($expression){
		$this->setGeneratedAlwaysAsExpression($expression);
		return $this;
	}
	
	public function setColumnFormat($type){
		$f = __METHOD__;
		if($this->hasColumnFormat()){
			$this->release($this->columnFormatType);
		}
		if(!is_string($type)){
			Debug::error("{$f} input parameter must be a string");
		}
		$type = strtolower($type);
		switch($type){
			case COLUMN_FORMAT_DEFAULT:
			case COLUMN_FORMAT_DYNAMIC:
			case COLUMN_FORMAT_FIXED:
				break;
			default:
				Debug::error("{$f} invalid column format \"{$type}\"");
		}
		return $this->columnFormatType = $this->claim($type);
	}
	
	public function hasColumnFormat():bool{
		return isset($this->columnFormatType);
	}
	
	public function getColumnFormat(){
		$f = __METHOD__;
		if(!$this->hasColumnFormat()){
			Debug::error("{$f} column format is undefined");
		}
		return $this->columnFormatType;
	}
	
	public function columnFormat($type){
		$this->setColumnFormat($type);
		return $this;
	}
	
	public function setDatabaseStorage($type){
		$f = __METHOD__;
		if($this->hasDatabaseStorage()){
			$this->release($this->databaseStorageType);
		}
		if(!is_string($type)){
			Debug::error("{$f} database storage type must be a string");
		}
		$type = strtolower($type);
		switch($type){
			case DATABASE_STORAGE_DISK:
			case DATABASE_STORAGE_MEMORY:
				if($this->hasGeneratedAlwaysExpression()){
					Debug::error("{$f} generated columns cannot specify storage to disk or memory");
				}
			case DATABASE_STORAGE_GENERATED_STORED:
			case DATABASE_STORAGE_GENERATED_VIRTUAL:
				// allowed regardless of generated always expression which may be set after this variable
				break;
			default:
				Debug::error("{$f} invalid database storage type \"{$type}\"");
		}
		return $this->databaseStorageType = $this->claim($type);
	}
	
	public function hasDatabaseStorage():bool{
		return isset($this->databaseStorageType);
	}
	
	public function getDatabaseStorage(){
		$f = __METHOD__;
		if(!$this->hasDatabaseStorage()){
			Debug::error("{$f} database storage is undefined");
		}
		return $this->databaseStorageType;
	}
	
	public function databaseStorage($type){
		$this->setDatabaseStorage($type);
		return $type;
	}
	
	public function withDefaultValue($value){
		$this->setDefaultValue($value);
		return $this;
	}
	
	/**
	 * Generate an IndexDefinition object for this datum.
	 * XXX TODO IndexDefinitions have engine attributes, key block size, visibility and comments,
	 * but they are not necessarily the same as the ones declared for this column.
	 * They also have a parser name for full text indices.
	 *
	 * @return IndexDefinition
	 */
	public function generateIndexDefinition(){
		$f = __METHOD__;
		try{
			$cn = $this->getName();
			if($this->hasIndexName()){
				$name = $this->getIndexName();
			}else{
				$ds = $this->getDataStructure();
				$type = $ds->getTableName();
				$name = "{$type}_{$cn}_index";
			}
			$index = new IndexDefinition($name);
			if($this->hasIndexType()){
				$index->setIndexType($this->getIndexType());
			}
			if($this instanceof StringDatum){
				$length = $this->getMaximumLength();
			}else{
				$length = null;
			}
			$keypart = new KeyPart($cn, $length);
			$index->setKeyParts([
				$keypart
			]);
			if($this->isPrimaryKey()){
				return new PrimaryKeyConstraint($index);
			}elseif($this->getUniqueFlag()){
				return new UniqueConstraint($index);
			}
			return $index;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	// XXX TODO missing reference definition
	public function toSQL(): string{
		$f = __METHOD__;
		try{
			$print = false;
			$string = back_quote($this->getName()) . " " . $this->getColumnTypeString();
			if($this->hasGeneratedAlwaysAsExpression()){
				if($print){
					Debug::print("{$f} this column has a generated always as expression");
				}
				// [COLLATE collation_name]
				if($this->hasCollationName()){
					$string .= " collate " . $this->getCollationName();
				}
				// [GENERATED ALWAYS] AS (expr)
				$expr = $this->getGeneratedAlwaysAsExpression();
				if($expr instanceof SQLInterface){
					$expr = $expr->toSQL();
				}
				$string .= " as ({$expr})";
				// [VIRTUAL | STORED] [NOT NULL | NULL]
				if($this->hasDatabaseStorage()){
					$string .= " " . $this->getDatabaseStorage();
					if(!$this->isNullable()){
						$string .= " not null";
					}
				}
				// [VISIBLE | INVISIBLE]
				if($this->hasVisibility()){
					$string .= " " . $this->getVisibility();
				}
				// [UNIQUE [KEY]] [[PRIMARY] KEY]
				if($this->getUniqueFlag()){
					$string .= " unique";
				}
				if($this->isPrimaryKey()){
					$string .= " primary key";
				}
				// [COMMENT 'string']
				if($this->hasComment()){
					$string .= " comment " . single_quote($this->getComment());
				}
			}else{
				if($print){
					Debug::print("{$f} generated always as expressions is undefined");
				}
				// data_type [NOT NULL | NULL] [DEFAULT {literal | (expr)} ]
				if(!$this->isNullable()){
					$string .= " not null";
				}
				if($this->hasDefaultValue() && $this->canHaveDefaultValue()){
					$string .= " default " . $this->getDefaultValueString();
				}
				// [VISIBLE | INVISIBLE]
				if($this->hasVisibility()){
					$string .= " " . $this->getVisibility();
				}
				// [AUTO_INCREMENT] [UNIQUE [KEY]] [[PRIMARY] KEY]
				if($this instanceof IntegerDatum && $this->getAutoIncrementFlag()){
					$string .= " auto_increment";
				}
				if($this->getUniqueFlag()){
					$string .= " unique";
				}
				if($this->isPrimaryKey()){
					$string .= " primary key";
				}
				// [COMMENT 'string']
				if($this->hasComment()){
					$string .= " comment " . single_quote($this->getComment());
				}
				// [COLLATE collation_name]
				if($this->hasCollationName()){
					$string .= " collate " . $this->getCollationName();
				}
				// [COLUMN_FORMAT {FIXED | DYNAMIC | DEFAULT}]
				if($this->hasColumnFormat()){
					$string .= " column_format " . $this->getColumnFormat();
				}
				// [ENGINE_ATTRIBUTE [=] 'string']
				if($this->hasEngineAttribute()){
					$string .= " engine_attribute " . single_quote($this->getEngineAttribute());
					// [SECONDARY_ENGINE_ATTRIBUTE [=] 'string']]
					if($this->hasSecondaryEngineAttribute()){
						$string .= " secondary_engine_attribute " . single_quote($this->getSecondaryEngineAttribute());
					}
				}
				// [STORAGE {DISK | MEMORY}]
				if($this->hasDatabaseStorage()){
					$string .= " storage " . $this->getDatabaseStorage();
				}
			}
			if(false && $this->hasReferenceDefinition()){
				// [reference_definition]
				// XXX TODO unimplemented
			}
			if($this->hasConstraints()){
				// [check_constraint_definition]
				foreach($this->getConstraints() as $constraint){
					if(!$constraint instanceof CheckConstraint){
						continue;
					}
					$string .= " {$constraint}";
				}
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function getPrimaryKeyFlag():bool{
		return $this->getFlag(COLUMN_FILTER_PRIMARY_KEY);
	}
	
	public function setReferenceColumn(?ForeignKeyDatum $column): ?ForeignKeyDatum{
		if($this->hasReferenceColumn()){
			$this->release($this->referenceColumn);
		}
		return $this->referenceColumn = $this->claim($column);
	}
	
	public function hasReferenceColumn(): bool{
		return isset($this->referenceColumn);
	}
	
	public function getReferenceColumn(): ForeignKeyDatum{
		$f = __METHOD__;
		if(!$this->hasReferenceColumn()){
			Debug::error("{$f} reference column is undefined");
		}
		return $this->referenceColumn;
	}
	
	public function setReferenceColumnName(?string $rcn): ?string{
		if($this->hasReferenceColumnName()){
			$this->release($this->referenceColumnName);
		}
		return $this->referenceColumnName = $this->claim($rcn);
	}
	
	public function hasReferenceColumnName(): bool{
		return isset($this->referenceColumnName);
	}
	
	public function getReferenceColumnName(): string{
		$f = __METHOD__;
		if(!$this->hasReferenceColumnName()){
			Debug::error("{$f} reference column name is undefined");
		}elseif($this->hasReferenceColumn()){
			return $this->getReferenceColumn()->getName();
		}
		return $this->referenceColumnName;
	}
	
	public function setIndexFlag(bool $value = true): bool{
		return $this->setFlag(COLUMN_FILTER_INDEX, $value);
	}
	
	public function getIndexFlag(): bool{
		return $this->getFlag(COLUMN_FILTER_INDEX);
	}
	
	public function index(bool $value = true): Datum{
		$this->setIndexFlag($value);
		return $this;
	}
	
	public function validate($v): int{
		$f = __METHOD__;
		$print = false;
		if($this->hasValidationClosure()){
			if($print){
				Debug::print("{$f} this datum has a validation closure");
			}
			$closure = $this->getValidationClosure();
			$status = $closure($v, $this);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} validation closure returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		}elseif($print){
			Debug::print("{$f} this datum does not have a validation closure");
		}
		return static::validateStatic($v);
	}
	
	public static function getDatabaseEncodedValueStatic($value){
		return $value;
	}
	
	public function cast($v){
		return $v;
	}
}