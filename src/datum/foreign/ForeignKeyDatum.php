<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum\foreign;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\common\HitPointsInterface;
use JulianSeymour\PHPWebApplicationFramework\core\ClassResolver;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionData;
use JulianSeymour\PHPWebApplicationFramework\datum\Sha1HashDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterInsertForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\DeallocateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseIntersectionDataEvent;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\ConstrainableTrait;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\ForeignKeyConstraint;
use JulianSeymour\PHPWebApplicationFramework\query\index\KeyPart;
use JulianSeymour\PHPWebApplicationFramework\query\table\FullTableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use mysqli;

class ForeignKeyDatum extends Sha1HashDatum implements ForeignKeyDatumInterface{

	use ConstrainableTrait;
	use ForeignKeyDatumTrait;
	use FullTableNameTrait;
	
	protected $intersectionData;
	
	public function __construct(string $name, ?int $relationship_type = null){
		parent::__construct($name);
		if($relationship_type !== null){
			$this->setRelationshipType($relationship_type);
		}
	}

	public function getConstructorParams(): ?array{
		if($this->hasRelationshipType()){
			return [
				$this->getName(),
				$this->getRelationshipType()
			];
		}
		return [$this->getName()];
	}
	
	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			COLUMN_FILTER_ADD_TO_RESPONSE, // true => automatically convert foreign data structure referenced by this column to array as part of XMLHttpResponse when the host data structure is also part of that response
			COLUMN_FILTER_AUTOLOAD, // true => automatically load this column's foreign structure when the host is loaded
			"childKey",
			COLUMN_FILTER_CONSTRAIN, // true => apply a foreign key constraint to this column on table creation
			"constraintGenerated",
			COLUMN_FILTER_CONTRACT_VERTEX, // true => call contractVertex() in DataStructure->beforeDeleteHook
			COLUMN_FILTER_RECURSIVE_DELETE,
			COLUMN_FILTER_EAGER, // true => eager load this column's foreign data structure even when lazy loading is enabled in loadForeignDataStructures
			"parentKey",
			COLUMN_FILTER_PREVENT_CIRCULAR_REF, // true => preventCircularReference gets called during validate()
			COLUMN_FILTER_TEMPLATE
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			COLUMN_FILTER_ADD_TO_RESPONSE,
			COLUMN_FILTER_AUTOLOAD,
			"childKey",
			COLUMN_FILTER_CONSTRAIN,
			"constraintGenerated",
			COLUMN_FILTER_CONTRACT_VERTEX,
			COLUMN_FILTER_RECURSIVE_DELETE,
			COLUMN_FILTER_EAGER,
			"parentKey",
			COLUMN_FILTER_PREVENT_CIRCULAR_REF,
			COLUMN_FILTER_TEMPLATE
		]);
	}
	
	public function setConstraintFlag(bool $value = true): bool{
		if($value && $this->hasForeignDataStructureClassResolver() && ! $this->hasPersistenceMode()){
			$this->setPersistenceMode(PERSISTENCE_MODE_INTERSECTION);
			$this->retainOriginalValue();
		}
		return $this->setFlag(COLUMN_FILTER_CONSTRAIN, $value);
	}

	public function getConstraintFlag(): bool{
		return $this->getFlag(COLUMN_FILTER_CONSTRAIN);
	}

	public function setForeignDataStructureClassResolver(?string $fdscr): ?string{
		$f = __METHOD__;
		if($this->hasForeignDataStructureClassResolver()){
			$this->release($this->foreignDataStructureClassResolver);
		}
		if(!isset($fdscr)){
			Debug::error("{$f} foreign data structure class resolver is undefined");
		}elseif(!is_string($fdscr)){
			Debug::error("{$f} foreign data structure class resolver is not a string");
		}elseif(!class_exists($fdscr)){
			Debug::error("{$f} class \"{$fdscr}\" does not exist");
		}elseif(!is_a($fdscr, ClassResolver::class, true)){
			Debug::error("{$f} class \"{$fdscr}\" is not a class resolver");
		}elseif($this->getConstraintFlag() && ! $this->hasPersistenceMode()){
			$this->setPersistenceMode(PERSISTENCE_MODE_INTERSECTION);
			$this->retainOriginalValue();
		}
		return $this->foreignDataStructureClassResolver = $this->claim($fdscr);
	}

	public function generateIndexDefinition(){
		$f = __METHOD__;
		$print = false;
		$column_name = $this->getName();
		if($this->getConstraintFlag()){
			if($print){
				Debug::print("{$f} column \"{$column_name}\" is constrained");
			}
			return $this->generateConstraint();
		}elseif($print){
			Debug::print("{$f} column \"{$column_name}\" is not constrained");
		}
		return parent::generateIndexDefinition();
	}

	public function processInput($input){
		$f = __METHOD__;
		$v = $input->getValueAttribute();
		$ret = parent::processInput($input);
		$ds = $this->getDataStructure();
		$index = $this->getName();
		if($v == null){
			$ds->ejectForeignDataStructure($index);
		}elseif($v !== $this->getValue()){
			if(registry()->hasObjectRegisteredToKey($v)){
				$ds->setForeignDataStructure($index, registry()->getRegisteredObjectFromKey($v));
			}else{
				Debug::warning("{$f} application instance does not have an object registered to key \"{$v}\"");
			}
		}
		return $ret;
	}

	public function hasIntersectionData():bool{
		return isset($this->intersectionData);
	}
	
	public function getIntersectionData(): IntersectionData{
		$f = __METHOD__;
		try{
			if($this->hasIntersectionData()){
				return $this->intersectionData;
			}
			return $this->setIntersectionData($this->generateIntersectionData());
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function setIntersectionData(?IntersectionData $intersection):?IntersectionData{
		$f = __METHOD__;
		try{
			if($this->hasIntersectionData()){
				$this->releaseIntersectionData();
			}
			if($intersection instanceof HitPointsInterface){
				$random = sha1(random_bytes(32));
				$that = $this;
				$closure1 = function(DeallocateEvent $event, HitPointsInterface $target) 
				use ($that, $random){
					$target->removeEventListener($event);
					if($that->hasEventListener(EVENT_RELEASE_INTERSECTION, $random)){
						$that->removeEventListener(EVENT_RELEASE_INTERSECTION, $random);
					}
					$that->releaseIntersectionData();
				};
				$intersection->addEventListener(EVENT_DEALLOCATE, $closure1, $random);
				$closure2 = function(ReleaseIntersectionDataEvent $event, ForeignKeyDatum $target) 
				use ($intersection, $random){
					$target->removeEventListener($event);
					if($intersection->hasEventListener(EVENT_DEALLOCATE, $random)){
						$intersection->removeEventListener(EVENT_DEALLOCATE, $random);
					}
				};
				$this->addEventListener(EVENT_RELEASE_INTERSECTION, $closure2, $random);
				$this->claim($intersection);
			}
			return $this->intersectionData = $intersection;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	protected function generateIntersectionData(){
		$f = __METHOD__;
		try{
			$print = false;
			$fdsc = $this->getForeignDataStructureClass();
			$hdsc = $this->getDataStructureClass();
			$intersection = new IntersectionData();
			$intersection->setHostDataStructureClass($hdsc);
			$intersection->setForeignDataStructureClass($fdsc);
			$intersection->setRelationship($this->getName());
			$ds = $this->getDataStructure();
			if($ds->hasIdentifierValue()){
				$hk = $this->getDataStructureKey();
				$intersection->setHostKey($hk);
				$intersection->setForeignDataStructure("hostKey", $this->getDataStructure());
				if($print){
					Debug::print("{$f} assigned value \"{$hk}\" to host key");
				}
			}elseif($print){
				Debug::print("{$f} host data structure lacks an identifier");
			}
			if($this->hasValue()){
				$value = $this->getValue();
				if(empty($value)){
					Debug::error("{$f} value is empty");
				}elseif($print){
					$did = $ds->getDebugId();
					Debug::printStackTraceNoExit("{$f} assigning value \"{$value}\" to foreignKey. Data structure debug ID is \"{$did}\"");
				}
				$intersection->setForeignKey($value);
			}elseif($print){
				$did = $ds->getDebugId();
				Debug::printStackTraceNoExit("{$f} this column lacks an actual value. Data structure debug ID is \"{$did}\"");
			}
			if($print){
				Debug::print("{$f} returning");
			}
			return $intersection;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function configureArrayMembership($value){
		$f = __METHOD__;
		$print = false;
		$column_name = $this->getName();
		if(is_bool($value)){
			if($print){
				Debug::print("{$f} received a boolean value for column \"{$column_name}\"");
			}
			return parent::configureArrayMembership($value);
		}elseif(!is_array($value) && ! is_string($value)){
			Debug::error("{$f} this function accepts bool, string and array");
		}
		parent::configureArrayMembership(true);
		$this->setAddToResponseFlag(true);
		return $this->getDataStructure()->getForeignDataStructure($this->getName())->configureArrayMembership($value);
	}

	private function getOriginalForeignDataType(){
		if($this->hasForeignDataType() && ! $this->hasForeignDataTypeName()){
			return $this->getForeignDataType();
		}
		return $this->getDataStructure()->getOriginalColumnValue($this->getForeignDataTypeName());
	}

	private function getOriginalForeignDataSubtype(){
		return $this->getDataStructure()->getOriginalColumnValue($this->getForeignDataSubtypeName());
	}

	private function hasIntersectionTableChanged(){
		$f = __METHOD__;
		try{
			$print = false;
			if($this->hasForeignDataType()){
				$old_type = $this->getForeignDataType();
				$new_type = $this->getForeignDataType();
			}elseif($this->hasForeignDataTypeName()){
				$new_type = $this->getForeignDataType();
				$old_type = $this->getOriginalForeignDataType();
			}else{
				$new_type = null;
				$old_type = null;
			}
			if($this->hasForeignDataSubtypeName()){
				$old_subtype = $this->getOriginalForeignDataSubtype();
				$new_subtype = $this->getForeignDataSubtype();
			}else{
				$old_subtype = null;
				$new_subtype = null;
			}
			$resolver = $this->getForeignDataStructureClassResolver();
			if($print){
				Debug::print("{$f} resolver class is \"{$resolver}\"; old type hint is \"{$old_type}\"; new type hint is \"{$new_type}\"; old subtype hint is \"{$old_subtype}\"; new subtype hint is \"{$new_subtype}\"");
			}
			$old_class = $resolver::resolveForeignDataStructureClass($old_type, $old_subtype);
			if(!method_exists($old_class, 'getTableNameStatic')){
				Debug::error("{$f} table name cannot be determined statically for old class \"{$old_class}\"");
			}
			$new_class = $resolver::resolveForeignDataStructureClass($new_type, $new_subtype);
			if(!method_exists($new_class, 'getTableNameStatic')){
				Debug::error("{$f} table name cannot be determined statically for new class \"{$new_class}\"");
			}
			if($print){
				Debug::print("{$f} old type \"{$old_type}\", old subtype \"{$old_subtype}\", new type \"{$new_type}\", new subtype \"{$new_subtype}\"");
			}
			if($old_class::getDatabaseNameStatic() !== $new_class::getDatabaseNameStatic()){
				if($print){
					Debug::print("{$f} database names have changed");
				}
				return true;
			}elseif($old_class::getTableNameStatic() !== $new_class::getTableNameStatic()){
				if($print){
					Debug::print("{$f} table names have changed");
				}
				return true;
			}elseif($print){
				Debug::print("{$f} database and table names have not changed");
			}
			return false;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	protected function preventCircularReference($value):bool{
		$f = __METHOD__;
		try{
			$print = false;
			if($value == null){
				if($print){
					Debug::print("{$f} value is null");
				}
				return true;
			}
			$ds = $this->getDataStructure();
			if($ds->getIdentifierValue() === $value){
				if($print){
					Debug::print("{$f} host column's data structure has identifier \"{$value}\"");
				}
				return false;
			}
			$column_name = $this->getName();
			$fds = $ds->getForeignDataStructure($column_name);
			return $fds->getAssociationDistance($column_name, $value) < 0;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setPreventCircularReferenceFlag(bool $value = true):bool{
		return $this->setFlag(COLUMN_FILTER_PREVENT_CIRCULAR_REF, $value);
	}

	public function getPreventCircularReferenceFlag():bool{
		return $this->getFlag(COLUMN_FILTER_PREVENT_CIRCULAR_REF);
	}

	public function hasPotentialValue(){
		$f = __METHOD__;
		try{
			$name = $this->getName();
			$print = false;
			if($this->getPersistenceMode() !== PERSISTENCE_MODE_INTERSECTION){
				if($print){
					Debug::print("{$f} column \"{$name}\" is non-polymorphic, so the answer is no");
				}
				return false;
			}elseif($print){
				Debug::print("{$f} column \"{$name}\" is polymorphic");
			}
			$ds = $this->getDataStructure();
			if($this->hasForeignDataTypeName()){
				$type_hint = $this->getForeignDataTypeName();
				$datatype = $ds->getColumn($type_hint);
				if($datatype->getPersistenceMode() === PERSISTENCE_MODE_DATABASE && $datatype->hasValue()){
					if($print){
						Debug::print("{$f} data structure has a type hint, so column \"{$name}\" has a value");
					}
					return true;
				}elseif($print){
					Debug::print("{$f} data structure lacks a type hint for column \"{$name}\"");
				}
			}elseif($print){
				Debug::print("{$f} foreign data type name is undefined for column \"{$name}\"");
			}
			if($this->hasForeignDataSubtypeName()){
				$subtype_hint = $this->getForeignDataSubtypeName();
				if($ds->hasColumnValue($subtype_hint)){
					if($print){
						$hint = $ds->getColumnValue($subtype_hint);
						if($hint === CONST_ERROR){
							$dsc = $ds->getClass();
							Debug::error("{$f} Hint is \"error\". Data structure class is \"{$dsc}\", column \"{$name}\", subtype hint name is \"{$subtype_hint}\"");
						}
						Debug::print("{$f} data structure has a subtype hint \"{$hint}\", so column \"{$name}\" has a value");
					}
					return true;
				}elseif($print){
					Debug::print("{$f} data structure lacks a subtype hint, so column \"{$name}\" is valueless");
				}
			}elseif($print){
				Debug::print("{$f} foreign data subtype name is undefined");
			}
			return false;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	private function deleteOriginalIntersectionData($mysqli){
		$f = __METHOD__;
		$rel = $this->getName();
		$intersection = new IntersectionData($this->getDataStructureClass(), $this->getForeignDataStructureClassResolver()::resolveForeignDataStructureClass($this->getOriginalForeignDataType(), $this->getOriginalForeignDataSubtype()), $rel);
		$del = QueryBuilder::delete()->from(
			$intersection->getDatabaseName(), $intersection->getTableName()
		)->where(
			new AndCommand(
				new WhereCondition("hostKey", OPERATOR_EQUALS), 
				new WhereCondition("relationship", OPERATOR_EQUALS)
			)
		)->prepareBindExecuteGetStatus(
			$mysqli, 'ss', $this->getDataStructure()->getIdentifierValue(), $rel
		);
		if($del !== SUCCESS){
			$err = ErrorMessage::getResultMessage($del);
			Debug::error("{$f} deleting intersection data returned error status \"{$err}\"");
			return $this->setObjectStatus($del);
		}
		return SUCCESS;
	}

	public function validate($v):int{
		$f = __METHOD__;
		$print = false;
		if($this->getPreventCircularReferenceFlag()){
			if(!$this->preventCircularReference($v)){
				Debug::error("{$f} preventCircularReference returned false");
				return $this->setObjectStatus(ERROR_CYCLE_DETECTED);
			}elseif($print){
				Debug::print("{$f} circular reference not detected");
			}
		}elseif($print){
			Debug::print("{$f} preventCircularReference flag is not set");
		}
		return parent::validate($v);
	}

	public function setRelationshipType(int $type): int{
		$f = __METHOD__;
		switch($type){
			case RELATIONSHIP_TYPE_ONE_TO_ONE:
			case RELATIONSHIP_TYPE_MANY_TO_ONE:
				break;
			case RELATIONSHIP_TYPE_ONE_TO_MANY:
			case RELATIONSHIP_TYPE_MANY_TO_MANY:
				Debug::error("{$f} X to many relationships only work with KeyListDatum");
			default:
				Debug::error("{$f} invalid relationship type \"{$type}\"");
		}
		if($this->hasRelationshipType()){
			$this->release($this->relationshipType);
		}
		return $this->relationshipType = $this->claim($type);
	}

	public function setPersistenceMode(?int $pm):?int{
		if(
			$pm === PERSISTENCE_MODE_INTERSECTION && 
			$this->hasForeignDataStructureClassResolver() && 
			$this->getConstraintFlag()
		){
			$this->retainOriginalValue();
		}
		return parent::setPersistenceMode($pm);
	}

	public function updateIntersectionTables(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$column_name = $this->getName();
			$print = false;
			if($print){
				$dsc = $this->getDataStructureClass();
				Debug::print("{$f} host class is \"{$dsc}\"; relationship is \"{$column_name}\"");
			}
			// 0 0 X //1. original value not retained, does not have value, table irrelevant -> nothing
			// 0 1 X //2. original value not retained, has value, table irrelevant -> insert
			// 1 0 X //3. original value retained, does not have value, table irrelevant -> delete
			// 1 1 0 //4. original value retained, has value, table has not changed -> update
			// 1 1 1 //5. original value retained, has value, table has changed -> delete and insert
			if($this->hasOriginalValue()){
				if($print){
					Debug::print("{$f} column \"{$column_name}\" has retained its original value");
				}
				if($this->hasValue()){
					if($print){
						Debug::print("{$f} column \"{$column_name}\" has retained its original value, and currently has a value");
					}
					$intersection = $this->generateIntersectionData();
					if($this->hasIntersectionTableChanged()){
						// 5. original value retained, has value, table has changed -> delete and insert
						if($print){
							$table = $intersection->getTableName();
							Debug::print("{$f} original value of column \"{$column_name}\" retained, value is defined, table has changed -- inserting new intersection data into \"{$table}\" and deleting the old one");
						}
						$status = $intersection->insert($mysqli);
						deallocate($intersection);
						if($status !== SUCCESS){
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} inserting intersection table returned error status \"{$err}\"");
						}
						$status = $this->deleteOriginalIntersectionData($mysqli);
					}elseif($this->getValue() !== $this->getOriginalValue()){
						// 4. original value retained, has value, table has not changed -> update
						if($print){
							Debug::print("{$f} original value of column \"{$column_name}\" was retained, current value is defined, table has not changed -- updating intersection table");
						}
						$where = new AndCommand(
							new WhereCondition("hostKey", OPERATOR_EQUALS), 
							new WhereCondition("relationship", OPERATOR_EQUALS)
						);
						$ds = $this->getDataStructure();
						$query = QueryBuilder::update(
							$intersection->getDatabaseName(), 
							$intersection->getTableName()
						)->set("foreignKey")->where($where);
						if($print){
							Debug::print("{$f} about to execute query statement \"{$query}\"");
						}
						$status = $query->prepareBindExecuteGetStatus($mysqli, 'sss', $this->getValue(), $ds->getIdentifierValue(), $intersection->getRelationship());
						deallocate($intersection);
					}else{
						if($print){
							Debug::print("{$f} original value retained, current value is defined, table has not changes, and neither has the value -- do nothing");
						}
						return SUCCESS;
					}
				}else{
					// 3. original value retained, does not have value, table irrelevant -> delete
					if($print){
						Debug::print("{$f} original value retained, no current value -- deleting original intersection data");
					}
					$status = $this->deleteOriginalIntersectionData($mysqli);
				}
			}elseif($this->hasValue()){
				// 2. original value not retained, has value, table irrelevant -> insert
				if($print){
					Debug::print("{$f} original value was not retained, current value is defined for column \"{$column_name}\" -- inserting new intersection data");
					$fdt = $this->getForeignDataStructureClass()::getDataType();
					Debug::print("{$f} foreign data type {$fdt}");
				}
				$intersection = $this->generateIntersectionData();
				if(!$intersection->hasForeignKey()){
					Debug::error("{$f} intersection data for column \"{$column_name}\" lacks a foreign key");
				}elseif(!$intersection->hasHostKey()){
					Debug::error("{$f} intersection data lacks a host key");
				}elseif(!$intersection->hasRelationship()){
					Debug::error("{$f} intersection data lacks a relationship");
				}
				$status = $intersection->insert($mysqli);
				deallocate($intersection);
			}else{
				// 1. original value not retained, does not have value, table irrelevant -> nothing'
				if($print){
					Debug::print("{$f} polymorphic key \"{$column_name}\" does not have original or current values");
				}
				return SUCCESS;
			}
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} inserting or updating intsersection data returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully updated intersection table");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	
	public function hasConstraints(): bool{
		return $this->getConstraintFlag() || $this->hasArrayProperty("constraints");
	}
	
	public function getConstraints(){
		$f = __METHOD__;
		$print = false;
		$column_name = $this->getName();
		if($this->getConstraintFlag() && !$this->getConstraintGeneratedFlag()){
			if($print){
				Debug::print("{$f} column \"{$column_name}\" is constrained");
			}
			$this->pushConstraint($this->generateConstraint());
			$this->setConstraintGeneratedFlag(true);
		}elseif($print){
			Debug::print("{$f} column \"{$column_name}\" is not constrained");
		}
		if($this->hasArrayProperty("constraints")){
			return $this->getProperty("constraints");
		}
		return null;
	}
	
	public function getConstraintGeneratedFlag():bool{
		return $this->getFlag("constraintGenerated");
	}
	
	public function setConstraintGeneratedFlag(bool $value=true):bool{
		return $this->setFlag("constraintGenerated", $value);
	}
	
	public function generateConstraint():ForeignKeyConstraint{
		$f = __METHOD__;
		try{
			$print = false;
			$fdsc = $this->getForeignDataStructureClass();
			$idn = $fdsc::getIdentifierNameStatic();
			$hostType = $this->getDataStructureClass()::getDataType();
			$foreignType = $fdsc::getDataType();
			$column_name = $this->getName();
			$index_name = "{$foreignType}_{$idn}_index";
			if($hostType !== $foreignType){
				$index_name = "{$hostType}_{$index_name}";
			}
			if($this->hasDataStructure()){
				$ds = $this->getDataStructure();
				if($ds->hasForeignDataStructure($column_name)){
					$fds = $ds->getForeignDataStructure($column_name);
					$db = $fds->getDatabaseName();
					$table_name = $fds->getTableName();
				}else{
					if(!method_exists($fdsc, 'getTableNameStatic')){
						Debug::error("{$f} table name cannot be determined statically for foreign data structure class \"{$fdsc}\"");
					}elseif($print){
						Debug::print("{$f} data structure does not have a foreign relationship \"{$column_name}\"");
					}
					$db = $fdsc::getDatabaseNameStatic();
					$table_name = $fdsc::getTableNameStatic();
				}
			}elseif(!method_exists($fdsc, 'getTableNameStatic')){
				Debug::error("{$f} this column does not have a data structure, and table name cannot be determined statically for foreign data structure class \"{$fdsc}\"");
			}else{
				if($print){
					Debug::print("{$f} this column does not have a data structure");
				}
				$db = $fdsc::getDatabaseNameStatic();
				$table_name = $fdsc::getTableNameStatic();
			}
			if($this->hasKeyParts()){
				$keyparts = $this->getKeyParts();
			}else{
				$keyparts = [new KeyPart($idn, 40)];
			}
			$constraint = new ForeignKeyConstraint(null, $index_name, [$column_name], $db, $table_name, $keyparts);
			if($print){
				Debug::print("{$f} instantitated ".$constraint->getDebugString());
			}
			if($this->hasOnDelete()){
				$constraint->setOnDelete($this->getOnDelete());
			}
			if($this->hasOnUpdate()){
				$constraint->setOnUpdate($this->getOnUpdate());
			}
			return $constraint;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function insertIntersectionData(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false;
			$name = $this->getName();
			$intersection = $this->generateIntersectionData();
			// error checking
			$count = $intersection->getFilteredColumnCount(COLUMN_FILTER_DATABASE);
			if(!$intersection->hasForeignKey()){
				Debug::error("{$f} generated intersection data without a foreign key for column \"{$name}\"");
			}elseif($count !== 3){
				Debug::error("{$f} intersection data has a column count of {$count}");
			}elseif(!$intersection->getColumn("foreignKey")->hasValue()){
				Debug::error("{$f} intersection table foreign key lacks an actual value");
			}elseif($print){
				$foreign_key = $intersection->getForeignKey();
				Debug::print("{$f} intersection table foreign key is defined as \"{$foreign_key}\"");
			}
			if($print){
				$hdsc = $intersection->getHostDataStructureClass();
				$host_key = $intersection->getHostKey();
				$host_test = new $hdsc();
				$status = $host_test->load($mysqli, $host_test->getIdentifierName(), $host_key);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} loading {$hdsc} with key \"{$host_key}\" returned error status \"{$err}\"");
				}
				$fdsc = $intersection->getForeignDataStructureClass();
				$foreign_key = $intersection->getForeignKey();
				$foreign_test = new $fdsc();
				$status = $foreign_test->load($mysqli, $foreign_test->getIdentifierName(), $foreign_key);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} loading {$fdsc} with key \"{$foreign_key}\" returned error status \"{$err}\"");
				}
				Debug::print("{$f} both sides of the intersection table already exist");
			}
			$status = $intersection->insert($mysqli);
			deallocate($intersection);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} inserting IntersectionData returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully inserted IntersectionData");
			}
			return $status;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function fulfillMutualReference():int{
		$f = __METHOD__;
		try{
			$column_name = $this->getName();
			$print = false && $this->getDebugFlag();
			$foreign_key = $this->getValue();
			$ds = $this->getDataStructure();
			if($print){
				Debug::print("{$f} entered for column \"{$column_name}\" of a ".$ds->getShortClass());
			}
			if(!registry()->has($foreign_key)){
				if(!$ds->hasForeignDataStructure($column_name)){
					Debug::error("{$f} unable to find this object's {$column_name} relationship");
				}elseif($print){
					Debug::print("{$f} registry does not know of an object with key \"{$foreign_key}\" for mutually referential 1 to 1 foreign relationship \"{$column_name}\". Getting it from the host object itself.");
				}
				$fds = $ds->getForeignDataStructure($column_name);
			}else{
				$fds = registry()->get($foreign_key);
			}
			$column = $this;
			if($fds->getInsertFlag() && !$fds->getDeleteFlag()){
				if($print){
					Debug::print("{$f} foreign data structure \"{$column_name}\" is flagged for insertion");
				}
				$closure = function (AfterInsertForeignDataStructuresEvent $event, DataStructure $target) 
				use ($column, $foreign_key, $fds){
					$f = __METHOD__;
					$print = false;
					$when = $event->getProperty("when");
					if($when !== CONST_AFTER){
						if($print){
							Debug::print("{$f} fulfilling mutual reference is fired only after the object is inserted");
						}
						return SUCCESS;
					}
					$target->removeEventListener($event);
					$fds->setInsertFlag(false);
					$target->setPostInsertForeignDataStructuresFlag(false);
					$column->setValue($foreign_key);
					$column->setUpdateFlag(true);
					$status = $target->update(db()->getConnection(PublicWriteCredentials::class));
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} updating mutually referential one to one foreign key after insert returned error status \"{$err}\"");
						return $target->setObjectStatus($status);
					}elseif($print){
						Debug::print("{$f} successfully updated mututally referential foreign key after insert");
					}
					return $status;
				};
				$ds->addEventListener(EVENT_AFTER_INSERT_FOREIGN, $closure);
				$this->ejectValue();
			}elseif($print){
				Debug::print("{$f} foreign data structure \"{$column_name}\" is not flagged for insertion");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function copy($that):int{
		$f = __METHOD__;
		if($that->hasForeignDataIdentifierName()){
			$this->setForeignDataIdentifierName($that->getForeignDataIdentifierName());
		}
		if($that->hasForeignDataStructureClass()){
			$this->setForeignDataStructureClass($that->getForeignDataStructureClass());
		}
		if($that->hasForeignDataStructureClassResolver()){
			$this->setForeignDataStructureClassResolver($that->getForeignDataStructureClassResolver());
		}
		if($that->hasForeignDataType()){
			$this->setForeignDataType($that->getForeignDataType());
		}
		if($that->hasForeignDataTypeName()){
			$this->setForeignDataTypeName($that->getForeignDataTypeName());
		}
		if($that->hasForeignDataSubtypeName()){
			$this->setForeignDataSubtypeName($that->getForeignDataSubtypeName());
		}
		if($that->hasConverseRelationshipKeyName()){
			$this->setConverseRelationshipKeyName($that->getConverseRelationshipKeyName());
		}
		if($that->hasRelativeSequence()){
			$this->setRelativeSequennce($that->getRelativeSequence());
		}
		if($that->hasUpdateBehavior()){
			$this->setUpdateBehavior($that->getUpdateBehavior());
		}
		$ret = parent::copy($that);
		if($that->hasKeyParts()){
			$this->setKeyParts(replicate($that->getKeyParts()));
		}
		if($that->hasVertexContractions()){
			$this->setVertexContractions(replicate($that->getVertexContractions()));
		}
		if($that->hasOnDelete()){
			$this->setOnDelete($that->getOnDelete());
		}
		if($that->hasOnUpdate()){
			$this->setOnUpdate($that->getOnUpdate());
		}
		return $ret;
	}
	
	public function releaseIntersectionData(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasIntersectionData()){
			Debug::error("{$f} intersection data is undefined");
		}
		$intersection = $this->getIntersectionData();
		unset($this->intersectionData);
		if($this->hasAnyEventListener(EVENT_RELEASE_INTERSECTION)){
			$this->dispatchEvent(new ReleaseIntersectionDataEvent($intersection, $deallocate));
		}
		$this->release($intersection, $deallocate);
	}
	
	public function dispose(bool $deallocate=false): void{
		if($this->hasIntersectionData()){
			$this->releaseIntersectionData($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->converseRelationshipKeyName, $deallocate);
		$this->release($this->possibleIntersections, $deallocate);
		$this->release($this->foreignDataIdentifierName, $deallocate);
		$this->release($this->foreignDataStructureClass, $deallocate);
		$this->release($this->foreignDataStructureClassResolver, $deallocate);
		$this->release($this->foreignDataType, $deallocate);
		$this->release($this->foreignDataTypeName, $deallocate);
		$this->release($this->foreignDataSubtypeName, $deallocate);
		$this->release($this->relationshipType, $deallocate);
		$this->release($this->relativeSequence, $deallocate);
		$this->release($this->updateBehavior, $deallocate);
		$this->release($this->vertexContractions, $deallocate);
		$this->release($this->onDeleteReferenceOption, $deallocate);
		$this->release($this->onUpdateReferenceOption, $deallocate);
	}
}
