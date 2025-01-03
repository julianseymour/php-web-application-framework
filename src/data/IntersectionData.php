<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\debug;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\is_abstract;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\query\database\StaticDatabaseNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\database\StaticDatabaseNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use mysqli;

class IntersectionData extends DataStructure implements StaticDatabaseNameInterface{

	use StaticDatabaseNameTrait;
	
	protected $foreignDataStructureClass;

	protected $hostDataStructureClass;

	protected static function getExcludedConstructorFunctionNames(): ?array{
		return array_merge(parent::getExcludedConstructorFunctionNames(), [
			"generateIntersectionData", 
			"getAllPossibleIntersectionData"
		]);
	}
		
	public function __construct(?string $hostClass=null, ?string $foreignClass=null, ?string $relationship=null, ?int $mode = ALLOCATION_MODE_EAGER){
		$f = __METHOD__;
		parent::__construct($mode);
		if($hostClass !== null){
			if(!is_a($hostClass, DataStructure::class, true)){
				Debug::error("{$f} host class must be a DataStructure class");
			}
			$this->setHostDataStructureClass($hostClass);
			if(!$this->hasHostDataStructureClass()){
				Debug::error("{$f} host data structure class is undefined");
			}
		}
		if($foreignClass !== null){
			if(!is_a($foreignClass, DataStructure::class, true)){
				Debug::error("{$f} foreign class \"{$foreignClass}\" must be a DataStructure class");
			}
			$this->setForeignDataStructureClass($foreignClass);
			if(!$this->hasForeignDataStructureClass()){
				Debug::error("{$f} foreign data structure class is undefined");
			}
		}
		if($relationship !== null){
			$this->setRelationship($relationship);
		}
	}

	public static function getKeyGenerationMode(): int{
		return KEY_GENERATION_MODE_UNIDENTIFIABLE;
	}

	public static function getIdentifierNameStatic():?string{
		return null;
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try{
			//parent::declareColumns($columns, $ds);
			$hostKey = new ForeignKeyDatum("hostKey");
			$hostKey->setPersistenceMode(PERSISTENCE_MODE_DATABASE);
			$hostKey->constrain();
			// $hostKey->setOnUpdate(
			$hostKey->setOnDelete(REFERENCE_OPTION_CASCADE); // );
			$hostKey->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
			$foreignKey = new ForeignKeyDatum("foreignKey");
			$foreignKey->setPersistenceMode(PERSISTENCE_MODE_DATABASE);
			$foreignKey->constrain();
			// $foreignKey->setOnUpdate(
			$foreignKey->setOnDelete(REFERENCE_OPTION_CASCADE); // );
			$foreignKey->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
			if(!BACKWARDS_REFERENCES_ENABLED){
				$hostKey->setRank(RANK_PARENT);
				$foreignKey->setRank(RANK_PARENT);
			}
			$relationship = new TextDatum("relationship");
			//$relationship->debug();
			//$ds->announce($relationship);
			array_push($columns, $hostKey, $foreignKey, $relationship);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		$print = false;
		parent::reconfigureColumns($columns, $ds);
		$keep = [
			"hostKey",
			"foreignKey",
			"relationship"
		];
		foreach($columns as $name => $column){
			if(in_array($name, $keep, true)){
				continue;
			}
			$column->volatilize();
		}
	}

	public static function getCompositeUniqueColumnNames(): ?array{
		return [
			[
				"hostKey",
				"foreignKey",
				"relationship"
			]
		];
	}

	public function setForeignDataStructureClass(string $fdsc):string{
		$f = __METHOD__;
		if(is_object($fdsc)){
			$fdsc = $fdsc->getClass();
		}elseif(!is_string($fdsc)){
			Debug::error("{$f} received something that is not a string");
		}elseif(empty($fdsc)){
			Debug::error("{$f} received empty string");
		}elseif(!class_exists($fdsc)){
			Debug::error("{$f} class \"{$fdsc}\" does not exist");
		}elseif(is_abstract($fdsc)){
			Debug::error("{$f} abstract class \"{$fdsc}\"");
		}elseif($this->hasForeignDataStructureClass()){
			$this->release($this->foreignDataStructureClass);
		}
		$foreign_key = $this->getColumn("foreignKey");
		return $this->foreignDataStructureClass = $foreign_key->setForeignDataStructureClass($this->claim($fdsc));
	}

	public function hasForeignDataStructureClass():bool{
		return isset($this->foreignDataStructureClass);
	}

	public function getTableName(): string{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasTableName()){
			$fdsc = $this->getForeignDataStructureClass();
			if(!method_exists($fdsc, 'getTableNameStatic')){
				Debug::error("{$f} table name cannot be determined statically for foreign data structure class \"{$fdsc}\"");
			}
			$foreign = $fdsc::getTableNameStatic();
			$hdsc = $this->getHostDataStructureClass();
			$rel = $this->getRelationship();
			if($this->hasHostDataStructure()){
				if($print){
					Debug::print("{$f} host data structure is defined");
				}
				$hds = $this->getHostDataStructure();
				$column = $hds->getColumn($rel);
				$htn = $hds->getTableName();
			}else{
				if($print){
					Debug::print("{$f} host data structure is undefined, instantiating a dummy now");
				}
				$dummy = new $hdsc();
				$column = $dummy->getColumn($rel);
				if(!method_exists($hdsc, 'getTableNameStatic')){
					Debug::error("{$f} table name cannot be determined statically for host data structure class \"{$hdsc}\"");
				}
				$htn = $hdsc::getTableNameStatic();
			}
			if($column->hasEmbeddedName()){
				if($print){
					Debug::print("{$f} column has an embedded name");
				}
				$tn = "{$htn}_".$column->getEmbeddedName() . "_{$foreign}";
			}else{
				if($print){
					Debug::print("{$f} column does not have an embedded name");
				}
				$tn = "{$htn}_{$foreign}";
			}
			if(!$this->hasHostDataStructure()){
				if($print){
					Debug::print("{$f} host data structure is still undefined");
				}
				deallocate($dummy);
				if($print){
					Debug::print("{$f} deallocated dummy data structure");
				}
			}elseif($print){
				Debug::print("{$f} we got our host table name from our host data structure");
			}
			if($print){
				Debug::print("{$f} intersection table name is \"{$tn}\"");
			}
			return $this->setTableName($tn);
		}
		if($print){
			Debug::print("{$f} intersection table name is \"{$this->tableName}\"");
		}
		return $this->tableName;
	}

	public static function getDatabaseNameStatic(): string{
		return "intersections";
	}

	public static function getPermissionStatic(string $name, $data){
		switch($name){
			case DIRECTIVE_INSERT:
			case DIRECTIVE_UPDATE:
			case DIRECTIVE_CREATE_TABLE:
				return SUCCESS;
			default:
				return parent::getPermissionStatic($name, $data);
		}
	}

	public function getForeignDataStructureClass():string{
		$f = __METHOD__;
		if(!$this->hasForeignDataStructureClass()){
			Debug::error("{$f} foreign data structure class is undefined");
		}
		return $this->foreignDataStructureClass;
	}

	public function setHostDataStructure(?DataStructure $host): ?DataStructure{
		return $this->setForeignDataStructure("hostKey", $host);
	}

	public function hasHostDataStructure(): bool{
		return $this->hasForeignDataStructure("hostKey");
	}

	public function getHostDataStructure(): DataStructure{
		return $this->getForeignDataStructure("hostKey");
	}

	public function setHostDataStructureClass(string $hdsc):string{
		$f = __METHOD__;
		if(is_object($hdsc)){
			$hdsc = $hdsc->getClass();
		}elseif(!is_string($hdsc)){
			Debug::error("{$f} received something that is not a string");
		}elseif(empty($hdsc)){
			Debug::error("{$f} received empty string");
		}elseif(!class_exists($hdsc)){
			Debug::error("{$f} class \"{$hdsc}\" does not exist");
		}elseif(!is_a($hdsc, DataStructure::class, true)){
			Debug::error("{$f} host data structure class is not one of a DataStructure");
		}elseif(is_abstract($hdsc)){
			Debug::error("{$f} abstract class \"{$hdsc}\"");
		}
		$host_key = $this->getColumn("hostKey");
		if($this->hasHostDataStructureClass()){
			$this->release($this->hostDataStructureClass);
		}
		return $this->hostDataStructureClass = $host_key->setForeignDataStructureClass($this->claim($hdsc));
	}

	public function hasHostDataStructureClass():bool{
		return isset($this->hostDataStructureClass);
	}

	public function getHostDataStructureClass():string{
		$f = __METHOD__;
		if(!$this->hasHostDataStructureClass()){
			if($this->hasHostDataStructure()){
				return $this->getHostDataStructure()->getClass();
			}
			Debug::error("{$f} host data structure class is undefined");
		}
		return $this->hostDataStructureClass;
	}

	public function setRelationship(string $value):string{
		return $this->setColumnValue("relationship", $value);
	}

	public function hasRelationship():bool{
		return $this->hasColumnValue("relationship");
	}

	public function getRelationship():string{
		$f = __METHOD__;
		if(!$this->hasRelationship()){
			Debug::error("{$f} foreign key name is undefined");
		}
		return $this->getColumnValue("relationship");
	}

	public function setHostKey(string $value):string{
		return $this->setColumnValue("hostKey", $value);
	}

	public function hasHostKey():bool{
		return $this->hasColumnValue("hostKey");
	}

	public function getHostKey():string{
		$f = __METHOD__;
		if(!$this->hasHostKey()){
			Debug::error("{$f} host key is undefined");
		}
		return $this->getColumnValue("hostKey");
	}

	public function setForeignKey(string $value):string{
		return $this->setColumnValue("foreignKey", $value);
	}

	public function hasForeignKey():bool{
		return $this->hasColumnValue("foreignKey");
	}

	public function getForeignKey():string{
		$f = __METHOD__;
		if(!$this->hasForeignKey()){
			Debug::error("{$f} foreign key is undefined");
		}
		return $this->getColumnValue("foreignKey");
	}

	public function insert(mysqli $mysqli): int{
		$f = __METHOD__;
		if(!$this->hasForeignKey()){
			Debug::error("{$f} foreign key is undefined");
		}elseif(!is_a($this->getHostDataStructureClass(), EventSourceData::class, true)){
			$hdsc = $this->getHostDataStructureClass();
			if(!method_exists($hdsc, 'getTableNameStatic')){
				Debug::error("{$f} table name cannot be determined statically for host data structure class \"{$hdsc}\"");
			}
			$select = new SelectStatement();
			if(
				$select->from(
					$hdsc::getDatabaseNameStatic(), 
					$hdsc::getTableNameStatic()
				)->where(
					new WhereCondition(
						$this->getHostDataStructureClass()::getIdentifierNameStatic(), 
						OPERATOR_EQUALS
					)
				)->withParameters(
					$this->getHostKey()
				)->withTypeSpecifier('s')->executeGetResultCount($mysqli) !== 1
			){
				Debug::error("{$f} host data structure of class ".get_short_class($this->getHostDataStructureClass())." with key ".$this->getHostKey()." does not exist");
			}else{
				$fdsc = $this->getForeignDataStructureClass();
				if(!method_exists($fdsc, 'getTableNameStatic')){
					Debug::error("{$f} table name cannot be determined statically for foreign data structure class \"{$fdsc}\"");
				}
				deallocate($select);
				$select = new SelectStatement();
				if(
					$select->from(
						$fdsc::getDatabaseNameStatic(),
						$fdsc::getTableNameStatic()
					)->where(
						new WhereCondition(
							$this->getForeignDataStructureClass()::getIdentifierNameStatic(),
							OPERATOR_EQUALS
						)
					)->withParameters(
						$this->getForeignKey()
					)->withTypeSpecifier('s')->executeGetResultCount($mysqli) !== 1
				){
					Debug::error("{$f} foreign data structure of class ".get_short_class($fdsc)." with key ".$this->getForeignKey()." and relationship ".$this->getRelationship()." does not exist in the database. Host data structure has class ".$hdsc::getShortClass()." and key ".$this->getHostKey().". Foreign data structure was instantiated ".registry()->get($this->getForeignKey())->getDeclarationLine());
				}
				deallocate($select);
			}
		}
		return parent::insert($mysqli);
	}

	public static function getPrettyClassName():string{
		return _("Intersection data");
	}

	public static function getDataType(): string{
		return DATATYPE_INTERSECTION;
	}

	public static function getPrettyClassNames():string{
		return _("Intersection data");
	}

	public static function getPhylumName(): string{
		return "intersections";
	}
	
	public function logDatabaseOperation(string $directive): int{
		if($this->getFlag("disableLog")){
			return 0;
		}
		$class = static::getShortClass();
		if($this->hasHostDataStructureClass()){
			$class .= " between " . $this->getHostDataStructureClass()::getShortClass();
			if($this->hasHostKey()){
				$class .= " (with host key \"" . $this->getHostKey() . "\")";
			}
			if($this->hasForeignDataStructureClass()){
				$class .= " and " . $this->getForeignDataStructureClass()::getShortClass();
				if($this->hasForeignKey()){
					$class .= " (with foreign key \"" . $this->getForeignKey() . "\")";
				}
			}
		}
		$idn = $this->getIdentifierName();
		$key = $idn === null ? "[unidentifiable]" : $this->getIdentifierValue();
		$did = $this->getDebugId();
		$decl = $this->getDeclarationLine();
		return debug()->digest("{$directive} {$class} with key {$key} (debug ID {$did}, declared {$decl})");
	}
	
	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->foreignDataStructureClass, $deallocate);
		$this->release($this->hostDataStructureClass, $deallocate);
	}
}
