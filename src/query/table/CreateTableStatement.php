<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\OrCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\FullTextStringDatum;
use JulianSeymour\PHPWebApplicationFramework\query\DuplicateKeyHandlerTrait;
use JulianSeymour\PHPWebApplicationFramework\query\IfNotExistsFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\UnionClause;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\Constraint;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexDefinition;
use JulianSeymour\PHPWebApplicationFramework\query\index\KeyPart;
use JulianSeymour\PHPWebApplicationFramework\query\partition\CreatePartitionOption;
use JulianSeymour\PHPWebApplicationFramework\query\partition\PartitionDefinition;
use JulianSeymour\PHPWebApplicationFramework\query\partition\PartitionedTrait;
use Exception;

class CreateTableStatement extends QueryStatement implements StaticPropertyTypeInterface{

	use DuplicateKeyHandlerTrait;
	use IfNotExistsFlagBearingTrait;
	use MultipleColumnDefiningTrait;
	use PartitionedTrait;
	use FullTableNameTrait;
	use StaticPropertyTypeTrait;
	
	protected $oldDatabaseName;
	
	protected $oldTableName;

	protected $queryExpression;

	// move this into a trait and combine with similar
	protected $partitionOption;

	protected $subpartitionOption;

	protected $tableOptions;

	// is NOT an array
	public function __construct(...$dbtable){
		parent::__construct();
		$this->unpackTableName($dbtable);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->databaseName, $deallocate);
		$this->release($this->duplicateKeyHandler, $deallocate);
		$this->release($this->oldDatabaseName, $deallocate);
		$this->release($this->oldTableName, $deallocate);
		$this->release($this->partitionOption, $deallocate);
		$this->release($this->queryExpression, $deallocate);
		$this->release($this->subpartitionOption, $deallocate);
		$this->release($this->tableName, $deallocate);
		$this->release($this->tableOptions, $deallocate);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"temporary",
			"ifNotExists"
		]);
	}
	
	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"temporary",
			"ifNotExists"
		]);
	}
	
	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null):array{
		return [
			"columns" => Datum::class,
			"createDefinitions" => new OrCommand(IndexDefinition::class, Constraint::class),
			"partitionDefintions" => PartitionDefinition::class
		];
	}
	
	public function isTemporaryTable():bool{
		return $this->getFlag("temporary");
	}

	public function setTemporaryFlag(bool $value = true):bool{
		return $this->setFlag("temporary", $value);
	}

	public function setTableOptions($tableOptions){
		$f = __METHOD__;
		if(!$tableOptions instanceof TableOptions){
			Debug::error("{$f} table options must be an instanceof TableOptions");
		}elseif($this->hasTableOptions()){
			$this->release($this->tableOptions);
		}
		return $this->tableOptions = $this->claim($tableOptions);
	}

	public function hasTableOptions():bool{
		return isset($this->tableOptions);
	}

	public function getTableOptions(){
		$f = __METHOD__;
		if(!$this->hasTableOptions()){
			Debug::error("{$f} table options are undefined");
		}
		return $this->tableOptions;
	}

	public function setPartitionOption($partition){
		$f = __METHOD__;
		if(!$partition instanceof CreatePartitionOption){
			Debug::error("{$f} partition options must be an instanceof PartitionOption");
		}
		return $this->partitionOption = $partition;
	}

	public function partitionBy(CreatePartitionOption $partition, $count = null): CreatePartitionOption{
		if($count !== NULL){
			$partition->setPartitionCount($count);
		}
		$this->setPartitionOption($partition);
		return $this;
	}

	public function hasPartitionOption():bool{
		return isset($this->partitionOption);
	}

	public function getPartitionOption(): CreatePartitionOption{
		$f = __METHOD__;
		if(!$this->hasPartitionOption()){
			Debug::error("{$f} partition options are undefined");
		}
		return $this->partitionOption;
	}

	public function hasSubpartitionOption():bool{
		return isset($this->subpartitionOption);
	}

	public function getSubpartitionOption(){
		$f = __METHOD__;
		if(!$this->hasSubpartitionOption()){
			Debug::error("{$f} subpartition options are undefined");
		}
		return $this->subpartitionOption;
	}

	public function setSubpartitionOption($partition){
		$f = __METHOD__;
		if(!$partition instanceof CreatePartitionOption){
			Debug::error("{$f} partition options must be an instanceof CreatePartitionOption");
		}
		return $this->subpartitionOption = $partition;
	}

	public function subpartitionBy(CreatePartitionOption $partition, $count = null){
		if($count !== NULL){
			$partition->setPartitionCount($count);
		}
		$this->setSubpartitionOption($partition);
		return $this;
	}

	public function getPartitionOptionsString(){
		$options = $this->getPartitionOption();
		if($options instanceof SQLInterface){
			$options = $options->toSQL();
		}
		$string = " partition by {$options}";
		if($options->hasPartitionCount()){
			$string .= " partitions " . $options->getPartitionCount();
		}
		if($this->hasSubpartitionOption()){
			$options = $this->getSubpartitionOption();
			if($options instanceof SQLInterface){
				$options = $options->toSQL();
			}
			$string .= " subpartition by {$options}";
			if($options->hasPartitionCount()){
				$string .= " subpartitions " . $options->getSubpartitionCount();
			}
		}
		$partitions = [];
		foreach($this->getPartitionDefinitions() as $p){
			if($p instanceof SQLInterface){
				$p = $p->toSQL();
				array_push($partitions, $p);
			}
		}
		$string .= "(" . implode(',', $partitions) . ")";
		return $string;
	}

	public function setQueryExpression($query){
		$f = __METHOD__;
		if(!$query instanceof QueryStatement && ! $query instanceof UnionClause){
			Debug::error("{$f} query expression must be an instanceof SelectStatement or UnionClause");
		}elseif($this->hasQueryExpression()){
			$this->release($this->queryExpression);
		}
		return $this->queryExpression = $this->claim($query);
	}

	public function hasQueryExpression():bool{
		return isset($this->queryExpression);
	}

	public function getQueryExpression(){
		$f = __METHOD__;
		if(!$this->hasQueryExpression()){
			Debug::error("{$f} query expression is undefined");
		}
		return $this->queryExpression;
	}

	public function as($query):CreateTableStatement{
		$this->setQueryExpression($query);
		return $this;
	}

	public function setOldTableName($oldTableName){
		$f = __METHOD__;
		if(!is_string($oldTableName)){
			Debug::error("{$f} old table name must be a string");
		}elseif($this->hasOldTableName()){
			$this->release($this->oldTableName);
		}
		return $this->oldTableName = $this->claim($oldTableName);
	}

	public function hasOldTableName():bool{
		return isset($this->oldTableName);
	}

	public function getOldTableName(){
		$f = __METHOD__;
		if(!$this->hasOldTableName()){
			Debug::error("{$f} old table name is undefined");
		}
		return $this->oldTableName;
	}

	public function hasOldDatabaseName():bool{
		return isset($this->oldDatabaseName);
	}
	
	public function setOldDatabaseName($name){
		if($this->hasOldDatabaseName()){
			$this->release($this->oldDatabaseName);
		}
		return $this->claim($name);
	}
	
	public function getOldDatabaseName(){
		$f = __METHOD__;
		if(!$this->hasOldDatabaseName()){
			Debug::error("{$f} old database name is undefined");
		}
		return $this->oldDatabaseName;
	}
	
	public function like(...$dbtable):CreateTableStatement{
		$f = __METHOD__;
		$count = count($dbtable);
		switch($count){
			case 1:
				$this->setOldTableName($dbtable[0]);
				break;
			case 2:
				$this->setOldDatabaseName($dbtable[0]);
				$this->setOldTableName($dbtable[1]);
				break;
			default:
				Debug::error("{$f} invalid parameter count {$count}");
		}
		return $this;
	}

	public function setCreateDefinitions($values){
		return $this->setArrayProperty("createDefinitions", $values);
	}

	public function pushCreateDefinitions(...$values):int{
		return $this->pushArrayProperty("createDefinitions", ...$values);
	}

	public function mergeCreateDefinitions($values):?array{
		return $this->mergeArrayProperty("createDefinitions", $values);
	}

	public function hasCreateDefinitions():bool{
		return $this->hasArrayProperty("createDefinitions");
	}

	public function getCreateDefinitions(){
		return $this->getProperty("createDefinitions");
	}

	public function getCreateDefinitonCount():int{
		return $this->getArrayPropertyCount("createDefinitions");
	}

	public function unshiftCreateDefinitions(...$values):int{
		return $this->unshiftArrayProperty("createDefinitions", ...$values);
	}

	public function getQueryStatementString(): string{
		$f = __METHOD__;
		try{
			$print = false;
			$string = "create ";
			if($this->isTemporaryTable()){
				$string .= "temporary ";
			}
			$string .= "table ";
			if($this->getIfNotExistsFlag()){
				$string .= "if not exists ";
			}
			if($this->hasDatabaseName()){
				$string .= back_quote($this->getDatabaseName()).".";
			}
			$string .= back_quote($this->getTableName());
			if($this->hasOldTableName()){
				$string .= " like ";
				if($this->hasOldDatabaseName()){
					$string .= back_quote($this->getOldDatabaseName()).".";
				}
				$string .= back_quote($this->getOldTableName());
				return $string;
			}
			if($this->hasColumns()){
				$columns = [];
				foreach($this->getColumns() as $c){
					if($c instanceof SQLInterface){
						$c = $c->toSQL();
						if($print){
							Debug::print("{$f} column is string \"{$c}\"");
						}
					}
					array_push($columns, $c);
				}
				$string .= " (" . implode(',', $columns);
				if($this->hasCreateDefinitions()){
					if($print){
						Debug::print("{$f} about to print create definitions");
						foreach($this->getCreateDefinitions() as $key => $def){
							if($def instanceof SQLInterface){
								$def = $def->toSQL();
							}
							Debug::print("{$f} {$key} : {$def}");
						}
					}
					$definitions = [];
					foreach($this->getCreateDefinitions() as $cd){
						if($cd instanceof SQLInterface){
							if($print){
								$class = $cd->getClass();
								$decl = $cd->getDeclarationLine();
								Debug::print("{$f} {$class} declared \"{$decl}\"");
							}
							$cd = $cd->toSQL();
						}
						if($print){
							if($cd == ""){
								Debug::error("{$f} create definition string is empty");
							}
							Debug::print("{$f} create definition is string \"{$cd}\"");
						}
						array_push($definitions, $cd);
					}
					$string .= ", " . implode(',', $definitions);
				}
				$string .= ")";
			}elseif(!$this->hasQueryExpression()){
				Debug::error("{$f} query must define either a list of columns or a query expression");
			}
			if($this->hasTableOptions()){
				$to = $this->getTableOptions();
				if($to instanceof SQLInterface){
					$to = $to->toSQL();
				}
				$string .= " {$to}";
			}
			if($this->hasPartitionOption()){
				$string .= " " . $this->getPartitionOptionsString();
			}
			if($this->hasDuplicateKeyHandler()){
				$string .= " " . $this->getDuplicateKeyHandler();
			}
			if($this->hasQueryExpression()){
				$qe = $this->getQueryExpression();
				if($qe instanceof SQLInterface){
					$qe = $qe->toSQL();
				}
				$string .= " {$qe}";
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function fromTableDefinition(DataStructure $ds){
		$query = QueryBuilder::createTable(
			$ds->getDatabaseName(), 
			$ds->getTableName()
		)->withColumns(
			array_values($ds->getFilteredColumns(DIRECTIVE_CREATE_TABLE))
		);
		if($ds->hasIndexDefinitions()){
			$query->pushCreateDefinitions(...$ds->getIndexDefinitions());
		}
		if($ds->hasConstraints()){
			$query->pushCreateDefinitions(...$ds->getConstraints());
		}
		return $query;
	}

	public function setColumns($columns){
		$f = __METHOD__;
		$print = false;
		foreach($columns as $datum){
			$cn = $datum->getName();
			iF($print){
				Debug::print("{$f} column \"{$cn}\"");
			}
			if(!$datum instanceof Datum){
				$gottype = gettype($datum);
				Debug::error("{$f} received a \"{$gottype}\"");
			}
			if($datum->hasConstraints()){
				$this->pushCreateDefinitions(...$datum->getConstraints());
			}
			if($datum instanceof FullTextStringDatum && $datum->getFulltextFlag()){
				if($print){
					Debug::print("{$f} column \"{$cn}\" is flagged as a full text index");
				}
				// array_push($fulltext, $datum);
				$class = $datum->getDataStructureClass(); // $fulltext[array_keys($fulltext)[0]]->getDataStructureClass();
				$type = $class::getDataType();
				$index = new IndexDefinition("{$type}_{$cn}_fulltext");
				// foreach($fulltext as $ft){
				$keypart = new KeyPart($cn);
				if($datum->hasMaximumLength()){
					$keypart->setLength($datum->getMaximumLength());
				}
				$index->pushKeyParts($keypart);
				$index->setIndexType(INDEX_TYPE_FULLTEXT);
				// }
				$this->pushCreateDefinitions($index);
			}elseif($datum->getIndexFlag()){
				if($print){
					Debug::print("{$f} column \"{$cn}\" is flagged as an index");
				}
				$this->pushCreateDefinitions($datum->generateIndexDefinition());
			}elseif($print){
				Debug::print("{$f} neither of the above for column \"{$cn}\"");
			}
		}
		return $this->setArrayProperty("columns", $columns);
	}

	public function setTableName($tableName){
		if($this->hasTableName()){
			$this->release($this->tableName);
		}
		return $this->tableName = $this->claim($tableName);
	}
}
