<?php

namespace JulianSeymour\PHPWebApplicationFramework\account;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\login\LoginAttempt;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructuralTrait;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\DatumBundle;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatumTrait;
use JulianSeymour\PHPWebApplicationFramework\input\TextInput;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\ListedIpAddress;

class UserMetadataBundle extends DatumBundle{

	use DataStructuralTrait;
	use ForeignKeyDatumTrait;

	public function __construct(?string $name=null, ?DataStructure $ds=null){
		$f = __METHOD__;
		$print = false;
		parent::__construct($name, $ds);
		if($ds !== null){
			$this->setDataStructure($ds);
		}elseif($print){
			Debug::warning("{$f} data structure is null");
		}
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			COLUMN_FILTER_ADD_TO_RESPONSE,
			COLUMN_FILTER_AUTOLOAD,
			COLUMN_FILTER_CONSTRAIN,
			COLUMN_FILTER_EAGER
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			COLUMN_FILTER_ADD_TO_RESPONSE,
			COLUMN_FILTER_AUTOLOAD,
			COLUMN_FILTER_CONSTRAIN,
			COLUMN_FILTER_EAGER
		]);
	}
	
	public function generateComponents(?DataStructure $ds = null): array{
		$f = __METHOD__;
		try{
			$print = false;
			$components = [];
			$name = $this->getName();
			// user account type, which can probably be axed by making this a subclass of ForeignMetadataBundle
			$subtypeName = "{$name}AccountType";
			$type = new StringEnumeratedDatum($subtypeName);
			$type->setValidEnumerationMap([
				ACCOUNT_TYPE_USER,
				ACCOUNT_TYPE_ADMIN,
				ACCOUNT_TYPE_DEVELOPER,
				ACCOUNT_TYPE_GROUP,
				ACCOUNT_TYPE_GUEST,
				ACCOUNT_TYPE_HELPDESK,
				ACCOUNT_TYPE_SHADOW,
				ACCOUNT_TYPE_TRANSLATOR
			]);
			if($this->hasPersistenceMode()){
				$type->setPersistenceMode($this->getPersistenceMode());
			}
			$type_string = new VirtualDatum("{$name}AccountTypeString");
			// hard password reset count at the time the object was inserted
			// $hard = new UnsignedIntegerDatum("{$name}HardResetCount", 32);
			// temporary role used during some use cases
			$role = new StringEnumeratedDatum("{$name}TemporaryRole");
			$role->volatilize();
			// $role->setDefaultValue(USER_ROLE_UNDEFINED);
			$role->setValidEnumerationMap([
				USER_ROLE_SENDER,
				USER_ROLE_RECIPIENT,
				USER_ROLE_BUYER,
				USER_ROLE_SELLER,
				USER_ROLE_HOST,
				USER_ROLE_VISITOR
			]);
			// foreign key reference to the table containing usernames, display names and normalized names
			$userNameKeyName = "{$name}NameKey";
			$username_key = new ForeignKeyDatum($userNameKeyName);
			$username_key->setForeignDataStructureClass(UsernameData::class);
			$username_key->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
			$username_key->setOnUpdate(REFERENCE_OPTION_CASCADE);
			$username_key->setOnDelete(REFERENCE_OPTION_SET_NULL);
			if($this->getConstraintFlag()){
				$username_key->constrain();
			}
			if($this->hasPersistenceMode()){
				$username_key->setPersistenceMode($this->getPersistenceMode());
			}
			// where condition for creating alises to load from username table
			$gdvc1 = new GetDeclaredVariableCommand();
			$gdvc1->setVariableName("usernames_alias.uniqueKey");
			$gdvc2 = new GetDeclaredVariableCommand();
			//$gdvc2->debug();
			//$ds->announce($gdvc2);
			$gdvc2->setVariableName("t0.{$userNameKeyName}");
			$where = new BinaryExpressionCommand();
			$where->setLeftHandSide($gdvc1);
			$where->setOperator(OPERATOR_EQUALSEQUALS);
			$where->setRightHandSide($gdvc2);
			// regular name
			$name_datum = new NameDatum("{$name}Name");
			$name_datum->setElementClass(TextInput::class);
			// $name_datum->setSearchable(true);
			$name_datum->setSubqueryClass(UsernameData::class);
			$name_datum->setSubqueryWhereCondition($where);
			$name_datum->setSubqueryColumnName("name");
			$name_datum->setReferenceColumn($username_key);
			if($this->hasDefaultValue()){
				$default = $this->getDefaultValue();
				$name_datum->setDefaultValue($default);
			}
			if($this->hasPersistenceMode()){
				$name_datum->setPersistenceMode($this->getPersistenceMode());
			}
			// display name
			$display_name = new NameDatum("{$name}DisplayName");
			//$display_name->debug();
			//$ds->announce($display_name);
			$display_name->setElementClass(TextInput::class);
			// $display_name->setSearchable(true);
			$display_name->setReferenceColumn($username_key);
			$display_name->setSubqueryClass(UsernameData::class);
			$display_name->setSubqueryWhereCondition($where);
			$display_name->setSubqueryColumnName("displayName");
			$display_name->setHumanReadableName(_("Display name"));
			if($this->hasPersistenceMode()){
				$display_name->setPersistenceMode($this->getPersistenceMode());
			}
			// normalized name
			$normalized_name = new NameDatum("{$name}NormalizedName");
			$normalized_name->setElementClass(TextInput::class);
			$normalized_name->setSubqueryClass(UsernameData::class);
			$normalized_name->setSubqueryWhereCondition($where);
			$normalized_name->setSubqueryColumnName("normalizedName");
			if($this->hasPersistenceMode()){
				$normalized_name->setPersistenceMode($this->getPersistenceMode());
			}
			// foreign key referencing the user, the most important part
			$userKeyName = "{$name}Key";
			$userKey = new ForeignKeyDatum($userKeyName);
			$userKey->setForeignDataStructureClassResolver(UserClassResolver::class);
			$userKey->setForeignDataType(DATATYPE_USER);
			if($this->hasRank() && !BACKWARDS_REFERENCES_ENABLED){
				$userKey->setRank($this->getRank());
			}
			// $userKey->setOriginalValue(DATATYPE_USER);
			// $userKey->setForeignDataTypeName("{$name}DataType");
			$userKey->setForeignDataSubtypeName($subtypeName);
			$userKey->setIndexFlag(true);
			if($this->hasRelationshipType()){
				$userKey->setRelationshipType($this->getRelationshipType());
			}else{
				$userKey->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
			}
			if($this->getAutoloadFlag()){
				$userKey->setAutoloadFlag(true);
			}
			if($this->getConstraintFlag()){
				$type->retainOriginalValue();
				if(!$type->getRetainOriginalValueFlag()){
					Debug::print("{$f} immediately after setting the flag, account type does not retain original value");
				}elseif($print){
					Debug::print("{$f} user account type retains its original value");
				}
				$userKey->constrain()->retainOriginalValue();
			}elseif($print){
				Debug::print("{$f} constraint flag is not set");
			}
			if($this->hasPersistenceMode()){
				$userKey->setPersistenceMode($this->getPersistenceMode());
			}
			if($this->isNullable()){
				$type->setNullable(true);
				$name_datum->setNullable(true);
				$username_key->setNullable(true);
				$userKey->setNullable(true);
			}
			if($this->hasDataStructure()){
				$ds = $this->getDataStructure();
				$closure = function ($event, $target) use ($ds, $name, $f, $print){
					if($event->getProperty("columnName") !== "{$name}Key"){
						return;
					}
					$struct = $event->getProperty("data");
					if($struct->hasAccountType() && $ds->hasColumn("{$name}AccountType")){
						$ds->setColumnValue("{$name}AccountType", $struct->getAccountType());
					}
					if($struct->hasColumn("{$name}TemporaryRole") && $struct->hasTemporaryRole()){
						$ds->setColumnValue("{$name}TemporaryRole", $struct->getTemporaryRole());
					}
					if($struct->hasForeignDataStructure("userNameKey")){
						if($print){
							Debug::print("{$f} username data is defined; setting it now");
						}
						$column_name = "{$name}NameKey";
						if(!BACKWARDS_REFERENCES_ENABLED && $ds->hasAllocationMode() && $ds->getAllocationMode() === ALLOCATION_MODE_SUBJECTIVE){
							if(!$ds->hasColumn($column_name)){
								Debug::error("{$f} ".$ds->getDebugString()." does not have a column {$column_name}");
							}
							$ds->getColumn($column_name)->setRank(RANK_PARENT);
						}
						$ds->setForeignDataStructure($column_name, $struct->getForeignDataStructure("userNameKey"));
					}elseif($ds->hasColumn("{$name}NameKey") && $struct->hasColumnValue("userNameKey")){
						$ds->setColumnValue("{$name}NameKey", $struct->getColumnValue("userNameKey"));
					}elseif($print){
						Debug::print("{$f} username data is not defined");
					}
					if($struct->hasColumnValue("name")){
						$ds->setColumnValue("{$name}Name", $struct->getName());
					}elseif($print){
						Debug::error("{$f} user does not have a defined name");
					}
				};
				$ds->addEventListener(EVENT_AFTER_SET_FOREIGN, $closure);
			}
			array_push($components, $type, $role, $type_string, $display_name, $name_datum, $userKey, $username_key, $normalized_name);
			return $components;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		if($this->hasDataStructure()){
			$this->releaseDataStructure();
		}
		$this->release($this->onDeleteReferenceOption, $deallocate);
		$this->release($this->onUpdateReferenceOption, $deallocate);
		$this->release($this->cacheKey, $deallocate);
		$this->release($this->timeToLive, $deallocate);
		$this->release($this->converseRelationshipKeyName, $deallocate);
		$this->release($this->foreignDataIdentifierName, $deallocate);
		$this->release($this->foreignDataStructureClass, $deallocate);
		$this->release($this->foreignDataStructureClassResolver, $deallocate);
		$this->release($this->foreignDataType, $deallocate);
		$this->release($this->foreignDataTypeName, $deallocate);
		$this->release($this->foreignDataSubtypeName, $deallocate);
		$this->release($this->possibleIntersections, $deallocate);
		$this->release($this->relationshipType, $deallocate);
		$this->release($this->relativeSequence, $deallocate);
		$this->release($this->updateBehavior, $deallocate);
		$this->release($this->vertexContractions, $deallocate);
	}
}
