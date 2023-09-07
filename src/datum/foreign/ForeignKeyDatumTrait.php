<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum\foreign;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableTrait;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\core\ClassResolver;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\EventSourceData;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionData;
use JulianSeymour\PHPWebApplicationFramework\datum\AbstractDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\ForeignKeyConstraintTrait;
use JulianSeymour\PHPWebApplicationFramework\query\index\KeyPartsTrait;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateFlagTrait;
use Exception;
use mysqli;

/**
 * common behavior shared by ForeignKeyDatum, KeyListDatum and ForeignMetadataBundle
 *
 * @author j
 *        
 */
trait ForeignKeyDatumTrait{

	use ForeignKeyConstraintTrait;
	use KeyPartsTrait;
	use CacheableTrait;
	use TemplateFlagTrait;

	/**
	 * name of the foreign data structure's identifying column
	 *
	 * @var string
	 */
	protected $foreignDataIdentifierName;

	/**
	 * a singular foreign data structure class for non-polymorphic foreign keys
	 *
	 * @var string
	 */
	protected $foreignDataStructureClass;

	/**
	 * class name of a IntersectionTableResolver that resolves this column's possible polymorphic classes
	 *
	 * @var string
	 */
	protected $foreignDataStructureClassResolver;

	/**
	 * define this when there is no foreign data type datum and only subtype matters
	 *
	 * @var string
	 */
	protected $foreignDataType;

	/**
	 * name of the primary type hint used to determine class of the foreign data structure
	 *
	 * @var string
	 */
	protected $foreignDataTypeName;

	/**
	 * name of the secondary type hint used to determine class of the foreign data structure
	 *
	 * @var string
	 */
	protected $foreignDataSubtypeName;

	/**
	 *
	 * @var string
	 */
	protected $converseRelationshipKeyName;

	/**
	 * RELATIONSHIP_TYPE_ONE_TO_ONE, RELATIONSHIP_TYPE_ONE_TO_MANY, RELATIONSHIP_TYPE_MANY_TO_ONE or RELATIONSHIP_TYPE_MANY_TO_MANY
	 *
	 * @var int
	 */
	protected $relationshipType;

	/**
	 * CONST_BEFORE or CONST_AFTER
	 *
	 * @var string
	 */
	protected $relativeSequence;

	protected $updateBehavior;

	/**
	 * maps class name => column name to contract vertices when this column's row is deleted
	 *
	 * @var array
	 */
	protected $vertexContractions;

	public function setForeignDataIdentifierName(?string $idn): ?string{
		if ($idn == null) {
			unset($this->foreignDataIdentifierName);
			return null;
		}
		return $this->foreignDataIdentifierName = $idn;
	}

	public function hasForeignDataIdentifierName(): bool{
		return isset($this->foreignDataIdentifierName);
	}

	public function getForeignDataIdentifierName(): string{
		$f = __METHOD__;
		if (! $this->hasForeignDataIdentifierName()) {
			Debug::error("{$f} foreign data identifier name is undefined");
		}
		return $this->foreignDataIdentifierName;
	}

	public function setConstraintFlag(bool $value = true): bool{
		return $this->setFlag(COLUMN_FILTER_CONSTRAIN, true);
	}

	public function getConstraintFlag(): bool{
		return $this->getFlag(COLUMN_FILTER_CONSTRAIN);
	}

	public function setContractVertexFlag(bool $value = true): bool{
		return $this->setFlag(COLUMN_FILTER_CONTRACT_VERTEX, $value);
	}

	public function getContractVertexFlag(): bool{
		return $this->getFlag(COLUMN_FILTER_CONTRACT_VERTEX);
	}

	public function constrain(bool $value = true): AbstractDatum{
		$this->setConstraintFlag($value);
		return $this;
	}

	public function hasConstraints(): bool{
		if ($this->getConstraintFlag()) {
			return true;
		}
		return parent::hasConstraints();
	}

	public function getConstraints(): array{
		$f = __METHOD__;
		$print = false;
		$cn = $this->getName();
		$constraints = parent::hasConstraints() ? parent::getConstraints() : [];
		if ($this->getConstraintFlag()) {
			if ($print) {
				Debug::print("{$f} column \"{$cn}\" is constrained");
			}
			array_push($constraints, $this->generateConstraint());
		} elseif ($print) {
			Debug::print("{$f} column \"{$cn}\" is not constrained");
		}
		return $constraints;
	}

	public function hasUpdateBehavior(): bool{
		return isset($this->updateBehavior);
	}

	public function setVertexContractions(?array $vc): ?array{
		$f = __METHOD__;
		if ($vc == null) {
			unset($this->vertexContractions);
			if ($this->getContractVertexFlag()) {
				$this->setContractVertexFlag(false);
			}
			return null;
		}
		foreach ($vc as $class => $columnName) {
			if (! is_string($class)) {
				Debug::error("{$f} class name is not a string");
			} elseif (! is_string($columnName)) {
				Debug::error("{$f} column name is not a string");
			} elseif (! class_exists($class)) {
				Debug::error("{$f} class \"{$class}\" does not exist");
			} elseif (! $class::hasColumnStatic($columnName)) {
				Debug::error("{$f} class \"{$class}\" does not have a column \"{$columnName}\"");
			}
		}
		if (! $this->getContractVertexFlag()) {
			$this->setContractVertexFlag(true);
		}
		return $this->vertexContractions = $vc;
	}

	public function hasVertexContractions(): bool{
		return isset($this->vertexContractions) && is_array($this->vertexContractions);
	}

	public function getVertexContractions(): array{
		$f = __METHOD__;
		if (! $this->getContractVertexFlag()) {
			Debug::error("{$f} contractVertex flag is not set");
		} elseif (! $this->hasVertexContractions()) {
			return [
				$this->getDataStructureClass() => $this->getName()
			];
		}
		return $this->vertexContractions;
	}

	/**
	 * Contract a vertex on a graph where the relationship between nodes is expressed as a foreign key
	 * stored in this column.
	 * For example, in a graph of objects linked by parentKey, this function
	 * will set the parentKey value of all the child nodes to the parentKey value of the parent node.
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function contractVertex(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			if (! $this->hasVertexContractions()) {
				Debug::error("{$f} vertex contractions undefined");
			}
			$hdsc = $this->getDataStructureClass();
			$ds = $this->getDataStructure();
			$idn = $ds->getIdentifierName();
			$id_ts = $ds->getColumn($idn)->getTypeSpecifier();
			$identifier = $ds->getIdentifierValue();
			$ts = $this->getTypeSpecifier();
			$updates = [];
			foreach ($this->getVertexContractions() as $class => $columnName) {
				$update = null;
				$datum = $class::getTypeSpecifierStatic($columnName);
				if ($datum->applyFilter(ForeignKeyDatum::class, COLUMN_FILTER_INTERSECTION)) {
					if ($print) {
						Debug::print("{$f} datum \"{$columnName}\" for class \"{$class}\" is polymorphic");
					}
					$resolver = $datum->getForeignDataStructureClassResolver();
					$foreignClasses = $resolver::getAllPossibleIntersectionClasses();
					foreach ($foreignClasses as $foreignClass) {
						$intersection = new IntersectionData($hdsc, $foreignClass, $columnName) // , $this->getCriticalFlag()
						;
						$update = QueryBuilder::update($intersection->getDatabaseName(), $intersection->getTableName())->set("foreignKey")->where(new AndCommand(new WhereCondition("foreignKey", OPERATOR_EQUALS), new WhereCondition("relationship", OPERATOR_EQUALS)));
						$update->setParameters([
							$identifier,
							$columnName
						]);
						$update->setTypeSpecifier("{$id_ts}s");
						array_push($updates, $update);
					}
				} else {
					if ($print) {
						Debug::print("{$f} datum \"{$columnName}\" for class \"{$class}\" is non-polymorphic");
					}
					if(!method_exists($class, 'getTableNameStatic')){
						Debug::error("{$f} table name cannot be determined statically for class \"{$class}\"");
					}
					$update = QueryBuilder::update($class::getDatabaseNameStatic(), $class::getTableNameStatic())->set($columnName)->where(new WhereCondition($columnName, OPERATOR_EQUALS));
					$update->setParameters([
						$this->getValue(),
						$identifier
					]);
					$update->setTypeSpecifier("{$ts}{$id_ts}");
					array_push($updates, $update);
				}
			}
			foreach ($updates as $update) {
				$status = $update->executeGetStatus();
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} executeGetStatus on query statement \"{$update}\" returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * set this to determine what happens when a value changes for the subordinate data structure on update.
	 * Not to be confused with $onUpdate, which is part of the foreign key contraint clause.
	 * available options:
	 * FOREIGN_UPDATE_BEHAVIOR_NORMAL: update the subordinate data structure as normal
	 * FOREIGN_UPDATE_BEHAVIOR_DELETE: insert a new subordinate data structure, and delete the old one if it's not referenced by other data structures
	 *
	 * @param int $mode
	 * @return int
	 */
	public function setUpdateBehavior(?int $mode): ?int{
		if ($mode === null) {
			unset($this->updateBehavior);
			return null;
		}
		return $this->updateBehavior = $mode;
	}

	public function getUpdateBehavior(): int{
		if (! $this->hasUpdateBehavior()) {
			return FOREIGN_UPDATE_BEHAVIOR_NORMAL;
		}
		return $this->updateBehavior;
	}

	public function setRelativeSequennce(?string $seq): ?string{
		if ($seq === null) {
			unset($this->relativeSequence);
			return null;
		}
		return $this->relativeSequence = $seq;
	}

	public function hasRelativeSequence(): bool{
		return isset($this->relativeSequence) && is_string($this->relativeSequence) && ! empty($this->relativeSequence);
	}

	public function getRelativeSequence(): string{
		$f = __METHOD__;
		$cn = $this->getName();
		$print = false;
		if ($this->hasRelativeSequence()) {
			if ($print) {
				Debug::print("{$f} relative sequence was explicitly defined as {$this->relativeSequence}");
			}
			return $this->relativeSequence;
		}
		$type = $this->getRelationshipType();
		switch ($type) {
			case RELATIONSHIP_TYPE_ONE_TO_ONE:
				if ($print) {
					Debug::print("{$f} {$cn} is a one-to-one relationship; beforeSaveHook will take care of any mutually referential foreign keys");
				}
				return CONST_AFTER;
			case RELATIONSHIP_TYPE_ONE_TO_MANY:
				if ($print) {
					Debug::print("{$f} {$cn} is a one to many relationship and the many side must be inserted/updated after the one side");
				}
				return CONST_AFTER;
			case RELATIONSHIP_TYPE_MANY_TO_ONE:
				if ($print) {
					Debug::print("{$f} {$cn} is a many to one relationship. The one side must be inserted/updated before the many.");
				}
				return CONST_BEFORE;
			case RELATIONSHIP_TYPE_MANY_TO_MANY:
				if ($print) {
					Debug::print("{$f} {$cn} is a many to many relationship, the order does not matter");
				}
				return CONST_AFTER;
			default:
				Debug::error("{$f} invalid relationship type \"{$err}\"");
		}
	}

	/**
	 * set this to true to automatically load the data structure referenced by the foreign key in DataStructure->loadForeignDataStructures
	 *
	 * @param boolean $value
	 * @return boolean
	 */
	public function setAutoloadFlag(bool $value = true): bool{
		return $this->setFlag(COLUMN_FILTER_AUTOLOAD, $value);
	}

	public function getAutoloadFlag(): bool{
		return $this->getFlag(COLUMN_FILTER_AUTOLOAD);
	}

	public function autoload(bool $value = true): AbstractDatum{
		$this->setAutoloadFlag($value);
		return $this;
	}

	public function setAddToResponseFlag(bool $value = true): bool{
		return $this->setFlag(COLUMN_FILTER_ADD_TO_RESPONSE, $value);
	}

	public function setRelationshipType(int $type): int{
		$f = __METHOD__;
		switch ($type) {
			case RELATIONSHIP_TYPE_ONE_TO_ONE:
			case RELATIONSHIP_TYPE_ONE_TO_MANY:
			case RELATIONSHIP_TYPE_MANY_TO_ONE:
			case RELATIONSHIP_TYPE_MANY_TO_MANY:
				break;
			default:
				Debug::error("{$f} invalid relationship type \"{$type}\"");
		}
		return $this->relationshipType = $type;
	}

	public function hasRelationshipType(): bool{
		return isset($this->relationshipType) && is_int($this->relationshipType);
	}

	public function getRelationshipType(): int{
		$f = __METHOD__;
		if (! $this->hasRelationshipType()) {
			$name = $this->getName();
			Debug::error("{$f} relationship type is undefined for column \"{$name}\"");
		}
		return $this->relationshipType;
	}

	public function getConverseRelationshipType(): int{
		$f = __METHOD__;
		if (! $this->hasRelationshipType()) {
			Debug::error("{$f} relationship type is undefined");
		}
		$type = $this->getRelationshipType();
		switch ($type) {
			case RELATIONSHIP_TYPE_ONE_TO_ONE:
			case RELATIONSHIP_TYPE_MANY_TO_MANY:
				return $type;
			case RELATIONSHIP_TYPE_ONE_TO_MANY:
				return RELATIONSHIP_TYPE_MANY_TO_ONE;
			case RELATIONSHIP_TYPE_MANY_TO_ONE:
				return RELATIONSHIP_TYPE_ONE_TO_MANY;
			default:
				Debug::error("{$f} invalid relationship type \"{$type}\"");
		}
	}

	/**
	 * when this flag is set, if the data structure that hosts this datum has a foreign data structure indexed at this datum's key, then that foreign data structure will be added to the list returned with the response when calling XMLHttpResponse->pushDataStructure().
	 * for example, messages need their file attachment and quoted message data structures
	 *
	 * @return boolean
	 */
	public function getAddToResponseFlag(): bool{
		return $this->getFlag(COLUMN_FILTER_ADD_TO_RESPONSE);
	}

	public function setForeignDataStructureClassResolver(?string $fdscr): ?string{
		$f = __METHOD__;
		if ($fdscr === null) {
			unset($this->foreignDataStructureClassResolver);
			return null;
		} elseif (! isset($fdscr)) {
			Debug::error("{$f} foreign data structure class resolver is undefined");
		} elseif (! is_string($fdscr)) {
			Debug::error("{$f} foreign data structure class resolver is not a string");
		} elseif (! class_exists($fdscr)) {
			Debug::error("{$f} class \"{$fdscr}\" does not exist");
		} elseif (! is_a($fdscr, ClassResolver::class, true)) {
			Debug::error("{$f} class \"{$fdscr}\" is not a class resolver");
		}
		return $this->foreignDataStructureClassResolver = $fdscr;
	}

	public function hasForeignDataStructureClassResolver(): bool{
		return isset($this->foreignDataStructureClassResolver);
	}

	public function getForeignDataStructureClassResolver(): string{
		$f = __METHOD__;
		if (! $this->hasForeignDataStructureClassResolver()) {
			Debug::error("{$f} foreign data structure class resolver is undefined");
			// return UniversalDataClassResolver::class;
		}
		return $this->foreignDataStructureClassResolver;
	}

	public function setForeignDataTypeName(?string $name): ?string{
		if ($name === null) {
			unset($this->foreignDataTypeName);
			return null;
		}
		return $this->foreignDataTypeName = $name;
	}

	public function hasForeignDataTypeName(): bool{
		return isset($this->foreignDataTypeName);
	}

	public function getForeignDataTypeName(): string{
		$f = __METHOD__;
		if (! $this->hasForeignDataTypeName()) {
			$vn = $this->getName();
			if ($this instanceof Datum && $this->hasDataStructure()) {
				$dsc = $this->getDataStructureClass();
				Debug::error("{$f} foreign data type name is undefined for {$dsc} column \"{$vn}\"");
			}
			Debug::error("{$f} foreign data type name is undefined for column \"{$vn}\"");
		}
		return $this->foreignDataTypeName;
	}

	public function setForeignDataType(?string $type): ?string{
		$f = __METHOD__;
		if ($type == null) {
			unset($this->foreignDataType);
			return null;
		} elseif (! is_string($type)) {
			Debug::error("{$f} foreign data type must be a string");
		} elseif (class_exists($type)) {
			if (! is_a($type, DataStructure::class, true)) {
				Debug::error("{$f} class \"{$type}\" is not a DataStructure");
			}
			$actual_type = $type::getDataType();
			if ($actual_type !== $type) {
				Debug::error("{$f} class \"{$type}\" has data type \"{$actual_type}\"");
			}
		}
		return $this->foreignDataType = $type;
	}

	public function hasForeignDataType(): bool{
		return isset($this->foreignDataType);
	}

	public function hasForeignDataSubtype(): bool{
		$f = __METHOD__;
		$print = false;
		$subtype_name = $this->getForeignDataSubtypeName();
		if ($print) {
			$dsc = $this->getDataStructureClass();
			$columnName = $this->getName();
			Debug::print("{$f} data structure class is \"{$dsc}\", datum is \"{$columnName}\", subtype name is \"{$subtype_name}\"");
		}
		return $this->getDataStructure()->hasColumnValue($subtype_name);
	}

	public function getForeignDataType(): string{
		$f = __METHOD__;
		$print = false;
		if ($this->hasForeignDataType()) {
			if ($print) {
				Debug::print("{$f} foreign data type \"{$this->foreignDataType}\" was already defined");
			}
			return $this->foreignDataType;
		} elseif (! $this->hasForeignDataTypeName()) {
			$vn = $this->getName();
			if ($this instanceof Datum && $this->hasDataStructure()) {
				$dsc = $this->getDataStructureClass();
				Debug::error("{$f} foreign data type name is undefined for {$dsc} column \"{$vn}\"");
			}
			Debug::error("{$f} foreign data type name is undefined for column \"{$vn}\"");
		}
		$n = $this->getForeignDataTypeName();
		$t = $this->getDataStructure()->getColumnValue($n);
		if ($print) {
			Debug::print("{$f} foreign data type \"{$n}\" is \"{$t}\"");
		}
		return $t;
	}

	public function setForeignDataSubtypeName(?string $name): ?string{
		if ($name === null) {
			unset($this->foreignDataSubtypeName);
			return null;
		}
		return $this->foreignDataSubtypeName = $name;
	}

	public function hasForeignDataSubtypeName(): bool{
		return isset($this->foreignDataSubtypeName);
	}

	public function getForeignDataSubtypeName(): string
	{
		$f = __METHOD__;
		if (! $this->hasForeignDataSubtypeName()) {
			if ($this instanceof Datum) {
				$name = $this->getName();
				Debug::error("{$f} foreign data subtyppe name is undefined for column \"{$name}\"");
			}
			Debug::error("{$f} foreign data subtype name is undefined");
		}
		return $this->foreignDataSubtypeName;
	}

	public function getForeignDataSubtype(): ?string{
		$f = __METHOD__;
		$print = false;
		$subtype_name = $this->getForeignDataSubtypeName();
		if ($print) {
			$dsc = $this->getDataStructureClass();
			$columnName = $this->getName();
			Debug::print("{$f} data structure class is \"{$dsc}\", datum is \"{$columnName}\", subtype name is \"{$subtype_name}\"");
		}
		$value = $this->getDataStructure()->getColumnValue($subtype_name);
		if ($print) {
			Debug::print("{$f} returning \"{$value}\"");
			if ($value === CONST_ERROR) {
				Debug::error("{$f} column value is \"error\"");
			}
		}
		return $value;
	}

	public function getForeignDataStructureClass(): string{
		$f = __METHOD__;
		$print = false;
		if (! $this->hasForeignDataStructureClass()) {
			$column_name = $this->getName();
			if ($print) {
				Debug::print("{$f} foreign data structure class is undefined for column \"{$column_name}\"");
			}
			if ($this->hasForeignDataStructureClassResolver()) {
				$resolver = $this->getForeignDataStructureClassResolver();
				if ($print) {
					Debug::print("{$f} about to resolve foreign data structure class of column \"{$column_name}\" with resolver class \"{$resolver}\"");
				}
				return $resolver::resolveClass($this);
			}
			$dsc = $this->getDataStructure()->getClass();
			Debug::error("{$f} foreign data structure class is undefined for datum with index \"{$column_name}\" in structure of class \"{$dsc}\"");
		} elseif (! is_string($this->foreignDataStructureClass)) {
			Debug::error("{$f} foreign data structure class is not a string");
		} elseif (! class_exists($this->foreignDataStructureClass)) {
			Debug::error("{$f} foreign data structure class \"{$this->foreignDataStructureClass}\" does not exist");
		} elseif ($print) {
			Debug::print("{$f} returning \"{$this->foreignDataStructureClass}\"");
		}
		return $this->foreignDataStructureClass;
	}

	public function setForeignDataStructureClass(?string $class): ?string{
		$f = __METHOD__;
		if (! is_string($class)) {
			Debug::error("{$f} class is not a string");
		} elseif (! class_exists($class)) {
			Debug::error("{$f} class \"{$class}\" does not exist");
		} elseif (is_a($class, ClassResolver::class, true)) {
			Debug::error("{$f} don't pass class resolvers to this function");
		}
		return $this->foreignDataStructureClass = $class;
	}

	public function hasForeignDataStructureClass(): bool{
		return ! empty($this->foreignDataStructureClass);
	}

	/**
	 * set this flag to always eagerly load the foreign data structure(s) at this index
	 *
	 * @param boolean $value
	 * @return boolean
	 */
	public function setEagerLoadFlag(bool $value = true): bool{
		return $this->setFlag(COLUMN_FILTER_EAGER, $value);
	}

	public function getEagerLoadFlag(): bool{
		return $this->getFlag(COLUMN_FILTER_EAGER);
	}

	public function getAllPossibleIntersectionData(): array{
		$f = __METHOD__;
		$print = false;
		if ($this->hasForeignDataStructureClass()) {
			if($print){
				Debug::print("{$f} foreign data structure class is defined");
			}
			$intersections = [
				new IntersectionData(
					$this->getDataStructureClass(), 
					$this->getForeignDataStructureClass(), 
					$this->getName()
				)
			];
		} elseif ($this->hasForeignDataStructureClassResolver()) {
			if($print){
				Debug::print("{$f} foreign data structure class resolver is defined");
			}
			$resolver = $this->getForeignDataStructureClassResolver();
			$intersections = $resolver::getAllPossibleIntersectionData($this);
		} else {
			if($print){
				Debug::print("{$f} foreign data structure class and resolver are both undefined");
			}
			$cn = $this->getName();
			$dsc = $this->getDataStructureClass();
			$ds = $this->getDataStructure();
			$key = $ds->hasIdentifierValue() ? $ds->getIdentifierValue() : "undefined";
			Debug::error("{$f} column \"{$cn}\" of data structure class \"{$dsc}\" and key \"{$key}\" lacks either a foreign data structure class or resolver");
		}
		if ($this->hasDataStructure()) {
			if($print){
				Debug::print("{$f} data structure is defined");
			}
			$ds = $this->getDataStructure();
			foreach ($intersections as $intersection) {
				$intersection->setHostDataStructure($ds);
			}
		}elseif($print){
			Debug::print("{$f} data strcuture is undefined");
		}
		return $intersections;
	}

	public function monomorph(string $foreignClass): AbstractDatum{
		$this->setForeignDataStructureClass($foreignClass);
		$this->setForeignDataStructureClassResolver(null);
		$this->setPersistenceMode(PERSISTENCE_MODE_DATABASE);
		return $this;
	}

	public function createIntersectionTables(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			$name = $this->getName();
			if (! $this->hasDataStructure()) {
				Debug::error("{$f} foreign key datum \"{$name}\" doesn't have a data structure");
			}
			$ds = $this->getDataStructure();
			$intersections = $this->getAllPossibleIntersectionData();
			foreach ($intersections as $intersection) {
				$count = $intersection->getColumnCount();
				if ($count < 3) {
					$table = $intersection->getTableName();
					Debug::warning("{$f} intersection data in table \"{$table}\" has a column count of {$count}");
					Debug::printArray($intersection->getColumns());
					Debug::printStackTrace();
				}
				$fdsc = $intersection->getForeignDataStructureClass();
				if ($ds instanceof EventSourceData) {
					$intersection->setHostDataStructure($ds);
				}
				$db = $intersection->getDatabaseName();
				$tableName = $intersection->getTableName();
				if (! QueryBuilder::tableExists($mysqli, $db, $tableName)) {
					if ($print) {
						Debug::print("{$f} intersection table \"{$db}.{$tableName}\" does not exist");
					}
					$status = $intersection->createTable($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} creating intersection table \"{$db}.{$tableName}\" returned error status \"{$err}\"");
						return $status;
					} elseif ($print) {
						Debug::print("{$f} successfully created new intersection table \"{$db}.{$tableName}\"");
					}
				} elseif ($print) {
					Debug::print("{$f} intersection table \"{$db}.{$tableName}\" already exists");
				}
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setRecursiveDeleteFlag(bool $value = true): bool{
		return $this->setFlag(COLUMN_FILTER_RECURSIVE_DELETE, $value);
	}

	public function getRecursiveDeleteFlag(): bool{
		return $this->getFlag(COLUMN_FILTER_RECURSIVE_DELETE);
	}

	public function recursiveDelete(bool $value = true): AbstractDatum{
		$this->setRecursiveDeleteFlag($value);
		return $this;
	}

	public function hasConverseRelationshipKeyName(){
		return isset($this->ConverseRelationshipKeyName);
	}

	public function getConverseRelationshipKeyName(){
		$f = __METHOD__;
		if (! $this->hasConverseRelationshipKeyName()) {
			Debug::error("{$f} converse relationship key is undefined");
		}
		return $this->ConverseRelationshipKeyName;
	}

	public function setConverseRelationshipKeyName($irk){
		return $this->ConverseRelationshipKeyName = $irk;
	}
}
