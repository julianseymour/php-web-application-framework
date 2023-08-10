<?php
namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\is_abstract;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use mysqli;

class IntersectionData extends DataStructure{

	protected $foreignDataStructureClass;

	protected $hostDataStructureClass;

	public function __construct($hostClass, $foreignClass, string $relationship, ?int $mode = ALLOCATION_MODE_EAGER){
		$f = __METHOD__;
		if (! is_a($hostClass, DataStructure::class, true)) {
			Debug::error("{$f} host class must be a DataStructure class");
		} elseif (! is_a($foreignClass, DataStructure::class, true)) {
			Debug::error("{$f} foreign class \"{$foreignClass}\" must be a DataStructure class");
		}
		parent::__construct($mode);
		$this->setHostDataStructureClass($hostClass);
		$this->setForeignDataStructureClass($foreignClass);
		if (! $this->hasHostDataStructureClass()) {
			Debug::error("{$f} host data structure class is undefined");
		} elseif (! $this->hasForeignDataStructureClass()) {
			Debug::error("{$f} foreign data structure class is undefined");
		}
		$this->setRelationship($relationship);
	}

	public static function getKeyGenerationMode(): int{
		return KEY_GENERATION_MODE_HASH;
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try {
			parent::declareColumns($columns, $ds);
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
			$relationship = new TextDatum("relationship");
			static::pushTemporaryColumnsStatic($columns, $hostKey, $foreignKey, $relationship);
		} catch (Exception $x) {
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
		foreach ($columns as $name => $column) {
			if (in_array($name, $keep, true)) {
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

	public function setForeignDataStructureClass($fdsc){
		$f = __METHOD__;
		if (is_object($fdsc)) {
			$fdsc = $fdsc->getClass();
		} elseif (! is_string($fdsc)) {
			Debug::error("{$f} received something that is not a string");
		} elseif (empty($fdsc)) {
			Debug::error("{$f} received empty string");
		} elseif (! class_exists($fdsc)) {
			Debug::error("{$f} class \"{$fdsc}\" does not exist");
		} elseif (is_abstract($fdsc)) {
			Debug::error("{$f} abstract class \"{$fdsc}\"");
		}
		$foreign_key = $this->getColumn("foreignKey");
		return $this->foreignDataStructureClass = $foreign_key->setForeignDataStructureClass($fdsc);
	}

	public function hasForeignDataStructureClass(){
		return isset($this->foreignDataStructureClass);
	}

	public function getTableName(): string{
		$f = __METHOD__;
		$print = false;
		if (! $this->hasTableName()) {
			$foreign = $this->getForeignDataStructureClass()::getTableNameStatic();
			$hdsc = $this->getHostDataStructureClass();
			$rel = $this->getRelationship();
			if ($this->hasHostDataStructure()) {
				$hds = $this->getHostDataStructure();
				$column = $hds->getColumn($rel);
			} else {
				$column = $hdsc::getColumnStatic($rel);
			}
			if ($this->hasHostDataStructure()) {
				$htn = $hds->getTableName();
			} else {
				$htn = $hdsc::getTableNameStatic();
			}
			if ($column->hasEmbeddedName()) {
				$tn = "{$htn}_" . $column->getEmbeddedName() . "_{$foreign}";
			} else {
				$tn = "{$htn}_{$foreign}";
			}
			if ($print) {
				Debug::print("{$f} intersection table name is \"{$tn}\"");
			}
			return $this->setTableName($tn);
		}
		if ($print) {
			Debug::print("{$f} intersection table name is \"{$this->tableName}\"");
		}
		return $this->tableName;
	}

	public static function getDatabaseNameStatic(): string{
		return "intersections";
	}

	public static function getPermissionStatic(string $name, $data){
		switch ($name) {
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
		if (! $this->hasForeignDataStructureClass()) {
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

	public function setHostDataStructureClass($hdsc){
		$f = __METHOD__;
		if (is_object($hdsc)) {
			$hdsc = $hdsc->getClass();
		} elseif (! is_string($hdsc)) {
			Debug::error("{$f} received something that is not a string");
		} elseif (empty($hdsc)) {
			Debug::error("{$f} received empty string");
		} elseif (! class_exists($hdsc)) {
			Debug::error("{$f} class \"{$hdsc}\" does not exist");
		} elseif (! is_a($hdsc, DataStructure::class, true)) {
			Debug::error("{$f} host data structure class is not one of a DataStructure");
		} elseif (is_abstract($hdsc)) {
			Debug::error("{$f} abstract class \"{$hdsc}\"");
		}
		$host_key = $this->getColumn("hostKey");
		return $this->hostDataStructureClass = $host_key->setForeignDataStructureClass($hdsc);
	}

	public function hasHostDataStructureClass(){
		return isset($this->hostDataStructureClass);
	}

	public function getHostDataStructureClass(){
		$f = __METHOD__;
		if (! $this->hasHostDataStructureClass()) {
			if ($this->hasHostDataStructure()) {
				return $this->getHostDataStructure()->getClass();
			}
			Debug::error("{$f} host data structure class is undefined");
		}
		return $this->hostDataStructureClass;
	}

	public function setRelationship($value){
		return $this->setColumnValue("relationship", $value);
	}

	public function hasRelationship(){
		return $this->hasColumnValue("relationship");
	}

	public function getRelationship(){
		$f = __METHOD__;
		if (! $this->hasRelationship()) {
			Debug::error("{$f} foreign key name is undefined");
		}
		return $this->getColumnValue("relationship");
	}

	public function setHostKey($value){
		return $this->setColumnValue("hostKey", $value);
	}

	public function hasHostKey(){
		return $this->hasColumnValue("hostKey");
	}

	public function getHostKey(){
		$f = __METHOD__;
		if (! $this->hasHostKey()) {
			Debug::error("{$f} host key is undefined");
		}
		return $this->getColumnValue("hostKey");
	}

	public function setForeignKey($value){
		return $this->setColumnValue("foreignKey", $value);
	}

	public function hasForeignKey(){
		return $this->hasColumnValue("foreignKey");
	}

	public function getForeignKey(){
		$f = __METHOD__;
		if (! $this->hasForeignKey()) {
			Debug::error("{$f} foreign key is undefined");
		}
		return $this->getColumnValue("foreignKey");
	}

	public function insert(mysqli $mysqli): int{
		$f = __METHOD__;
		if (! $this->hasForeignKey()) {
			Debug::error("{$f} foreign key is undefined");
		}elseif(!is_a($this->getHostDataStructureClass(), EventSourceData::class, true)){
			if(
				QueryBuilder::select()->from(
					$this->getHostDataStructureClass()::getDatabaseNameStatic(), 
					$this->getHostDataStructureClass()::getTableNameStatic()
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
			}elseif(
				QueryBuilder::select()->from(
					$this->getForeignDataStructureClass()::getDatabaseNameStatic(),
					$this->getForeignDataStructureClass()::getTableNameStatic()
				)->where(
					new WhereCondition(
						$this->getForeignDataStructureClass()::getIdentifierNameStatic(),
						OPERATOR_EQUALS
					)
				)->withParameters($this->getForeignKey())->withTypeSpecifier('s')->executeGetResultCount($mysqli) !== 1
			){
				Debug::error("{$f} foreign data structure of class ".get_short_class($this->getForeignDataStructureClass())." with key ".$this->getForeignKey()." and relationship ".$this->getRelationship()." does not exist in the database. Host data structure has class ".$this->getHostDataStructureClass()." and key ".$this->getHostKey().". Foreign data structure was instantiated ".registry()->get($this->getForeignKey())->getDeclarationLine());
			}
		}
		
		return parent::insert($mysqli);
	}

	public static function getPrettyClassName(?string $lang = null){
		return _("Intersection data");
	}

	public static function getTableNameStatic(): string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public static function getDataType(): string{
		return DATATYPE_INTERSECTION;
	}

	public static function getPrettyClassNames(?string $lang = null){
		return _("Intersection data");
	}

	public static function getPhylumName(): string{
		return "intersections";
	}
	
	public function dispose(): void{
		parent::dispose();
		unset($this->foreignDataStructureClass);
		unset($this->hostDataStructureClass);
	}
}
