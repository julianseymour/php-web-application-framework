<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\converse;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\debug;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\lazy;
use function JulianSeymour\PHPWebApplicationFramework\mutual_reference;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureCountCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureListCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureListMemberCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\HasForeignDataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\SetForeignDataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterDeleteForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterDeriveForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterExpandEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterGenerateKeyEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterInsertForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterLoadEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterReleaseAllForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterSetForeignDataStructureEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterUpdateForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeDeleteForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeDeriveForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeExpandEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeInsertForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeReleaseAllForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeSetForeignDataStructureEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeUpdateForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\DeallocateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseForeignDataStructureEvent;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use mysqli;
use JulianSeymour\PHPWebApplicationFramework\account\NormalUser;

/**
 * This trait was created to get all of the DataStructure functionality related for foreign data structures into a separate file
 * @author j
 *
 */
trait ObjectRelationalMappingTrait{
	
	/**
	 * Other DataStructures to which this object has a relationship.
	 * The keys of this array correspond to the ForeignKeyDatums that define the parameters of the relationship
	 *
	 * @var array
	 */
	private $foreignDataStructures;
	
	/**
	 * like $foreignDataStructures, except these are in the process of getting replaced during an update operation
	 *
	 * @var array
	 */
	private $oldDataStructures;
	
	public function acquireForeignDataStructure(mysqli $mysqli, string $column_name):?DataStructure{
		return $this->loadForeignDataStructure($mysqli, $column_name, false, 3, true);
	}
	
	public function getForeignDataStructures():?array{
		$f = __METHOD__;
		if(!$this->hasForeignDataStructures()){
			Debug::error("{$f} foreign data structures are undefined for this ".$this->getDebugString());
		}
		return $this->foreignDataStructures;
	}
	
	/**
	 * delete local reference to a foreign data structure $column_name and return it
	 *
	 * @param string $column_name
	 * @return DataStructure
	 */
	public function ejectForeignDataStructure(string $column_name): ?DataStructure{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($this->hasColumn($column_name)){
			$column = $this->getColumn($column_name);
			$this->ejectColumnValue($column_name);
			if($column->applyFilter(COLUMN_FILTER_FOREIGN, COLUMN_FILTER_INTERSECTION)){
				if($column->hasForeignDataTypeName()){
					$this->ejectColumnValue($column->getForeignDataTypeName());
				}
				if($column->hasForeignDataSubtypeName()){
					$this->ejectColumnValue($column->getForeignDataSubtypeName());
				}
			}elseif($print){
				Debug::print("{$f} datum at column \"{$column_name}\" is not an intersectional foreign key datum");
			}
		}
		if($this->hasForeignDataStructure($column_name)){
			$ret = $this->getForeignDataStructure($column_name);
			if($this->canReleaseForeignDataStructure($column_name)){
				$this->releaseForeignDataStructure($column_name);
			}else{
				unset($this->foreignDataStructures[$column_name]);
				if(empty($this->foreignDataStructures)){
					unset($this->foreignDataStructures);
				}
			}
			return $ret;
		}
		return null;
	}
	
	public function ejectOldDataStructure(string $column_name): DataStructure{
		$f = __METHOD__;
		if(!$this->hasOldDataStructure($column_name)){
			Debug::error("{$f} no old data structure to delete");
		}
		$ret = $this->oldDataStructures[$column_name];
		unset($this->oldDataStructures[$column_name]);
		if(!$this->hasOldDataStructures()){
			unset($this->oldDataStructures);
			if($this->getDeleteOldDataStructuresFlag()){
				$this->setDeleteOldDataStructuresFlag(false);
			}
		}
		$this->release($ret);
		return $ret;
	}
	
	/**
	 * like the above but for key lists
	 *
	 * @param string $column_name
	 * @param mixed $key
	 * @return DataStructure|NULL
	 */
	public function ejectForeignDataStructureListMember(string $column_name, $key): ?DataStructure{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasForeignDataStructureListMember($column_name, $key)){
			Debug::error("{$f} no foreign data structure list member at column \"{$column_name}\" with key \"{$key}\"");
		}elseif($print){
			Debug::print("{$f} ejecting foreign data structure list \"{$column_name}\" member with key \"{$key}\"");
		}
		$column = null;
		if($this->hasColumn($column_name)){
			$column = $this->getColumn($column_name);
			if(!$column instanceof KeyListDatum){
				Debug::error("{$f} datum at column \"{$column_name}\" is not a key list");
			}
		}
		if($print){
			$count = $this->getForeignDataStructureCount($column_name);
			Debug::print("{$f} before ejection, this object has {$count} foreign data structures in list \"{$column_name}\"");
		}
		$fds = $this->foreignDataStructures[$column_name][$key];
		if($this->canReleaseForeignDataStructureListMember($column_name, $key)){
			$this->releaseForeignDataStructureListMember($column_name, $key);
		}else{
			unset($this->foreignDataStructures[$column_name][$key]);
			if(empty($this->foreignDataStructures[$column_name])){
				unset($this->foreignDataStructures[$column_name]);
				if(empty($this->foreignDataStructures)){
					unset($this->foreignDataStructures);
				}
			}
		}
		/*if($column !== null){
			if($this->hasForeignDataStructureList($column_name)){
				$structs = $this->foreignDataStructures[$column_name];
				$keys = array_keys($structs);
				$column->setValue($keys);
			}else{
				$column->ejectValue();
			}
		}*/
		if($print){
			$count2 = $this->getForeignDataStructureCount($column_name);
			Debug::print("{$f} after ejection, this object has {$count2} foreign data structures in list \"{$column_name}\"");
			if($count === $count2){
				Debug::error("{$f} ejection failed");
			}
		}
		return $fds;
	}
	
	public function ejectOldDataStructureListMember(string $column_name, $key): DataStructure{
		$f = __METHOD__;
		if(!$this->hasOldDataStructureListMember($column_name, $key)){
			Debug::error("{$f} no old data structure \"{$column_name}\" with key \"{$key}\"");
		}
		$fds = $this->oldDataStructures[$column_name][$key];
		unset($this->oldDataStructures[$column_name][$key]);
		if(empty($this->oldDataStructures[$column_name])){
			unset($this->oldDataStructures[$column_name]);
			if(!$this->hasOldDataStructures()){
				unset($this->oldDataStructures);
				if($this->getDeleteOldDataStructuresFlag()){
					$this->setDeleteOldDataStructuresFlag(false);
				}
			}
		}
		$this->release($fds);
		return $fds;
	}
	
	public function getOldDataStructure($column_name): DataStructure{
		$f = __METHOD__;
		if(!$this->hasOldDataStructure($column_name)){
			Debug::error("{$f} old subordinate data structure of type \"{$column_name}\" is undefined");
		}
		return $this->oldDataStructures[$column_name];
	}
	
	public function getForeignDataStructure(string $column_name): DataStructure{
		$f = __METHOD__;
		if(!$this->hasForeignDataStructure($column_name)){
			if($this->hasIdentifierValue()){
				$key = $this->getIdentifierValue();
			}else{
				$key = "undefined";
			}
			$decl = $this->getDeclarationLine();
			if($this->hasColumnValue($column_name)){
				Debug::error("{$f} foreign data structure \"{$column_name}\" is undefined for object with debug ID \"{$this->debugId}\" and key \"{$key}\", declared {$decl}. However, the column value is defined. Fix this by flagging that column to auto load.");
			}else{
				Debug::error("{$f} foreign data structure \"{$column_name}\" is undefined for object with debug ID \"{$this->debugId}\" and key \"{$key}\", declared {$decl}. Furthermore, the column value is undefined.");
			}
		}elseif(is_array($this->foreignDataStructures[$column_name])){
			Debug::error("{$f} foreign data structure {$column_name} is an array for this ".$this->getDebugString());
		}
		return $this->foreignDataStructures[$column_name];
	}
	
	public function getForeignDataStructureCommand(string $column_name): GetForeignDataStructureCommand{
		return new GetForeignDataStructureCommand($this, $column_name);
	}
	
	public function hasForeignDataStructureCommand(string $column_name): HasForeignDataStructureCommand{
		return new HasForeignDataStructureCommand($this, $column_name);
	}
	
	public function setForeignDataStrucureCommand(string $column_name, $struct): SetForeignDataStructureCommand{
		return new SetForeignDataStructureCommand($this, $column_name, $struct);
	}
	
	public function hasOldDataStructure(string $column_name):bool{
		return is_array($this->oldDataStructures) && array_key_exists($column_name, $this->oldDataStructures) && isset($this->oldDataStructures[$column_name]) && is_object($this->oldDataStructures[$column_name]);
	}
	
	/**
	 * returns true if this object has a foreign data structure or structures for column $column_name,
	 * false otherwise
	 *
	 * @param string $column_name
	 * @return boolean
	 */
	public function hasForeignDataStructure(string $column_name): bool{
		$f = __METHOD__;
		try{
			$print = false;
			if(is_array($column_name)){
				Debug::error("{$f} column is an array");
			}elseif($print){
				$ds = $this->getDebugString();
			}
			$column = null;
			if($this->hasColumn($column_name)){
				if($print){
					Debug::print("{$f} this ".$this->getDebugString()." has a column \"{$column_name}\"");
				}
				$column = $this->getColumn($column_name);
				if($column instanceof VirtualDatum){
					if($column->hasReturnType() && $column->getReturnType() === TYPE_ARRAY){
						if($print){
							Debug::print("{$f} {$ds}'s column \"{$column_name}\" is a VirtualDatum with an array return type -- returning hasVirtualForeignDataStructureList");
						}
						return $this->hasVirtualForeignDataStructureList($column_name);
					}elseif($print){
						Debug::print("{$f} {$ds}'s column \"{$column_name}\" is a VirtualDatum with a non-array return type -- returning hasForeignDataStructure");
					}
					return $this->hasVirtualForeignDataStructure($column_name);
				}elseif($column instanceof KeyListDatum){
					if($print){
						Debug::print("{$f} {$ds}'s column \"{$column_name}\" is a KeyListDatum -- returning hasForeignDataStructureList");
					}
					return $this->hasForeignDataStructureList($column_name);
				}elseif($print){
					Debug::print("{$f} {$ds}'s column \"{$column_name}\" is a ".$column->getDebugString());
				}
			}elseif($print){
				Debug::print("{$f} {$ds} has no column \"{$column_name}\"");
			}//
			if(!isset($this->foreignDataStructures) || !is_array($this->foreignDataStructures)){
				if($print){
					Debug::print("{$f} foreign data structures array has not been allocated for this {$ds}");
				}
				return false;
			}elseif(!array_key_exists($column_name, $this->foreignDataStructures)){
				if($print){
					Debug::print("{$f} this {$ds} has no defined foreign data structure \"{$column_name}\"");
				}
				return false;
			}elseif(empty($this->foreignDataStructures[$column_name])){
				if($print){
					Debug::print("{$f} data structure at column \"{$column_name}\" is null");
				}
				return false;
			}elseif(!is_object($this->foreignDataStructures[$column_name])){
				if(is_array($this->foreignDataStructures[$column_name])){
					return $this->hasForeignDataStructureList($column_name);
				}elseif($print){
					Debug::print("{$f} data structure at column \"{$column_name}\" is not an object or array");
				}
				return false;
			}else{
				$status = $this->foreignDataStructures[$column_name]->getObjectStatus();
				if($status === ERROR_NOT_FOUND){
					$sc = $this->getShortClass();
					$key = $this->hasIdentifierValue() ? $this->getIdentifierValue() : '[undefined]';
					Debug::error("{$f} foreign data structure \"{$column_name}\" is defined, but object status is not found for {$sc} with key {$key}. Foreign data structure is ".$this->foreignDataStructures[$column_name]->getDebugString());
				}
			}
			return true;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function hasForeignDataStructures(): bool{
		return isset($this->foreignDataStructures) && is_array($this->foreignDataStructures) && !empty($this->foreignDataStructures);
	}
	
	public function setOldDataStructure($column_name, $old_struct){
		if(!isset($this->oldDataStructures) || !is_array($this->oldDataStructures)){
			$this->oldDataStructures = [];
		}
		return $this->oldDataStructures[$column_name] = $this->claim($old_struct);
	}
	
	/**
	 * set this object as the foreign data structure (or a member of a list of foreign
	 * data structures) referenced by $struct at the converse relationship index stored in this
	 * object's datum at $column_name
	 *
	 * @param string $column_name
	 *        	: index of the datum that stores the ConverseRelationshipKey that $struct uses to reference this object
	 * @param DataStructure $struct
	 *        	: data structure that stores the reference to this object needing reciprocation
	 */
	protected function reciprocateRelationship(string $column_name, DataStructure $struct):void{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag() && $struct->getDebugFlag();
			if(!$this->hasColumn($column_name)){
				Debug::error("{$f} no datum at column \"{$column_name}\"");
			}
			$column = $this->getColumn($column_name);
			if(!$column->hasConverseRelationshipKeyName()){
				Debug::error("{$f} datum at column \"{$column_name}\" does not know the name of its converse relationship key");
			}
			$converse_key = $column->getConverseRelationshipKeyName();
			if($struct->hasColumn($converse_key)){
				if($print){
					Debug::print("{$f} foreign data structure has a datum at converse relationship column \"{$converse_key}\"");
				}
				$converse_datum = $struct->getColumn($converse_key);
				$mapping = $converse_datum->getRelationshipType();
				if($print){
					Debug::print("{$f} datum \"{$converse_key}\" has relationship type {$mapping}");
				}
			}elseif($column->hasRelationshipType()){
				$mapping = $column->getConverseRelationshipType();
				if($print){
					Debug::print("{$f} datum \"{$column_name}\" is mapped by {$mapping}\"");
				}
			}else{
				Debug::error("{$f} data structure does not have a datum for converse relationship \"{$converse_key}\", and datum at column \"{$column_name}\" does not know how many objects map to it");
			}
			switch($mapping){
				case RELATIONSHIP_TYPE_ONE_TO_ONE:
				case RELATIONSHIP_TYPE_MANY_TO_ONE:
					if($print){
						Debug::print("{$f} {$column_name} is a one to one or many to one relationship");
					}
					if($struct->hasColumn($converse_key)){
						if(!$struct->getColumn($converse_key)->hasConverseRelationshipKeyName()){
							if(true || $print){
								Debug::warning("{$f} for whatever reason, foreign data structure ".$struct->getDebugString()."'s column {$converse_key} does not know its 1:1 converse relationship key name with our ".$this->getDebugString()."'s column {$column_name}, fixing that now");
							}
							$struct->getColumn($converse_key)->setConverseRelationshipKeyName($column_name);
						}
					}
					
					if(
						!$struct->hasForeignDataStructure($converse_key) || (
							$struct->hasColumn($converse_key) && (
								!$struct->hasColumnValue($converse_key) || (
									$this->hasIdentifierValue() &&
									$struct->getColumnValue($converse_key) !== $this->getIdentifierValue()
									)
								)
							)
						){
							if($print){
								if(!$struct->hasForeignDataStructure($converse_key)){
									Debug::print("{$f} struct does not have a foreign data structure mapped to \"{$converse_key}\"");
								}else{
									Debug::print("{$f} struct has something mapped to \"{$converse_key}\"");
								}
								if($struct->hasColumn($converse_key)){
									Debug::print("{$f} struct has a column \"{$converse_key}\"");
									$converse_value = $struct->getColumnValue($converse_key);
									$identifier = $this->hasIdentifierValue() ? $this->getIdentifierValue() : "undefined";
									if($converse_value !== $identifier){
										Debug::print("{$f} struct's value \"{$converse_value}\" for column \"{$converse_key}\" differs from this object's identifier \"{$identifier}\"");
									}else{
										Debug::print("{$f} struct's value \"{$converse_value}\" for column \"{$converse_key}\" is identical to this object's identifier");
									}
									Debug::print("{$f} mapping this object to foreign data structure's converse relationship key \"{$converse_key}\"");
								}else{
									Debug::print("{$f} struct does not have a column \"{$converse_key}\"");
								}
							}
							$struct->setForeignDataStructure($converse_key, $this);
					}elseif($print){
						Debug::print("{$f} foreign data structure has already mapped this object to converse relationship key \"{$converse_key}\"");
					}
					if(BACKWARDS_REFERENCES_ENABLED){
						$deref_closure1 = function(DataStructure $thing1, bool $deallocate=false)
						use ($column_name, $f, $print){
							if($thing1->hasForeignDataStructure($column_name)){
								$thing1->releaseForeignDataStructure($column_name, $deallocate);
							}
						};
						$deref_closure2 = function(DataStructure $thing2, bool $deallocate=false)
						use ($converse_key, $f, $print){
							if($thing2->hasForeignDataStructure($converse_key)){
								$thing2->releaseForeignDataStructure($converse_key, $deallocate);
							}
						};
						mutual_reference($this, $struct, $deref_closure1, $deref_closure2, EVENT_RELEASE_FOREIGN, EVENT_RELEASE_FOREIGN, [
							"columnName" => $column_name
						], [
							"columnName" => $converse_key
						]);
					}
					break;
				case RELATIONSHIP_TYPE_ONE_TO_MANY:
				case RELATIONSHIP_TYPE_MANY_TO_MANY:
					if($print){
						Debug::print("{$f} {$column_name} is a one to many or many to many relationship");
					}
					if($struct->hasColumn($converse_key)){
						if(!$struct->getColumn($converse_key)->hasConverseRelationshipKeyName()){
							$struct->getColumn($converse_key)->setConverseRelationshipKeyName($column_name);
						}
					}
					
					$keygen_closure = function(?AfterGenerateKeyEvent $event, DataStructure $target)
					use ($column_name, $struct, $converse_key, $mapping){
						$f = __FUNCTION__;
						$print = false;
						if($event instanceof AfterGenerateKeyEvent){
							$target->removeEventListener($event);
						}
						if(!$struct->hasForeignDataStructureListMember($converse_key, $target->getIdentifierValue())){
							if($print){
								Debug::print("{$f} mapping this object as a member of foreign data structure's key list at converse relationship column \"{$converse_key}\"");
							}
							$struct->setForeignDataStructureListMember($converse_key, $target);
							
							if(BACKWARDS_REFERENCES_ENABLED){
								$key1 = $this->getIdentifierValue();
								$key2 = $struct->getIdentifierValue();
								$deref_closure1 = function(DataStructure $that, bool $deallocate=false)
								use($column_name, $key2, $f, $print){
									if($that->hasForeignDataStructure($column_name)){
										$that->releaseForeignDataStructureListMember($column_name, $key2, $deallocate);
									}
								};
								$deref_closure2 = function(DataStructure $struct, bool $deallocate=false)
								use($converse_key, $key1, $f, $print){
									if($struct->hasForeignDataStructure($converse_key)){
										$struct->releaseForeignDataStructureListMember($converse_key, $$key1, $deallocate);
									}
								};
								mutual_reference($this, $struct, $deref_closure1, $deref_closure2, EVENT_RELEASE_FOREIGN, EVENT_RELEASE_FOREIGN, [
									"columnName" => $column_name,
									"foreignKey" => $key2
								], [
									"columnName" => $converse_key,
									"foreignKey" => $key1
								]);
							}
						}elseif($print){
							Debug::print("{$f} foreign data structure at column \"{$column_name}\" already has this object as a member of its key list at converse relationship column \"{$converse_key}\"");
						}
					};
					if($this->hasIdentifierValue()){
						if($print){
							Debug::print("{$f} identifier value is defined, calling closure immediately");
						}
						$keygen_closure(null, $this);
					}else{
						if($print){
							Debug::print("{$f} identifier value is undefined. Adding AfterGenerateKeyEvent listener");
						}
						$this->addEventListener(EVENT_AFTER_GENERATE_KEY, $keygen_closure);
					}
					break;
				default:
					Debug::error("{$f} invalid mapping \"{$mapping}\"");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function subordinateForeignDataStructure(string $column_name, DataStructure $subordinate_struct, ?string $converse_key_name=null, ?int $relationship_type=null){
		$f = __METHOD__;
		if($converse_key_name === null){
			if(!$this->hasColumn($column_name)){
				Debug::error("{$f} column \"{$column_name}\" is undefined for this ".$this->getDebugString());
			}
			$column = $this->getColumn($column_name);
			if(!$column->hasConverseRelationshipKeyName()){
				Debug::error("{$f} column \"{$column_name}\" does not know its converse relationship key name for this ".$this->getDebugString());
			}
			$converse_key_name = $column->getConverseRelationshipKeyName();
		}
		if(!$subordinate_struct->hasColumn($converse_key_name)){
			if($relationship_type === null){
				if(!$this->hasColumn($column_name)){
					Debug::error("{$f} column \"{$column_name}\" is undefined for this ".$this->getDebugString());
				}
				$relationship_type = $this->getColumn($column_name)->getRelationshipType();
			}
			switch($relationship_type){
				case RELATIONSHIP_TYPE_ONE_TO_ONE:
				case RELATIONSHIP_TYPE_ONE_TO_MANY:
					$new_column = new ForeignKeyDatum($converse_key_name, converse($relationship_type));
					break;
				case RELATIONSHIP_TYPE_MANY_TO_ONE:
				case RELATIONSHIP_TYPE_MANY_TO_MANY:
					$new_column = new KeyListDatum($converse_key_name, converse($relationship_type));
					break;
				default:
					Debug::error("{$f} invalid relationship type {$relationship_type}");
			}
			$new_column->setConverseRelationshipKeyName($column_name);
			$new_column->volatilize();
			$subordinate_struct->pushColumn($new_column);
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			$subordinate_struct->getColumn($converse_key_name)->setRank(RANK_PARENT);
		}
	}
	
	public function hasForeignDataStructureListMember(string $column_name, string $key): bool{
		return $this->hasForeignDataStructureList($column_name) && array_key_exists($key, $this->foreignDataStructures[$column_name]);
	}
	
	public function hasOldDataStructureListMember(string $column_name, string $key):bool{
		return $this->hasOldDataStructureList($column_name) && array_key_exists($key, $this->oldDataStructures[$column_name]);
	}
	
	protected function beforeSetForeignDataStructureHook(string $column_name, DataStructure $struct):int{
		if($this->hasAnyEventListener(EVENT_BEFORE_SET_FOREIGN)){
			$this->dispatchEvent(new BeforeSetForeignDataStructureEvent($column_name, $struct));
		}
		return SUCCESS;
	}
	
	protected function afterSetForeignDataStructureHook(string $column_name, DataStructure $struct):int{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($this->hasColumn($column_name)){
			$column = $this->getColumn($column_name);
			if($column->hasConverseRelationshipKeyName()){
				$converse = $column->getConverseRelationshipKeyName();
				if($print){
					Debug::print("{$f} datum at column \"{$column_name}\" has an converse relationship key name \"{$converse}\"");
				}
				$reciprocate = false;
				$relationship_type = $column->getRelationshipType();
				switch($relationship_type){
					case RELATIONSHIP_TYPE_ONE_TO_ONE:
					case RELATIONSHIP_TYPE_ONE_TO_MANY:
						if(
							!$struct->hasForeignDataStructure($converse) || (
							$this->hasIdentifierValue() &&
							!$struct->getForeignDataStructure($converse)->hasIdentifierValue()
						) || (
							!$this->hasIdentifierValue() &&
							$struct->getForeignDataStructure($converse)->hasIdentifierValue()
						) || (
							$this->hasIdentifierValue() &&
							$struct->getForeignDataStructure($converse)->hasIdentifierValue() &&
							$this->getIdentifierValue() !== $struct->getForeignDataStructure($converse)->getIdentifierValue()
						)){
							$reciprocate = true;
						}
						break;
					case RELATIONSHIP_TYPE_MANY_TO_ONE:
					case RELATIONSHIP_TYPE_MANY_TO_MANY:
						if(
							!$struct->hasForeignDataStructureList($converse) || (
							$this->hasIdentifierValue() &&
							!$struct->hasForeignDataStructureListMember($converse, $this->getIdentifierValue())
						)
						){
							$reciprocate = true;
						}
						break;
					default:
						Debug::error("{$f} invalid relationship type {$relationship_type}");
				}
				if($reciprocate){
					$this->reciprocateRelationship($column_name, $struct);
					if($print){
						$did = $this->getDebugId();
						Debug::print("{$f} returned from reciprocating relationship \"{$column_name}\"; debug ID is \"{$did}\"");
					}
				}elseif($print){
					Debug::print("{$f} we either don't have enough information to reciprocate the relationship {$column_name} to {$converse}, or it has already been assigned from the other side");
				}
			}elseif($print){
				Debug::print("{$f} datum at column \"{$column_name}\" does not have an converse relationship key name");
			}
		}elseif($print){
			Debug::print("{$f} no, this object does not have a column \"{$column_name}\"");
		}
		if($this->hasAnyEventListener(EVENT_AFTER_SET_FOREIGN)){
			$this->dispatchEvent(new AfterSetForeignDataStructureEvent($column_name, $struct));
		}
		return SUCCESS;
	}
	
	public function withForeignDataStructure($column_name, $struct): DataStructure{
		$this->setForeignDataStructure($column_name, $struct);
		return $this;
	}
	
	/**
	 * Sets $struct as a member of $this->foreignDataStructures at index $column_name.
	 * Also automates a reciprocating the relationship.
	 * Can be intercepted/extended by redeclaring before and afterSetForeignDataStructureHook()
	 *
	 * @param string $column_name
	 * @param DataStructure $struct
	 * @return DataStructure
	 */
	public /*final*/ function setForeignDataStructure(string $column_name, DataStructure $struct):?DataStructure{
		$f = __METHOD__;
		try{
			$print = false;
			$claim = true;
			if(!isset($struct)){
				Debug::error("{$f} received a null data structure");
			}elseif(is_array($struct)){
				Debug::error("{$f} don't call this on arrays");
				return $this->setForeignDataStructureList($column_name, $struct);
			}elseif(!is_object($struct)){
				$gottype = gettype($struct);
				Debug::error("{$f} struct is a {$gottype}, not an object");
			}elseif(!$struct instanceof DataStructure){
				$class = $struct->getClass();
				Debug::error("{$f} struct is a \"{$class}\"");
			}elseif(!$struct->getAllocatedFlag()){
				Debug::error("{$f} foreign {$column_name} ".$struct->getDebugString()." has already been deallocated");
			}elseif($struct->isDeleted()){
				if($print){
					Debug::print("{$f} data structure passed for foreign relationship \"{$column_name}\" is deleted or not found");
				}
				$key = $struct->getIdentifierValue();
				if($print){
					Debug::error("{$f} data structure with key \"{$key}\" is deleted");
				}
			}elseif($struct->hasObjectStatus() && $struct->getObjectStatus() === ERROR_NOT_FOUND){
				if($print){
					Debug::print("{$f} data structure passed for foreign relationship \"{$column_name}\" was not found");
				}
				$key = $struct->getIdentifierValue();
				$sc = get_short_class($struct);
				Debug::error("{$f} data structure of class {$sc} with key \"{$key}\" not found");
			}elseif($print){
				Debug::print("{$f} entered for column \"{$column_name}\"");
				if($this->getDebugFlag() && $column_name === "accessAttemptKey"){
					Debug::printStackTraceNoExit();
				}
			}
			if($this->hasColumn($column_name)){
				if($print){
					Debug::print("{$f} this object has a column \"{$column_name}\"");
				}
				$column = $this->getColumn($column_name);
				if($column instanceof KeyListDatum){
					Debug::error("{$f} don't call this function on KeyListDatum \"{$column_name}\"");
				}elseif(!$column instanceof ForeignKeyDatum){
					Debug::error("{$f} don't call this on something other than foreign key datums. Column name was \"{$column_name}\"");
				}
				if(!BACKWARDS_REFERENCES_ENABLED){
					if($struct->hasAllocationMode() && $struct->getAllocationMode() === ALLOCATION_MODE_SUBJECTIVE){
						if($print){
							Debug::print("{$f} foreign data structure is the subjective user data");
						}
						$column->setRank(RANK_PARENT);
					}elseif($column->hasConverseRelationshipKeyName()){
						$crkn = $column->getConverseRelationshipKeyName();
						if($struct->hasColumn($crkn) && $struct->getColumn($crkn)->getRank() === RANK_CHILD){
							if($print){
								Debug::print("{$f} flagging foreign data structure's column {$crkn} as a parent key");
							}
							$column->setRank(RANK_PARENT);
						}elseif($print){
							Debug::print("{$f} foreign data structure {$column_name} does not have a column {$crkn} that is flagged as a child key");
						}
					}elseif($print){
						Debug::print("{$f} foreign data structure is not the subjective user data, and column {$column_name} does not know its converse relationship key name");
					}
					if($column->getRank() === RANK_PARENT){
						if($print){
							Debug::print("{$f} column \"{$column_name}\" has the parent key flag set");
						}
						$claim = false;
					}else{
						if(
							$this->hasAllocationMode()
							&& $this->getAllocationMode() === ALLOCATION_MODE_SUBJECTIVE
							){
								$column->setRank(RANK_CHILD);
						}
						if($column->hasConverseRelationshipKeyName() && $column->getRank() === RANK_CHILD){
							$crkn = $column->getConverseRelationshipKeyName();
							if($struct->hasColumn($crkn)){
								$struct->getColumn($crkn)->setRank(RANK_PARENT);
							}
						}
					}
				}elseif($print){
					Debug::print("{$f} backwards references are enabled");
				}
			}elseif($print){
				Debug::print("{$f} this object does not have a column \"{$column_name}\"");
			}
			if(!isset($this->foreignDataStructures) || !is_array($this->foreignDataStructures)){
				$this->foreignDataStructures = [];
			}
			$status = $this->beforeSetForeignDataStructureHook($column_name, $struct);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} before set foreign data structure hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($this->canReleaseForeignDataStructure($column_name)){
				$this->releaseForeignDataStructure($column_name);
			}
			$this->foreignDataStructures[$column_name] = $struct;
			if($claim){
				$this->claim($struct);
				$that = $this;
				$random1 = sha1(random_bytes(32));
				$random2 = sha1(random_bytes(32));
				$closure1 = function(DeallocateEvent $event, DataStructure $target)
				use ($that, $column_name, $random2){
					$target->removeEventListener($event);
					if($that->hasEventListener(EVENT_RELEASE_FOREIGN, $random2)){
						$that->removeEventListener(EVENT_RELEASE_FOREIGN, $random2);
					}
					if($that->hasForeignDataStructure($column_name)){
						$that->releaseForeignDataStructure($column_name);
					}
				};
				$struct->addEventListener(EVENT_DEALLOCATE, $closure1, $random1);
				$closure2 = function(ReleaseForeignDataStructureEvent $event, DataStructure $target)
				use($column_name, $random1){
					if($column_name === $event->getColumnName()){
						$target->removeEventListener($event);
						$struct = $event->getForeignDataStructure();
						if($struct->hasEventListener(EVENT_DEALLOCATE, $random1)){
							$struct->removeEventListener(EVENT_DEALLOCATE, $random1);
						}
					}
				};
				$this->addEventListener(EVENT_RELEASE_FOREIGN, $closure2, $random2);
			}
			if(!$this->hasForeignDataStructure($column_name)){
				Debug::error("{$f} immediately after setting foreign data structure \"{$column_name}\" it is undefined");
			}elseif($this->hasColumn($column_name)){
				$column = $this->getColumn($column_name);
				if(!$struct->hasIdentifierValue()){
					if($print){
						Debug::print("{$f} foreign data structure at column \"{$column_name}\" does not have a key");
					}
				}elseif($this->getReceptivity() !== DATA_MODE_SEALED){
					$key = $struct->getIdentifierValue(); // Key();
					if($print){
						Debug::print("{$f} assigning key \"{$key}\" to column \"{$column_name}\"");
					}
					$this->setColumnValue($column_name, $key);
					if($column->applyFilter(COLUMN_FILTER_FOREIGN, COLUMN_FILTER_INTERSECTION)){
						if($column->hasForeignDataTypeName()){
							$this->setColumnValue($column->getForeignDataTypeName(), $struct->getDataType());
						}
						if(
							//$column->hasForeignDataSubtypeName() && (
								$struct->hasColumnValue('subtype') 
								|| $struct instanceof StaticSubtypeInterface
							//)
						){
							if(!$column->hasForeignDataSubtypeName()){
								Debug::error("{$f} column {$column_name} ".$column->getDebugString()." of this ".$this->getDebugString()." does not know its foreign data subtype name when referencing a ".$struct->getDebugString());
							}
							$subtype = $struct->getSubtype();
							$this->setColumnValue($column->getForeignDataSubtypeName(), $subtype);
						}
					}elseif($print){
						Debug::print("{$f} datum \"{$column_name}\" is not a foreign key datum interface, or is not polymorphic");
					}
				}elseif($print){
					Debug::print("{$f} this object has been sealed");
				}
				//moved reciprocation into afterSetForeignDataStructureHook
			}elseif($print){
				Debug::print("{$f} this object does not have a datum at column \"{$column_name}\"");
			}
			$status = $this->afterSetForeignDataStructureHook($column_name, $struct);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} after set foreign data structure hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully set foreign data structure \"{$column_name}\" for ".$this->getDebugString());
			}
			return $struct;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * returns foreign data structure list member at an integer offset (foreign data structure lists are associative)
	 *
	 * @param string $column_name
	 *        	: name of the foreign data structure list
	 * @param int $offset
	 *        	: e.g. 0 returns the first item
	 * @return mixed
	 */
	public function getForeignDataStructureListMemberAtOffset(string $column_name, int $offset): DataStructure{
		$f = __METHOD__;
		if(!$this->hasForeignDataStructureList($column_name)){
			Debug::error("{$f} no foreign data structure list for column \"{$column_name}\"");
		}
		$keys = array_keys($this->getForeignDataStructureList($column_name));
		if(!array_key_exists($offset, $keys)){
			Debug::error("{$f} invalid offset \"{$offset}\"");
		}
		$key = $keys[$offset];
		return $this->getForeignDataStructureListMember($column_name, $key);
	}
	
	public function loadForeignDataStructureFromConverse(mysqli $mysqli, string $column_name, bool $lazy=false, int $recursion_depth = 0, bool $subordinate=false):?DataStructure{
		$f = __METHOD__;
		try{
			$print = false;
			if(!$this->hasColumn($column_name)){
				Debug::error("{$f} column \"{$column_name}\" is undefined for this ".$this->getDebugString());
			}
			$column = $this->getColumn($column_name);
			$struct_class = $column->getForeignDataStructureClass($this);
			$struct = new $struct_class();
			$crkn = $column->getConverseRelationshipKeyName();
			if(!BACKWARDS_REFERENCES_ENABLED){
				if($column->getRank() === RANK_CHILD || $this->hasAllocationMode() && $this->getAllocationMode() === ALLOCATION_MODE_SUBJECTIVE){
					$subordinate = true;
				}
				if($subordinate){
					$this->subordinateForeignDataStructure($column_name, $struct, $crkn);
				}
			}
			if(!$struct->hasColumn($crkn)){
				Debug::error("{$f} {$struct_class} does not have a column \"{$crkn}\"");
			}
			if($column->hasDatabaseName()){
				$struct->setDatabaseName($column->getDatabaseName());
			}
			if($column->hasTableName()){
				$struct->setTableName($column->getTableName());
			}
			$pm = $struct->getColumn($crkn)->getPersistenceMode();
			switch($pm){
				case PERSISTENCE_MODE_DATABASE:
					if($print){
						Debug::print("{$f} good news, it's stored in the database");
					}
					$status = $struct->load($mysqli, $crkn, $this->getIdentifierValue());
					break;
				case PERSISTENCE_MODE_INTERSECTION:
					$status = $struct->load(
					$mysqli,
					$struct_class::whereIntersectionalForeignKey(static::class, $crkn), [
					$this->getIdentifierValue(),
					$crkn
					]
					);
					break;
				default:
					if($print){
						Debug::print("{$f} unimplemented: using converse relationship key to load undefined foreign data structure {$column_name} with foreign key persistence mode \"".Debug::getPersistenceModeString($pm)."\"");
					}
					return null;
			}
			if($status === ERROR_NOT_FOUND){
				if($print){
					Debug::print("{$f} not found");
				}
				deallocate($struct);
				return null;
			}elseif($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} loading {$struct_class} by {$crkn} returned error status \"{$err}\"");
				return null;
			}elseif($print){
				Debug::print("{$f} successfully loaded foreign data structure {$column_name} by going through converse relationship key \"{$crkn}\"");
			}
			if($recursion_depth > 0){
				if($print){
					Debug::print("{$f} about to load data structures recursively");
				}
				$status = $struct->loadForeignDataStructures($mysqli, $lazy, $recursion_depth - 1, $subordinate);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} recursively calling this function on foreign data structure with column \"{$column_name}\" returned error status \"{$err}\"");
					$struct->setObjectStatus($status);
				}
			}elseif($print){
				Debug::print("{$f} recursion depth is 0");
			}
			return $this->setForeignDataStructure($column_name, $struct);
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * load the foreign data structure at index $column_name
	 *
	 * @param mysqli $mysqli
	 * @param string $column_name
	 *        	: index of foreign data structure to load
	 * @param boolean $lazy
	 *        	: if true, lazy load the foreign data structure
	 * @param number $recursion_depth
	 *        	: if > 0, call recursively on foreign data structures with $recursion_depth-1
	 * @return NULL|DataStructure
	 */
	public function loadForeignDataStructure(mysqli $mysqli, string $column_name, bool $lazy = false, int $recursion_depth = 0, bool $subordinate=false): ?DataStructure{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			$column = $this->getColumn($column_name);
			if(!BACKWARDS_REFERENCES_ENABLED){
				if($column->getRank() === RANK_CHILD || $this->hasAllocationMode() && $this->getAllocationMode() === ALLOCATION_MODE_SUBJECTIVE){
					$subordinate = true;
				}
			}
			if(!$column->hasValue()){
				if($column->hasConverseRelationshipKeyName() && $this->hasIdentifierValue()){
					if($print){
						Debug::print("{$f} key at column \"{$column_name}\" is undefined, we have enough information to load it anyway");
					}
					return $this->loadForeignDataStructureFromConverse($mysqli, $column_name, $lazy, $recursion_depth, $subordinate);
				}elseif($print){
					Debug::print("{$f} nothing we can do here");
				}
				return null;
			}
			$key = $this->getColumnValue($column_name);
			if($print){
				Debug::print("{$f} key \"{$key}\" is stored in column \"{$column_name}\"");
			}
			// return if it was already registered
			if(registry()->hasObjectRegisteredToKey($key)){
				if($print){
					Debug::print("{$f} the registry already knows about our {$column_name} with identifier \"{$key}\"");
				}
				$struct = registry()->getRegisteredObjectFromKey($key);
				if($struct->isDeleted() || $struct->isNotFound()){
					if($print){
						Debug::print("{$f} data structure is deleted unfortunately");
					}
					return null;
				}elseif($print){
					Debug::print("{$f} column \"{$column_name}\" maps to ".$struct->getDebugString());
				}
				return $this->setForeignDataStructure($column_name, $struct);
			}elseif($print){
				Debug::print("{$f} nothing in the registry maps to key \"{$key}\"");
			}
			$struct_class = $column->getForeignDataStructureClass($this);
			if($print){
				Debug::print("{$f} column \"{$column_name}\" maps to a {$struct_class}");
			}
			$struct = new $struct_class();
			if($subordinate && !BACKWARDS_REFERENCES_ENABLED && $column->hasConverseRelationshipKeyName()){
				$this->subordinateForeignDataStructure($column_name, $struct);
			}
			$struct->setIdentifierValue($key);
			// attempt to load from cache
			if($struct->isRegistrable() && CACHE_ENABLED && $column->hasTimeToLive()){
				if(cache()->hasAPCu($key)){
					cache()->expireAPCu($key, $column->getTimeToLive());
					$results = cache()->getAPCu($key);
					$status = $struct->processQueryResultArray($mysqli, $results);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} processing cached query results returned error status \"{$err}\"");
						$this->setObjectStatus($status);
						return null;
					}elseif($print){
						Debug::print("{$f} successfully loaded cached foreign data structure \"{$column_name}\" with key \"{$key}\"");
					}
					// return $this->setForeignDataStructure($column_name, $struct);
					$struct->setLoadedFlag(true);
					$struct->setObjectStatus(SUCCESS);
				}elseif($print){
					Debug::print("{$f} cache miss for foreign data structure with key \"{$key}\"");
				}
				$struct->setTimeToLive($column->getTimeToLive());
			}elseif($print){
				Debug::print("{$f} foreign column does not have a cache duration");
			}
			if($lazy && !$column->getEagerLoadFlag()){
				if($print){
					Debug::print("{$f} lazy loading data structure \"{$column_name}\"");
				}
				// event handler for lazy recursive foreign data structure loading
				if($recursion_depth > 0){
					if($print){
						Debug::print("{$f} about to arm event handler for lazy recursive load");
					}
					$closure = function (AfterLoadEvent $event, DataStructure $target)
					use ($mysqli, $recursion_depth, $subordinate){
						$f = __FUNCTION__;
						$print = false;
						if($print){
							$tc = $target->getClass();
							Debug::print("{$f} lazy recursive loading foreign data structures for a {$tc}");
						}
						$target->removeEventListener($event);
						$status = $target->loadForeignDataStructures($mysqli, true, $recursion_depth - 1, $subordinate);
						if($status !== SUCCESS){
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} lazy recursive foreign data structure loading returned error status \"{$err}\"");
							$target->setObjectStatus($status);
						}
					};
					$struct->addEventListener(EVENT_AFTER_LOAD, $closure);
				}elseif($print){
					Debug::print("{$f} recursion depth is 0");
				}
				lazy()->deferLoad($struct);
			}else{
				if($print){
					Debug::print("{$f} we are not lazy loading this data structure");
				}
				if($struct->getLoadedFlag()){
					$status = SUCCESS;
				}else{
					$status = $struct->loadFromKey($mysqli, $key);
				}
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} loading foreign data structure at column \"{$column_name}\" with key \"{$key}\" returned error status \"{$err}\"");
					$struct->setObjectStatus($status);
				}elseif($recursion_depth > 0){
					if($print){
						Debug::print("{$f} about to load data structures recursively");
					}
					$status = $struct->loadForeignDataStructures($mysqli, $lazy, $recursion_depth - 1, $subordinate);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} recursively calling this function on foreign data structure with column \"{$column_name}\" returned error status \"{$err}\"");
						$struct->setObjectStatus($status);
					}
				}elseif($print){
					Debug::print("{$f} recursion depth is 0");
				}
			}
			if($print){
				Debug::print("{$f} loaded {$column_name} with key \"{$key}\"");
			}
			return $this->setForeignDataStructure($column_name, $struct);
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * load foreign data structure list indexed at KeyListDatum $column_name
	 *
	 * @param mysqli $mysqli
	 * @param string $column_name
	 * @param boolean $lazy
	 * @param number $recursion_depth
	 * @return int
	 */
	protected final function loadForeignDataStructureList(mysqli $mysqli, string $column_name, bool $lazy = false, int $recursion_depth = 0, bool $subordinate=false){
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			$column = $this->getColumn($column_name);
			if(!BACKWARDS_REFERENCES_ENABLED){
				if($column->getRank() === RANK_CHILD || $this->hasAllocationMode() && $this->getAllocationMode() === ALLOCATION_MODE_SUBJECTIVE){
					$subordinate = true;
				}
			}
			$type = $column->getRelationshipType();
			switch($type){
				case RELATIONSHIP_TYPE_ONE_TO_MANY:
				case RELATIONSHIP_TYPE_MANY_TO_MANY:
					if($column->getLoadedFlag()){
						if($print){
							Debug::print("{$f} key list \"{$column_name}\" has already been loaded");
							if(!$this->hasForeignDataStructureList($column_name)){
								Debug::print("{$f} there is no foreign data structure list \"{$column_name}\"");
							}else{
								$count = $this->getForeignDataStructureCount($column_name);
								Debug::print("{$count} foreign data structures in list \"{$column_name}\"");
							}
						}
						return SUCCESS;
					}elseif(!$this->hasIdentifierValue()){
						if($print){
							Debug::print("{$f} this object doesn't have a key yet");
						}
						return SUCCESS;
					}
					$keys = [];
					if($print){
						Debug::print("{$f} this is an X to many relationship and must be loaded from an intersection table");
					}
					$intersections = $column->getAllPossibleIntersectionData();
					foreach($intersections as $intersection){
						$fdsc = $intersection->getForeignDataStructureClass();
						$kgm = $fdsc::getKeyGenerationMode();
						switch($kgm){
							case KEY_GENERATION_MODE_HASH:
							case KEY_GENERATION_MODE_PSEUDOKEY:
								break;
							case KEY_GENERATION_MODE_LITERAL:
							case KEY_GENERATION_MODE_NATURAL:
							case KEY_GENERATION_MODE_UNIDENTIFIABLE:
							default:
								if($print){
									Debug::print("{$f} key generation mode {$kgm}, have to skip this one");
								}
								continue 2;
								
						}
						$db = $intersection->getDatabaseName();
						$table = $intersection->getTableName();
						$select = new SelectStatement("foreignKey");
						$select->from($db, $table)->where(new AndCommand(new WhereCondition("hostKey", OPERATOR_EQUALS), new WhereCondition("relationship", OPERATOR_EQUALS)));
						$select->setTypeSpecifier("ss");
						$params = [
							$this->getIdentifierValue(),
							$column_name
						];
						$select->setParameters($params);
						if($print){
							Debug::print("{$f} query for loading foreign keys from intersection table \"{$table}\" is \"{$select}\" with the following parameters:");
							Debug::printArray($params);
						}
						$result = $select->executeGetResult($mysqli);
						$results = $result->fetch_all(MYSQLI_ASSOC);
						$result->free_result();
						foreach($results as $r){
							$keys[$r['foreignKey']] = $intersection->getForeignDataStructureClass();
						}
					}
					if(!empty($keys)){
						if($print){
							Debug::print("{$f} loaded the following keys for column \"{$column_name}\":");
							Debug::printArray($keys);
						}
						if(!$column->getRetainOriginalValueFlag()){
							$column->retainOriginalValue();
						}
						$column->setOriginalValue(array_keys($keys));
					}elseif($print){
						Debug::print("{$f} failed to load any keys for column \"{$column_name}\"");
					}
					if($column->hasOriginalValue()){
						$column->setValue($column->getOriginalValue());
					}
					break;
				default: // unused
					Debug::error("{$f} this should be impossible");
			}
			if(!empty($keys)){
				foreach($keys as $key => $struct_class){
					if(is_array($key)){
						Debug::error("{$f} value of column \"{$column_name}\" is a multidimensional array");
					}
					if($print){
						Debug::print("{$f} about to acquire a {$struct_class} with key \"{$key}\" for foreign data structure list \"{$column_name}\"");
					}
					if($struct_class::isRegistrableStatic()){
						if($print){
							Debug::print("{$f} class \"{$struct_class}\" is registrable");
						}
						if(registry()->has($key)){
							$struct = registry()->getRegisteredObjectFromKey($key);
							if($print){
								$sc2 = $struct->getClass();
								Debug::print("{$f} there is already a {$sc2} mapped to key \"{$key}\". About to set a {$struct_class} with key \"{$key}\" for column \"{$column_name}\"");
							}
							$this->setForeignDataStructureListMember($column_name, $struct);
							continue;
						}elseif($print){
							Debug::print("{$f} nothing registered for key \"{$key}\"");
						}
					}elseif($print){
						Debug::print("{$f} data structure is not registrable");
					}
					$struct = new $struct_class();
					if($subordinate && !BACKWARDS_REFERENCES_ENABLED){
						$this->subordinateForeignDataStructure($column_name, $struct);
					}
					$struct->setIdentifierValue($key);
					if($lazy){
						if($print){
							Debug::print("{$f} about to defer loading of {$struct_class} with key \"{$key}\"");
						}
						lazy()->deferLoad($struct);
					}else{
						if($print){
							Debug::print("{$f} we are not lazy loading this data structure");
						}
						$status = $struct->load($mysqli, $struct_class::getIdentifierNameStatic(), $key);
						if($status !== SUCCESS){
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} loading foreign data structure returned error status \"{$err}\"");
							$struct->setObjectStatus($status);
						}elseif($recursion_depth > 0){
							if($print){
								Debug::print("{$f} about to load data structures recursively");
							}
							$status = $struct->loadForeignDataStructures($mysqli, $lazy, $recursion_depth - 1, $subordinate);
							if($status !== SUCCESS){
								$err = ErrorMessage::getResultMessage($status);
								Debug::warning("{$f} recursively calling this function on foreign data structure with column \"{$column_name}\" returned error status \"{$err}\"");
								$struct->setObjectStatus($status);
							}
						}elseif($print){
							Debug::print("{$f} recursion depth is 0");
						}
					}
					if($print){
						Debug::print("{$f} about to set a {$struct_class} with key \"{$key}\" for column \"{$column_name}\"");
					}
					$this->setForeignDataStructureListMember($column_name, $struct);
				}
			}elseif($print){
				Debug::print("{$f} key list is empty");
			}
			$column->setLoadedFlag(true);
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * load all foreign data structures that are flagged as autoload
	 *
	 * @param mysqli $mysqli
	 * @param boolean $lazy
	 *        	: if true, lazy load all foreign data structures
	 * @param number $recursion_depth
	 *        	: if > 0, call recursively on all loaded structures with $recursion_depth - 1
	 * @return int
	 */
	public function loadForeignDataStructures(mysqli $mysqli, bool $lazy = false, int $recursion_depth = 0, bool $subordinate = false): int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			$columns = $this->getFilteredColumns(COLUMN_FILTER_AUTOLOAD);
			if(empty($columns)){
				if($print){
					Debug::print("{$f} no foreign data structures to load");
				}
				return SUCCESS;
			}elseif($print){
				$count = count($columns);
				Debug::print("{$f} about to load {$count} foreign data structures from the following columns:");
				Debug::printArray(array_keys($columns));
			}
			foreach($columns as $column_name => $column){
				if(!$this->hasColumn($column_name)){
					if($print){
						Debug::print("{$f} no such column \"{$column_name}\"");
					}
					continue;
				}
				if(!BACKWARDS_REFERENCES_ENABLED){
					if($column->getRank() === RANK_CHILD || $this->hasAllocationMode() && $this->getAllocationMode() === ALLOCATION_MODE_SUBJECTIVE){
						$subordinate = true;
					}
				}
				if($column instanceof ForeignKeyDatum){
					if($print){
						Debug::print("{$f} datum \"{$column_name}\" is a ForeignKeyDatum");
					}
					if($this->hasForeignDataStructure($column_name)){
						if($print){
							Debug::print("{$f} already loaded foreign data structure \"{$column_name}\"");
						}
						continue;
					}
					if(
						$column->hasValue() ||
						$column->getPersistenceMode() === PERSISTENCE_MODE_VOLATILE &&
						$column->hasConverseRelationshipKeyName() &&
						$this->hasIdentifierValue()
						){
							$this->loadForeignDataStructure($mysqli, $column_name, $lazy, $recursion_depth, $subordinate);
					}else{
						if($print){
							Debug::print("{$f} column \"{$column_name}\" has no value");
						}
						continue;
					}
				}elseif($column instanceof KeyListDatum){
					if($print){
						Debug::print("{$f} datum \"{$column_name}\" is a KeyListDatum");
					}
					if(
						(
							$column->hasValue() &&
							!$column->applyFilter(COLUMN_FILTER_ALIAS)
							) || $column->applyFilter(COLUMN_FILTER_INTERSECTION)
						){
							if($print){
								Debug::print("{$f} about to load ".get_short_class($this)."'s foreign data structure list for column \"{$column_name}\"");
								if($column->applyFilter(COLUMN_FILTER_INTERSECTION)){
									Debug::print("{$f} column \"{$column_name}\" is stored in an intersection table");
								}
							}
							$this->loadForeignDataStructureList($mysqli, $column_name, $lazy, $recursion_depth, $subordinate);
					}elseif($print){
						Debug::print("{$f} no keys at column \"{$column_name}\"");
					}
				}else{
					$column_class = $column->getClass();
					Debug::error("{$f} datum at column \"{$column_name}\" is a {$column_class}");
				}
			}
			if($print){
				Debug::print("{$f} assigned data structures; returning normally");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function hasForeignDataStructureList(string $column_name): bool{
		return isset($this->foreignDataStructures) && is_array($this->foreignDataStructures) && array_key_exists($column_name, $this->foreignDataStructures) && is_array($this->foreignDataStructures[$column_name]) && !empty($this->foreignDataStructures[$column_name]);
	}
	
	public function getForeignDataStructureCount(string $column_name): int{
		$f = __METHOD__;
		if(!isset($this->foreignDataStructures)){
			return 0;
		}elseif(!is_array($this->foreignDataStructures)){
			Debug::error("{$f} foreignDataStructures is set, but not an array");
		}elseif(!array_key_exists($column_name, $this->foreignDataStructures)){
			return 0;
		}elseif(is_array($this->foreignDataStructures[$column_name])){
			return count($this->foreignDataStructures[$column_name]);
		}elseif($this->foreignDataStructures[$column_name] instanceof DataStructure){
			return 1;
		}
		Debug::error("{$f} none of the above");
	}
	
	/**
	 * sets the foreign data structure list, destroying the existing one if applicable
	 *
	 * @param string $column_name
	 * @param DataStructure[] $list
	 * @return DataStructure[]
	 */
	public function setForeignDataStructureList(string $column_name, array $list): array{
		$f = __METHOD__;
		$print = false;
		if(!is_array($list)){
			Debug::error("{$f} list is not an array");
		}elseif(empty($list)){
			Debug::error("{$f} don't call this function on an empty array");
		}elseif(!isset($this->foreignDataStructures) || !is_array($this->foreignDataStructures)){
			$this->foreignDataStructures = [
				$column_name => []
			];
		}else{
			$this->foreignDataStructures[$column_name] = [];
		}
		if($print && ! $this->hasColumn($column_name)){
			Debug::print("{$f} no datum at column \"{$column_name}\"");
		}
		foreach($list as $struct){
			$this->setForeignDataStructureListMember($column_name, $struct);
		}
		return $list;
	}
	
	public function releaseForeignDataStructure(string $column_name, bool $deallocate=false):int{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(!$this->canReleaseForeignDataStructure($column_name)){
			Debug::error("{$f} cannot release foreign data structure \"{$column_name}\" for this ".$this->getDebugString());
		}
		$fds = $this->getForeignDataStructure($column_name);
		unset($this->foreignDataStructures[$column_name]);
		if(empty($this->foreignDataStructures)){
			unset($this->foreignDataStructures);
		}
		if($this->hasAnyEventListener(EVENT_RELEASE_FOREIGN)){
			$this->dispatchEvent(new ReleaseForeignDataStructureEvent($column_name, $fds, null, $deallocate));
		}
		$print = $print || $fds->getDebugFlag();
		$this->disableDeallocation();
		if($print){
			Debug::print("{$f} about to release foreign data structure \"{$column_name}\" ".$fds->getDebugString()." for this ".$this->getDebugString());
		}
		$this->release($fds, $deallocate);
		$this->enableDeallocation();
		return SUCCESS;
	}
	
	public function releaseForeignDataStructureListMember(string $column_name, $key, bool $deallocate=false):int{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(!$this->canReleaseForeignDataStructureListMember($column_name, $key)){
			Debug::error("{$f} cannot release foreign data structure list \"{$column_name}\" member with key \"{$key}\" for this ".$this->getDebugString());
		}
		$fds = $this->getForeignDataStructureListMember($column_name, $key);
		unset($this->foreignDataStructures[$column_name][$key]);
		if(empty($this->foreignDataStructures[$column_name])){
			unset($this->foreignDataStructures[$column_name]);
			if(empty($this->foreignDataStructures)){
				unset($this->foreignDataStructures);
			}
		}
		if($this->hasAnyEventListener(EVENT_RELEASE_FOREIGN)){
			$this->dispatchEvent(new ReleaseForeignDataStructureEvent($column_name, $fds, $key,$deallocate));
		}
		if($print){
			Debug::print("{$f} about to release {$column_name} list member ".$fds->getDebugString()." with key {$key} from this ".$this->getDebugString());
		}
		$this->disableDeallocation();
		$this->release($fds, $deallocate);
		$this->enableDeallocation();
		if($this->hasColumn($column_name)){
			$column = $this->getColumn($column_name);
			if($this->hasForeignDataStructureList($column_name)){
				$column->setValue(array_keys($this->foreignDataStructures[$column_name]));
			}else{
				$column->ejectValue();
			}
		}
		return SUCCESS;
	}
	
	/**
	 * assign a foreign data structure as a member of the list at column $column_name.
	 *
	 * @param string $column_name
	 * @param DataStructure $struct
	 * @return DataStructure
	 */
	public final function setForeignDataStructureListMember(string $column_name, ...$structs): int{
		$f = __METHOD__;
		try{
			$pushed = 0;
			foreach($structs as $struct){
				$print = $this->getDebugFlag() || $struct->getDebugFlag();
				if(is_array($struct)){
					Debug::error("{$f} data structure is an array. This function only accepts objects as its second parameter");
				}elseif(!is_object($struct)){
					$gottype = gettype($struct);
					Debug::error("{$f} structure's type is \"{$gottype}\"");
				}elseif($struct->getObjectStatus() === ERROR_DELETED){
					Debug::print("{$f} structure is deleted");
					return -1;
				}
				$status = $this->beforeSetForeignDataStructureHook($column_name, $struct);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} beforeSetForeignDataStructureHook returned error status \"{$err}\"");
					$this->setObjectStatus($status);
					return -1;
				}
				if(!is_array($this->foreignDataStructures)){
					$this->foreignDataStructures = [];
				}
				if($struct->hasColumn($struct->getIdentifierName()) && ! $struct->hasIdentifierValue()){
					if($print){
						Debug::print("{$f} foreign data structure does not have a key; generating one now");
					}
					$status = $struct->generateKey();
					if($status === ERROR_KEY_COLLISION){
						if($print){
							Debug::print("{$f} key collision detected; assigning the already existing object instead");
						}
						$key = $struct->getIdentifierValue();
						$struct = registry()->getRegisteredObjectFromKey($key);
					}elseif($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} generateKey returned error status \"{$err}\"");
						$struct->setObjectStatus($status);
						return -1;
					}
				}
				$key = $struct->getIdentifierValue();
				if(!is_string($key) && ! is_int($key)){
					$gottype = gettype($key);
					Debug::error("{$f} data structure returned a {$gottype}");
				}elseif(!array_key_exists($column_name, $this->foreignDataStructures)){
					$this->foreignDataStructures[$column_name] = [];
				}elseif(!is_array($this->foreignDataStructures[$column_name])){
					Debug::error("{$f} column {$column_name} does not map to an array");
				}
				if($this->canReleaseForeignDataStructureListMember($column_name, $key)){
					if($print){
						Debug::print("{$f} key \"{$key}\" was already mapped");
					}
					$this->releaseForeignDataStructureListMember($column_name, $key);
				}elseif($print){
					Debug::print("{$f} {$column_name} with key \"{$key}\" is not already assigned");
				}
				$claim = true;
				if($this->hasColumn($column_name)){
					$column = $this->getColumn($column_name);
					if(!BACKWARDS_REFERENCES_ENABLED){
						if(
							$struct->hasAllocationMode()
							&& $struct->getAllocationMode() === ALLOCATION_MODE_SUBJECTIVE
							){
								$column->setRank(RANK_PARENT);
						}else{
							if($column->hasConverseRelationshipKeyName()){
								$crkn = $column->getConverseRelationshipKeyName();
								if($struct->hasColumn($crkn) && $struct->getColumn($crkn)->getRank() === RANK_CHILD){
									$column->setRank(RANK_PARENT);
								}
							}
						}
						if($column->getRank() === RANK_PARENT){
							$claim = false;
						}else{
							if(
								$this->hasAllocationMode()
								&& $this->getAllocationMode() === ALLOCATION_MODE_SUBJECTIVE
								){
									$column->setRank(RANK_CHILD);
							}
							if($column->getRank() === RANK_CHILD && $column->hasConverseRelationshipKeyName()){
								$crkn = $column->getConverseRelationshipKeyName();
								if($struct->hasColumn($crkn)){
									$struct->getColumn($crkn)->setRank(RANK_PARENT);
								}
							}
						}
					}
					if(!$column->inArray($key)){
						if($print){
							Debug::print("{$f} no, array does not have a value \"{$key}\"");
						}
						$column->pushValue($key);
						if(!$this->hasColumnValue($column_name)){
							Debug::error("{$f} immediatelty after pushing value, column \"{$column_name}\" has no value");
						}elseif(!$column->applyFilter(COLUMN_FILTER_VALUED)){
							Debug::error("{$f} immediately after pushing value, failed filter " . COLUMN_FILTER_VALUED);
						}elseif($print){
							Debug::print("{$f} pushed value successfully");
						}
					}elseif($print){
						Debug::print("{$f} column \"{$column_name}\" already has a value \"{$key}\"");
					}
				}elseif($print){
					if($print){
						Debug::print("{$f} this object does not have a column \"{$column_name}\"");
					}
				}
				if($print){
					Debug::print("{$f} appending data structure with key \"{$key}\" to column \"{$column_name}\"");
				}
				$this->foreignDataStructures[$column_name][$key] = $struct;
				if($claim){
					$this->claim($struct);
					$that = $this;
					$random1 = sha1(random_bytes(32));
					$random2 = sha1(random_bytes(32));
					$closure1 = function(DeallocateEvent $event, DataStructure $target)
					use ($that, $column_name, $key, $random2){
						$target->removeEventListener($event);
						if($that->hasEventListener(EVENT_RELEASE_FOREIGN, $random2)){
							$that->removeEventListener(EVENT_RELEASE_FOREIGN, $random2);
						}
						if($that->hasForeignDataStructureListMember($column_name, $key)){
							$that->releaseForeignDataStructureListMember($column_name, $key);
						}
					};
					$struct->addEventListener(EVENT_DEALLOCATE, $closure1, $random1);
					$closure2 = function(ReleaseForeignDataStructureEvent $event, DataStructure $target)
					use ($column_name, $key, $random1){
						if(
							$event->getColumnName() === $column_name &&
							$event->getForeignKey() === $key
						){
							$target->removeEventListener($event);
							$struct = $event->getForeignDataStructure();
							if($struct->hasEventListener(EVENT_DEALLOCATE, $random1)){
								$struct->removeEventListener(EVENT_DEALLOCATE, $random1);
							}
						}
					};
					$this->addEventListener(EVENT_RELEASE_FOREIGN, $closure2, $random2);
				}
				$status = $this->afterSetForeignDataStructureHook($column_name, $struct);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} afterSetForeignDataStructureHook returned error status \"{$err}\"");
					$this->setObjectStatus($status);
					return -1;
				}
				$pushed++;
			}
			return $pushed;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function hasVirtualForeignDataStructure(string $column_name):bool{
		return false;
	}
	
	public function hasVirtualForeignDataStructureList(string $column_name):bool{
		return false;
	}
	
	public function hasVirtualForeignDataStructureListMember(string $column_name, $index):bool{
		return false;
	}
	
	public function getForeignDataStructureKey(string $column_name){
		return $this->getForeignDataStructure($column_name)->getIdentifierValue();
	}
	
	public function hasOldDataStructures():bool{
		return isset($this->oldDataStructures) && is_array($this->oldDataStructures) && !empty($this->oldDataStructures);
	}
	
	public function hasOldDataStructureList(string $column_name): bool{
		return $this->hasOldDataStructures() && array_key_exists($column_name, $this->oldDataStructures) && is_array($this->oldDataStructures[$column_name]) && !empty($this->oldDataStructures[$column_name]);
	}
	
	public function getForeignDataStructureList(string $column_name): array{
		$f = __METHOD__;
		if(!$this->hasForeignDataStructureList($column_name)){
			Debug::error("{$f} no foreign data structure list at column \"{$column_name}\"");
			return [];
		}
		return $this->foreignDataStructures[$column_name];
	}
	
	public function getForeignDataStructureListMember(string $column_name, $key): DataStructure{
		$f = __METHOD__;
		if(!$this->hasForeignDataStructureListMember($column_name, $key)){
			Debug::error("{$f} undefined foreign data structure list member at column \"{$column_name}\", key \"{$key}\"");
		}
		$list = $this->getForeignDataStructureList($column_name);
		$s = $list[$key];
		return $s;
	}
	
	public function getForeignDataStructureListMemberCommand(string $column_name, $key): GetForeignDataStructureListMemberCommand{
		return new GetForeignDataStructureListMemberCommand($this, $column_name, $key);
	}
	
	protected function fulfillMutuallyReferentialForeignKeys():int{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} about to deal with mutually referential 1:1 foreign keys for class ".$this->getShortClass());
		}
		$columns = $this->getFilteredColumns(ForeignKeyDatum::class, COLUMN_FILTER_VALUED, COLUMN_FILTER_UPDATE, "!" . COLUMN_FILTER_INTERSECTION);
		if(!empty($columns)){
			foreach($columns as $cn => $column){
				if($column->getRelationshipType() !== RELATIONSHIP_TYPE_ONE_TO_ONE){ // XXX maybe make this a filter
					if($print){
						Debug::print("{$f} column \"{$cn}\" is not one to one");
					}
					continue;
				}elseif($print){
					Debug::print("{$f} column \"{$cn}\" is one-to-one");
				}
				$status = $column->fulfillMutualReference();
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} fulfillMutualReference for column \"{$cn}\" returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("{$f} fulfillMutualReference succeeded for column \"{$cn}\"");
				}
			}
		}elseif($print){
			Debug::print("{$f} no foreign key datums with defined values");
			$this->debugMutualOneToOneForeignKeys();
		}
		return SUCCESS;
	}
	
	public function debugMutualOneToOneForeignKeys(){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(!$this->getDebugFlag()){
			return SUCCESS;
		}
		$columns = $this->getFilteredColumns(
			ForeignKeyDatum::class,
			COLUMN_FILTER_VALUED,
			COLUMN_FILTER_UPDATE,
			"!".COLUMN_FILTER_INTERSECTION
		);
		if(empty($columns)){
			Debug::print("{$f} there are no mututal 1:1 foreign keys to debug");
		}elseif($print){
			Debug::print("{$f} success");
		}
		return SUCCESS;
	}
	
	/**
	 * generates keys for all foreign data structures that persist in the database in some way, and that don't already have keys
	 *
	 * @return int
	 */
	protected function generateUndefinedForeignKeys(){
		$f = __METHOD__;
		try{
			$print = false;
			$columns = $this->getFilteredColumns(COLUMN_FILTER_FOREIGN);
			if(empty($columns)){
				if($print){
					Debug::print("{$f} no foreign key/key list datums that get stored in the database");
				}
				return SUCCESS;
			}
			foreach($columns as $name => $column){
				$p = $column->getPersistenceMode();
				switch($p){
					case PERSISTENCE_MODE_DATABASE:
					case PERSISTENCE_MODE_EMBEDDED:
					case PERSISTENCE_MODE_ENCRYPTED:
					case PERSISTENCE_MODE_INTERSECTION:
						if($print){
							Debug::print("{$f} persistence mode \"{$p}\" gets stored in the database");
						}
						break;
					case PERSISTENCE_MODE_ALIAS:
					case PERSISTENCE_MODE_COOKIE:
					case PERSISTENCE_MODE_SESSION:
					case PERSISTENCE_MODE_VOLATILE:
						if($print){
							Debug::print("{$f} persistence mode \"{$p}\" does not get stored in the database");
						}
						continue 2;
					default:
						Debug::error("{$f} undefined persistence mode \"{$p}\"");
				}
				if($column instanceof ForeignKeyDatum){
					if(!$this->hasForeignDataStructure($name)){
						if($print){
							Debug::print("{$f} no foreign data structure \"{$name}\"");
						}
						continue;
					}elseif($column->hasValue()){
						if($print){
							$value = $column->getValue();
							Debug::print("{$f} column \"{$name}\" already has a value, and it's \"{$value}\"");
						}
						continue;
					}elseif($print){
						Debug::print("{$f} about to generate key for foreign data structure \"{$name}\"");
					}
					$struct = $this->getForeignDataStructure($name);
					$status = $struct->generateKey();
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} generating key for foreign data structure \"{$name}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}
					$this->setForeignDataStructure($name, $struct);
				}elseif($column instanceof KeyListDatum){
					if(!$this->hasForeignDataStructureList($name)){
						continue;
					}
					$list = $this->getForeignDataStructureList($name);
					if(empty($list)){
						if($print){
							Debug::print("{$f} foreign data structure list \"{$name}\" is empty");
						}
						continue;
					}
					foreach($list as $struct){
						if($struct->hasIdentifierValue()){
							if($print){
								Debug::print("{$f} one of the foreign data structures for column \"{$name}\" already has an identifier");
							}
							continue;
						}elseif($print){
							Debug::print("{$f} about to generate key for foreign data structure list member of column \"{$name}\"");
						}
						$status = $struct->generateKey();
						if($status !== SUCCESS){
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} generating key for foreign data structure list member of column \"{$name}\" returned error status \"{$err}\"");
							return $this->setObjectStatus($status);
						}
						$this->setForeignDataStructureListMember($name, $struct);
					}
				}else{
					Debug::error("{$f} column \"{$name}\" is somehow not a foreign key or key list");
				}
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	protected function beforeDeriveForeignDataStructuresHook(): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_DERIVE)){
			$this->dispatchEvent(new BeforeDeriveForeignDataStructuresEvent());
		}
		return SUCCESS;
	}
	
	/**
	 * this is for generating foreign data structures that can derive themselves form a template
	 * e.g.
	 * a record of a taxable event that is generated from the tax object
	 *
	 * @return int
	 */
	public function deriveForeignDataStructures(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if($this->getFlag("derived")){
				if($print){
					Debug::print("{$f} already called this function");
				}
				return SUCCESS;
			}
			$status = $this->beforeDeriveForeignDataStructuresHook();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeDeriveForeignDataStructuresHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$derived = $this->getFilteredColumns(COLUMN_FILTER_TEMPLATE);
			if(empty($derived)){
				if($print){
					Debug::print("{$f} there are no derivable columns to process");
				}
				$this->setFlag("derived", true);
				return $this->afterDeriveForeignDataStructuresHook();
			}elseif($print){
				Debug::print("{$f} about to process ".count($derived)." foreign columns");
			}
			foreach($derived as $name => $column){
				if(!$column->hasValue() && ! $column->hasOriginalValue()){
					if($print){
						Debug::print("{$f} column \"{$name}\" lacks an original or current value, continuing");
					}
					continue;
				}elseif($print){
					Debug::print("{$f} about to generate/delete derived foreign data structures for column \"{$name}\"");
				}
				$inv = $column->getAppliedTemplateColumnName();
				$new = $column->hasValue() ? $column->getValue() : [];
				$old = $column->hasOriginalValue() ? $column->getOriginalValue() : [];
				// generate derived foreign data structures, and flag them for insertion
				$insert_us = array_diff($new, $old);
				if(!empty($insert_us)){
					if($print){
						$count = count($insert_us);
						Debug::print("{$f} {$count} foreign data structures to generate");
					}
					foreach($insert_us as $key){
						if($print){
							Debug::print("{$f} about to apply template for foreign data structure \"{$name}\" with key \"{$key}\"");
						}
						$fds = registry()->get($key)->applyTemplate($mysqli, $this);
						if($fds === null){
							if($print){
								Debug::print("{$f} applyTemplate returned null, continuing");
							}
							return null;
						}
						$fds->setInsertFlag(true);
						$this->setForeignDataStructureListMember($inv, $fds);
					}
					$this->setPostInsertForeignDataStructuresFlag(true);
					$this->setPostUpdateForeignDataStructuresFlag(true);
				}elseif($print){
					Debug::print("{$f} there are no new foreign data structure to generate");
				}
				// flag old foreign data structures for deletion that are no longer needed
				$delete_us = array_diff($old, $new);
				if(!empty($delete_us)){
					if($print){
						$count = count($delete_us);
						Debug::print("{$f} {$count} template keys to delete");
					}
					foreach($this->getForeignDataStructureList($inv) as $key => $fds){
						if(in_array($fds->getColumnValue("templateKey"), $delete_us, true)){
							if($print){
								Debug::print("{$f} foreign data structure in list \"{$name}\" with key \"{$key}\" has an unused template key and will be flagged for deletion");
							}
							$fds->setDeleteFlag(true);
							$this->setPostUpdateForeignDataStructuresFlag(true);
							$this->setDeleteForeignDataStructuresFlag(true);
						}elseif($print){
							Debug::print("{$f} foreign data structure in list \"{$name}\" with key \"{$key}\" will NOT be flagged for deletion");
						}
					}
				}elseif($print){
					Debug::print("{$f} there are no template keys to delete");
				}
			}
			$this->setFlag("derived", true);
			$status = $this->afterDeriveForeignDataStructuresHook();
			if($print){
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	protected function afterDeriveForeignDataStructuresHook(): int{
		if($this->hasAnyEventListener(EVENT_AFTER_DERIVE)){
			$this->dispatchEvent(new AfterDeriveForeignDataStructuresEvent());
		}
		return SUCCESS;
	}
	
	/**
	 * push foreign data structure list members to foreign dat astructure list $phylum
	 *
	 * @param string $phylum
	 * @param DataStructure[] ...$structs
	 * @return int
	 */
	public function pushForeignDataStructureListMember(string $phylum, ...$structs): int{
		$f = __METHOD__;
		$print = false;
		if(!isset($structs)){
			Debug::error("{$f} missing second parameter");
		}
		$pushed = 0;
		foreach($structs as $struct){
			$this->setForeignDataStructureListMember($phylum, $struct);
			$key = $struct->getIdentifierValue();
			if(!$this->hasForeignDataStructureListMember($phylum, $key)){
				if($print){
					Debug::print("{$f} pushed new child with identifier \"{$key}\"");
				}
				$pushed ++;
			}elseif($print){
				Debug::print("{$f} already have a child with identifier \"{$key}\"");
			}
		}
		return $pushed;
	}
	
	/**
	 * Override accessor for virtual foreign data structures by column name
	 *
	 * @param string $column_name
	 * @param string $key_or_offset
	 */
	public function getVirtualForeignDataStructure(string $column_name, $key_or_offset = null){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}
	
	public function getVirtualForeignDataStructureList(string $column_name){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}
	
	public function getVirtualForeignDataStructureListMember(string $column_name, $index){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}
	
	/**
	 * call reload() on foreign data structures
	 *
	 * @param mysqli $mysqli
	 * @param boolean $foreign
	 * @return int
	 */
	public final function reloadForeignDataStructures(mysqli $mysqli, bool $foreign = true): int{
		$f = __METHOD__;
		try{
			$print = false;
			$columns = $this->getFilteredColumns(COLUMN_FILTER_FOREIGN);
			if(!empty($columns)){
				foreach($columns as $column_name => $column){
					if($column instanceof ForeignKeyDatum){
						if($this->hasForeignDataStructure($column_name)){
							$fds = $this->getForeignDataStructure($column_name);
							if(!$fds->getLoadedFlag() || $fds->getReloadedFlag()){
								if($print){
									if(!$fds->getLoadedFlag()){
										Debug::print("{$f} foreign data structure \"{$column_name}\" was not loaded from the database in the first place");
									}elseif($fds->getReloadedFlag()){
										Debug::print("{$f} foreign data structure \"{$column_name}\" has already been reloaded");
									}
								}
								continue;
							}
							$status = $fds->reload($mysqli, $foreign);
							if($status !== SUCCESS){
								$err = ErrorMessage::getResultMessage($status);
								$fdsc = $fds->getClass();
								$fdsk = $fds->getIdentifierValue();
								Debug::warning("{$f} reloading foreign data structure {$fdsc} \"{$column_name}\" with key \"{$fdsk}\" returned error status \"{$err}\"");
								return $this->setObjectStatus($status);
							}
						}elseif($print){
							Debug::print("{$f} foreign object \"{$column_name}\" will not be reloaded");
						}
					}elseif($column instanceof KeyListDatum){
						if($this->hasForeignDataStructureList($column_name)){
							$list = $this->getForeignDataStructureList($column_name);
							foreach($list as $foreign_key => $fds){
								if(!$fds->getLoadedFlag() || $fds->getReloadedFlag()){
									if($print){
										if(!$fds->getLoadedFlag()){
											Debug::print("{$f} foreign data structure \"{$column_name}\" with key \"{$foreign_key}\" was not loaded from the database in the first place");
										}elseif($fds->getReloadedFlag()){
											Debug::print("{$f} foreign data structure \"{$column_name}\" with key \"{$foreign_key}\" has already been reloaded");
										}
									}
									continue;
								}
								$status = $fds->reload($mysqli, $foreign);
								if($status !== SUCCESS){
									$err = ErrorMessage::getResultMessage($status);
									Debug::warning("{$f} reloading foreign data structure from list \"{$column_name}\" with key \"{$foreign_key}\" returned error status \"{$err}\"");
									return $this->setObjectStatus($status);
								}
							}
						}elseif($print){
							Debug::print("{$f} no foreign data structure list \"{$column_name}\"");
						}
					}
				}
			}elseif($print){
				Debug::print("{$f} no foreign key columns");
			}
			return SUCCESS;
		}catch(Exception $x){
			X($f, $x);
		}
	}
	
	protected function beforeUpdateForeignDataStructuresHook(mysqli $mysqli, string $when): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_UPDATE_FOREIGN)){
			$this->dispatchEvent(new BeforeUpdateForeignDataStructuresEvent($when));
		}
		return SUCCESS;
	}
	
	/**
	 * update foreign data structures that are flagged for update
	 *
	 * @param mysqli $mysqli
	 * @param string $when
	 *        	: see description of similarly-named parameter for insertForeignDataStructures()
	 * @return int
	 */
	protected function updateForeignDataStructures(mysqli $mysqli, string $when): int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			switch($when){
				case CONST_BEFORE:
					$columns = $this->getFilteredColumns(COLUMN_FILTER_BEFORE);
					// $status = $this->permit(user(), DIRECTIVE_PREUPDATE_FOREIGN);
					break;
				case CONST_AFTER:
					$columns = $this->getFilteredColumns(COLUMN_FILTER_AFTER);
					// $status = $this->permit(user(), DIRECTIVE_POSTUPDATE_FOREIGN);
					break;
				default:
					Debug::error("{$f} invalid foreign data structure type");
			}
			$status = $this->beforeUpdateForeignDataStructuresHook($mysqli, $when);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeUpdateForeignDataStructuresHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} beforeUpdateForeignDataStructureHook returned success");
			}
			foreach($columns as $column_name => $column){
				if($column instanceof KeyListDatum){
					$multiple = true;
				}elseif($column instanceof ForeignKeyDatum){
					$multiple = false;
				}else{
					Debug::error("{$f} neither of the above for column \"{$column_name}\"");
				}
				if(($multiple && ! $this->hasForeignDataStructureList($column_name)) || ! $this->hasForeignDataStructure($column_name)){
					continue;
				}
				if($multiple){
					$structs = $this->getForeignDataStructureList($column_name);
				}else{
					$struct = $this->getForeignDataStructure($column_name);
					if(!$struct->getUpdateFlag()){
						continue;
					}
					$structs = [
						$struct
					];
				}
				foreach($structs as $struct){
					if(!$struct->getUpdateFlag()){
						if($print){
							Debug::print("{$f} struct at column \"{$column_name}\" does not have its update flag set");
						}
						continue;
					}elseif($print){
						Debug::print("{$f} about to update subordinate data structure at column \"{$column_name}\"");
					}
					$status = $struct->update($mysqli);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} updating data structure at column \"{$column_name}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print){
						Debug::print("{$f} successfully updated subordinate data structure at column \"{$column_name}\"");
					}
				}
			}
			$status = $this->afterUpdateForeignDataStructuresHook($mysqli, $when);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} afterUpdateForeignDataStructuresHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} afterUpdateForeignDataStructureHook returned success");
			}
			
			if($print){
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	protected function afterUpdateForeignDataStructuresHook(mysqli $mysqli, string $when): int{
		if($this->hasAnyEventListener(EVENT_AFTER_UPDATE_FOREIGN)){
			$this->dispatchEvent(new AfterUpdateForeignDataStructuresEvent($when));
		}
		return SUCCESS;
	}
	
	public function getPreUpdateForeignDataStructuresFlag():bool{
		return $this->getFlag(DIRECTIVE_PREUPDATE_FOREIGN);
	}
	
	public function setPreUpdateForeignDataStructuresFlag(bool $value=true):bool{
		return $this->setFlag(DIRECTIVE_PREUPDATE_FOREIGN, $value);
	}
	
	public function getPostUpdateForeignDataStructuresFlag():bool{
		return $this->getFlag(DIRECTIVE_POSTUPDATE_FOREIGN);
	}
	
	public function setPostUpdateForeignDataStructuresFlag(bool $value=true):bool{
		return $this->setFlag(DIRECTIVE_POSTUPDATE_FOREIGN, $value);
	}
	
	public function postUpdateForeignDataStructures(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			// insert foreign data structures to which this object does not have a constrained reference, or which have a constrained reference to this object
			if($this->getPostInsertForeignDataStructuresFlag()){
				if($print){
					Debug::print("{$f} post-insert foreign data structure flag is set");
				}
				$status = $this->insertForeignDataStructures($mysqli, CONST_AFTER);
				$this->setPostInsertForeignDataStructuresFlag(false);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} post-inserting foreign data structures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("{$f} successfully post-inserted subordinate data structure(s)");
				}
			}elseif($print){
				Debug::print("{$f} post-insert foreign data structures flag is not set");
			}
			// like the above, except update
			if($this->getPostUpdateForeignDataStructuresFlag()){
				if($print){
					Debug::print("{$f} post-update foreign data structures flag is set -- about to see if any of them need to be updated");
				}
				$status = $this->updateForeignDataStructures($mysqli, CONST_AFTER);
				$this->setPostUpdateForeignDataStructuresFlag(false);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} post-updateForeignDataStructures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("{$f} successfully post-updated foreign data structures");
				}
			}elseif($print){
				Debug::print("{$f} post-update foreign data structures flag is not set");
			}
			// delete foreign data structures
			if($this->getDeleteForeignDataStructuresFlag()){
				$status = $this->deleteForeignDataStructures($mysqli);
				$this->setDeleteForeignDataStructuresFlag(false);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::print("{$f} deleteForeignDataStructures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
			}elseif($print){
				Debug::print("{$f} deleteForeignDataStructures flag is not set");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function updateForeignColumns(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false;
			$embedded = $this->getEmbeddedDataStructures();
			if(!empty($embedded)){
				foreach($embedded as $groupname => $e){
					if(!$e->getUpdateFlag()){
						if($print){
							Debug::print("{$f} embedded data structure \"{$groupname}\" does not need an update");
						}
						continue;
					}
					$status = $e->update($mysqli);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} updating embedded data structure \"{$groupname}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print){
						Debug::print("{$f} updated embedded data structure \"{$groupname}\"");
					}
				}
				deallocate($embedded);
			}elseif($print){
				Debug::print("{$f} no embedded data structures");
			}
			// update polymorphic keys stored in intersection tables
			$polys = $this->getFilteredColumns(COLUMN_FILTER_INTERSECTION, COLUMN_FILTER_UPDATE);
			if(!empty($polys)){
				if($print){
					Debug::print("{$f} about to update the following foreing columns stored in intersection tables:");
					Debug::printArray(array_keys($polys));
				}
				foreach($polys as $vn => $poly){
					if($print){
						Debug::print("{$f} about to call updateIntersectionTables on column \"{$vn}\"");
					}
					$status = $poly->updateIntersectionTables($mysqli);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} updating intersection table for datum \"{$vn}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print){
						Debug::print("{$f} successfully updated intersection tables on column \"{$vn}\"");
					}
				}
			}elseif($print){
				Debug::print("{$f} no polymorphic foreign keys to update");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function preUpdateForeignDataStructures(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false;
			// insert foreign data structures to which this object has a constrained reference
			if($this->getPreInsertForeignDataStructuresFlag()){
				if($print){
					Debug::print("{$f} preinsert subordinate data structure flag is set");
				}
				$status = $this->insertForeignDataStructures($mysqli, CONST_BEFORE);
				$this->setPreInsertForeignDataStructuresFlag(false);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} preinserting foreign data structure returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("{$f} successfully preinserted foreign data structure(s)");
				}
			}elseif($print){
				Debug::print("{$f} preinsert foreign data structures flag is not set");
			}
			// update foreign data structures to which this object has a constrained reference
			if($this->getPreUpdateForeignDataStructuresFlag()){
				if($print){
					Debug::print("{$f} preupdate foreign data structures flag is set -- about to see if any of them need to be updated");
				}
				$status = $this->updateForeignDataStructures($mysqli, CONST_BEFORE);
				$this->setPreUpdateForeignDataStructuresFlag(false);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} preupdateForeignDataStructures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("{$f} successfully preupdated foreign data structures");
				}
			}elseif($print){
				Debug::print("{$f} preupdate foreign data structures flag is not set");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function copyForeignDataStructures($that):int{
		$f = __METHOD__;
		try{
			$print = false;
			foreach(array_keys($that->getForeignDataStructures()) as $column_name){
				if($that->hasForeignDataStructure($column_name)){
					if($this->hasColumn($column_name)){
						$column = $this->getColumn($column_name);
					}else{
						$column = null;
					}
					if(
						$column instanceof KeyListDatum || (
							!$that->hasColumn($column_name) &&
							$that->hasForeignDataStructureList($column_name)
						)
					){
						if($that->hasForeignDataStructureList($column_name)){
							$structs = $that->getForeignDataStructureList($column_name);
							foreach($structs as $struct){
								$this->setForeignDataStructureListMember($column_name, $struct);
							}
						}else{
							if($print){
								Debug::print("{$f} no foreign data structure list \"{$column_name}\"");
							}
							continue;
						}
					}elseif(
						$column instanceof ForeignKeyDatum || (
							!$that->hasColumn($column_name) &&
							$that->hasForeignDataStructure($column_name)
						)
					){
						$this->setForeignDataStructure($column_name, $that->getForeignDataStructure($column_name));
					}else{
						$dc = $column->getClass();
						Debug::error("{$f} datum is an instance of \"{$dc}\"");
					}
				}
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function getForeignDataStructureListCommand(string $phylum): GetForeignDataStructureListCommand{
		return new GetForeignDataStructureListCommand($this, $phylum);
	}
	
	/**
	 * returns distance from this object along a chain of objects linked by foreign key reference in column $column_name until key $value is reached;
	 * negative values are returned if there is no association
	 *
	 * @param string $column_name
	 * @param mixed $value
	 * @return int
	 */
	public function getAssociationDistance(string $column_name, $value): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($this->getIdentifierValue() === $value){
				return 0;
			}elseif(!$this->hasColumn($column_name) || ! $this->hasColumnValue($column_name)){
				if($print){
					Debug::print("{$f} column \"{$column_name}\" does not exist, or it has a different value");
				}
				return - 1;
			}
			$column = $this->getColumn($column_name);
			if(!$column->applyFilter(COLUMN_FILTER_FOREIGN)){
				Debug::error("{$f} column \"{$column_name}\" is not a foreign key");
			}elseif($value === $column->getValue()){
				return 1;
			}
			$fds = $this->getForeignDataStructure($column_name);
			$distance = $fds->getAssociationDistance($column_name, $value);
			if($distance < 0){
				return $distance;
			}
			return $distance + 1;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 *
	 * @param mysqli $mysqli
	 * @param string|string[] ...$column_names
	 * @return int
	 */
	public static function migrateMonomorphicToPoly(mysqli $mysqli, ...$column_names): int{
		$f = __METHOD__;
		try{
			$print = false;
			if(empty($column_names)){
				Debug::error("{$f} column names are empty");
				return FAILURE;
			}
			$that = new static();
			foreach($column_names as $column_name){
				$column = $that->getColumn($column_name);
				if(!$column instanceof ForeignKeyDatum){
					Debug::error("{$f} column \'{$column_name}\" is not a ForeignKeyDatum");
					continue;
				}elseif(!$column->applyFilter(COLUMN_FILTER_INTERSECTION)){
					Debug::error("{$f} column \"{$column_name}\" is not set up to be polymorphic, make it so before calling this function on it");
				}
				// 1. create intersection tables if they don't already exist
				$status = $column->createIntersectionTables($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} createIntersectionTables for column \"{$column_name}\" returned error status \"{$err}\"");
					return $status;
				}
			}
			// 2. load all instances of this class, update all keys
			$select = $that->select();
			$select->pushExpressions(...$column_names);
			$result = $select->executeGetResult($mysqli);
			$results = $result->fetch_all(MYSQLI_ASSOC);
			$result->free_result();
			foreach($results as $result){
				$obj = new static();
				$obj->getColumn($column_name)->setRetainOriginalValueFlag(false);
				$status = $obj->processQueryResultArray($mysqli, $result);
				$key = $obj->getIdentifierValue();
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} processQueryResultArray for object with identifier \"{$key}\" returned error status \"{$err}\"");
				}
				// 3. set foreign data structures for each column
				foreach($column_names as $column_name){
					if(!$obj->hasColumnValue($column_name)){
						Debug::error("{$f} object with identifier \"{$key}\" does not have a value for column \"{$column_name}\", cannot migrate");
						continue;
					}
					$fk = $obj->getColumnValue($column_name);
					if(!registry()->has($fk)){
						Debug::warning("{$f} registry does not have n object with identifier \"{$fk}\"");
						continue;
					}
					$struct = registry()->get($fk);
					$obj->setForeignDataStructure($column_name, $struct);
					$column = $obj->getColumn($column_name);
					if(!$column->hasValue()){
						Debug::error("{$f} after setting foreign data structure, column \"{$column_name}\" lacks an actual value");
					}
					$intersection = $column->generateIntersectionData();
					if(!$intersection->hasForeignKey()){
						Debug::error("{$f} intersection data lacks a foreign key");
					}elseif(!$intersection->hasHostKey()){
						Debug::error("{$f} intersection data lacks a host key");
					}elseif(!$intersection->hasRelationship()){
						Debug::error("{$f} intersection data lacks a relationship");
					}
					// see if the intersection data already exists
					$select = $intersection->select()->where(
						new AndCommand(
							new WhereCondition("hostKey", OPERATOR_EQUALS),
							new WhereCondition("relationship", OPERATOR_EQUALS)
							)
						)->withTypeSpecifier('ss')->withParameters([$key, $column_name]);
						deallocate($intersection);
						$count = $select->executeGetResultCount($mysqli);
						if($count === 0){
							if($print){
								Debug::print("{$f} marking object with key \"{$key}\" foreign column \"{$column_name}\" for update");
							}
							$column->setUpdateFlag(true);
							$obj->setUpdateFlag(true);
						}elseif($count === 1){
							if($print){
								Debug::print("{$f} intersection data already exists for object with key \"{$key}\" foreign column \"{$column_name}\"");
							}
							continue;
						}else{
							Debug::error("{$f} illegal intersection data count {$count} for object with key \"{$key}\" foreign column \"{$column_name}\"");
						}
				}
				// 4. update the object
				if(!$obj->getUpdateFlag()){
					if($print){
						Debug::print("{$f} object with ID \"{$key}\" is not flagged for update");
					}
					continue;
				}elseif($print){
					Debug::print("{$f} about to update object with key \"{$key}\"");
				}
				$status = $obj->update($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} updating object with ID \"{$key}\" returned error status \"{$err}\"");
					return $status;
				}elseif($print){
					Debug::print("{$f} successfully updated object with ID \"{$key}\"");
				}
			}
			if($print){
				Debug::print("{$f} migration successful");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function getForeignDataStructureCountCommand(string $column_name): GetForeignDataStructureCountCommand{
		return new GetForeignDataStructureCountCommand($this, $column_name);
	}
	
	public function unshiftForeignDataStructureListMember(string $column_name, DataStructure $struct){
		$f = __METHOD__;
		$print = false;
		if(!$this->hasForeignDataStructureList($column_name)){
			if($print){
				Debug::print("{$f} no foreign data struture list for column \"{$column_name}\"");
			}
			$this->setForeignDataStructureListMember($column_name, $struct);
			return SUCCESS;
		}elseif($print){
			Debug::print("{$f} about to unshift a foreign data structure to the beginning of list \"{$column_name}\"");
		}
		$backup = $this->getForeignDataStructureList($column_name);
		$temp = [
			$struct->getIdentifierValue() => $struct
		];
		$this->setForeignDataStructureList($column_name, array_merge($temp, $backup));
		unset($backup);
		return SUCCESS;
	}
	
	public function getFirstRelationship(string $tree_name): ?DataStructure{
		return $this->getForeignDataStructureListMemberAtOffset($tree_name, 0);
	}
	
	public function getDescendants(string $phylum): array{
		$f = __METHOD__;
		try{
			$print = false;
			if(!$this->hasForeignDataStructureList($phylum)){
				if($print){
					Debug::print("{$f} child count is 0");
				}
				return [];
			}elseif($print){
				Debug::print("{$f} entered; about to get child array \"{$phylum}\"");
			}
			$children = $this->getForeignDataStructureList($phylum);
			$descendants = [];
			foreach($children as $child){
				$descendants[$child->getIdentifierValue()] = $child;
				if(!$child->hasForeignDataStructureList($phylum)){
					if($print){
						Debug::print("{$f} child does not have any children in phylum \"{$phylum}\"");
					}
					continue;
				}elseif($print){
					$key = $child->getIdentifierValue();
					Debug::print("{$f} calling this recursively on a child with key \"{$key}\" node for phylum \"{$phylum}\"");
				}
				$grandchildren = $child->getDescendants($phylum);
				$descendants = array_merge($descendants, $grandchildren);
			}
			return $descendants;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	protected function reportAmbiguousRelationship(string $column_name):?string{
		$sc = $this->getShortClass();
		$ds = $this->getDebugString();
		if(!$this->hasColumn($column_name)){
			return "{$sc} does not have a column \"{$column_name}\"";
		}
		$column = $this->getColumn($column_name);
		if($column->getRank() === RANK_PARENT){
			return null;
		}elseif($column instanceof ForeignKeyDatum){
			if(!$this->hasForeignDataStructure($column_name)){
				return "{$ds} does not have a foreign data structure \"{$column_name}\"";
			}
			$fds = $this->getForeignDataStructure($column_name);
		}elseif($column instanceof KeyListDatum){
			if($this->hasForeignDataStructureList($column_name)){
				return "{$ds} does not have a foreign data structure list \"{$column_name}\"";
			}
			$fds = $this->getForeignDataStructureListMemberAtOffset($column_name, 0);
		}else{
			return "{$sc}'s column \"{$column_name}\" is neither a ForeignKeyDatum nor KeyListDatum";
		}
		if(!$column->hasConverseRelationshipKeyName()){
			return "{$sc}'s column \"{$column_name}\" does not know its converse relationship key name";
		}
		$converse = $column->getConverseRelationshipKeyName();
		if(!$fds->hasColumn($converse)){
			return "{$ds}'s {$column_name} ".$fds->getDebugString()." does not have a column \"{$converse}\"";
		}
		$fsc = $fds->getShortClass();
		$fc = $fds->getColumn($converse);
		if($column->getRank() === RANK_PARENT || $fc->getRank() === RANK_PARENT){
			if($column->getRank() === RANK_PARENT && $fc->getRank() === RANK_PARENT){
				return "Both this {$ds}'s {$column_name} and its converse {$fsc}'s {$converse} are flagged as parent keys";
			}
			return null;
		}
		return "Neither this {$ds}'s {$column_name} nor its converse {$fsc}'s {$converse} are flagged as parent keys";
	}
	
	public function releaseForeignDataStructureList(string $column_name, bool $recursive=false){
		$f = __METHOD__;
		if(!$this->hasForeignDataStructureList($column_name)){
			Debug::error("{$f} foreign data structure list \'{$column_name}\" is undefined");
		}
		foreach(array_keys($this->getForeignDataStructureList($column_name)) as $key){
			$this->releaseForeignDataStructureListMember($column_name, $key, $recursive);
		}
	}
	
	public function releaseAllForeignDataStructures(bool $recursive=false):void{
		$f = __METHOD__;
		$print = false;
		if($print){
			$ds = $this->getDebugString();
		}
		if($this->hasAnyEventListener(EVENT_BEFORE_RELEASE_ALL_FOREIGN)){
			$this->dispatchEvent(new BeforeReleaseAllForeignDataStructuresEvent($recursive));
		}
		if($this->hasForeignDataStructures()){
			foreach($this->getForeignDataStructures() as $column_name => $fds){
				if(!$this instanceof EmbeddedData){
					$error = $this->reportAmbiguousRelationship($column_name);
					if($error){
						debug()->log($error);
					}
				}
				if(!BACKWARDS_REFERENCES_ENABLED){
					if($this->hasColumn($column_name)){
						if($this->getColumn($column_name)->getRank() === RANK_PARENT){
							if($print){
								Debug::print("{$f} this {$ds}'s column \"{$column_name}\" has the parent key flag set");
							}
							continue;
						}elseif($print){
							Debug::print("{$f} this {$ds}'s column \"{$column_name}\" is not a parent key");
						}
					}elseif($print){
						Debug::print("{$f} this {$ds} does not have a column \"{$column_name}\"");
					}
				}elseif($print){
					Debug::print("{$f} backwards references are enabled");
				}
				if(is_array($fds)){
					$this->releaseForeignDataStructureList($column_name, $recursive);
				}else{
					$this->releaseForeignDataStructure($column_name, $recursive);
				}
			}
		}elseif($print){
			Debug::print("{$f} no foreign data structures to release for this {$ds}");
		}
		if($this->hasAnyEventListener(EVENT_AFTER_RELEASE_ALL_FOREIGN)){
			$this->dispatchEvent(new AfterReleaseAllForeignDataStructuresEvent($recursive));
		}
	}
	
	public function canReleaseForeignDataStructure(string $column_name):bool{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(!$this->hasForeignDataStructure($column_name)){
			if($print){
				Debug::print("{$f} we do not have a foreign data structure called \"{$column_name}\", returning false");
			}
			return false;
		}elseif(BACKWARDS_REFERENCES_ENABLED){
			if($print){
				Debug::print("{$f} backwards references are enabled, returning true");
			}
			return true;
		}elseif(!$this->hasColumn($column_name)){
			if($print){
				Debug::print("{$f} we don't have a column \"{$column_name}\", returning true");
			}
			return true;
		}elseif($this->getColumn($column_name)->getRank() === RANK_PARENT){
			if($print){
				Debug::print("{$f} column \"{$column_name}\" has the parent key flag set, returning false");
			}
			return false;
		}elseif($print){
			Debug::print("{$f} none of the above conditions satisfied, returning true");
		}
		return true;
	}
	
	public function canReleaseForeignDataStructureListMember(string $column_name, $key):bool{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if(!$this->hasForeignDataStructureListMember($column_name, $key)){
			if($print){
				Debug::print("{$f} we don't have a foreign data structure list \"{$column_name}\" member with key \"{$key}\", returning false");
			}
			return false;
		}elseif(BACKWARDS_REFERENCES_ENABLED){
			if($print){
				Debug::print("{$f} backwards references are enabled, returning true");
			}
			return true;
		}elseif(!$this->hasColumn($column_name)){
			if($print){
				Debug::print("{$f} we don't have a column \"{$column_name}\", returning true");
			}
			return true;
		}elseif($this->getColumn($column_name)->getRank() === RANK_PARENT){
			if($print){
				Debug::print("{$f} column \"{$column_name}\" has its parentKey flag set, returning false");
			}
			return false;
		}elseif($print){
			Debug::print("{$f} none of the above conditions satisfied, returning true");
		}
		return true;
	}
	
	public function getPostInsertForeignDataStructuresFlag(){
		return $this->getFlag(DIRECTIVE_POSTINSERT_FOREIGN);
	}
	
	public function setPostInsertForeignDataStructuresFlag(bool $value = true): bool{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::printStackTraceNoExit("{$f} entered");
		}
		return $this->setFlag(DIRECTIVE_POSTINSERT_FOREIGN, $value);
	}
	
	public function trimForeignDataStructure(int $recursion_depth=1):int{
		$f = __METHOD__;
		if(!$this->hasForeignDataStructures()){
			Debug::error("{$f} no foreign data structures to trim");
		}
		foreach($this->getForeignDataStructures() as /*$key =>*/ $value){
			if(is_array($value)){
				foreach($value as $fds){
					if($fds->getTrimmedFlag()){
						continue;
					}
					$fds->trimUnusedColumns(true, $recursion_depth-1);
				}
			}else{
				if($value->getTrimmedFlag()){
					continue;
				}
				$value->trimUnusedColumns(true, $recursion_depth-1);
			}
		}
		return SUCCESS;
	}
	
	public function setDeleteForeignDataStructuresFlag(bool $value=true):bool{
		return $this->setFlag(DIRECTIVE_DELETE_FOREIGN, $value);
	}
	
	public function getDeleteForeignDataStructuresFlag():bool{
		return $this->getFlag(DIRECTIVE_DELETE_FOREIGN);
	}
	
	protected function beforeDeleteForeignDataStructuresHook(mysqli $mysqli): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_DELETE_FOREIGN)){
			$this->dispatchEvent(new BeforeDeleteForeignDataStructuresEvent());
		}
		return SUCCESS;
	}
	
	/**
	 * deletes foreign data structures that are flagged for deletion
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function deleteForeignDataStructures(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			$status = $this->permit(user(), DIRECTIVE_DELETE_FOREIGN);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} permission returner error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$status = $this->beforeDeleteForeignDataStructuresHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeDeleteForeignDataSttructuresHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$columns = $this->getFilteredColumns(COLUMN_FILTER_FOREIGN);
			$delete_us = [];
			foreach($columns as $column_name => $column){
				$column = $this->getColumn($column_name);
				if($column instanceof KeyListDatum){
					if($this->hasForeignDataStructureList($column_name)){
						foreach($this->getForeignDataStructureList($column_name) as $key => $fds){
							if($fds->getDeleteFlag()){
								if($print){
									$fdsc = $fds->getClass();
									Debug::print("{$f} {$fdsc} with key \"{$key}\" is flagged for deletion");
								}
								array_push($delete_us, $fds);
							}
						}
					}elseif($print){
						Debug::print("{$f} no foreign data structure list \"{$column_name}\"");
					}
				}elseif($column instanceof ForeignKeyDatum){
					if(!$this->hasForeignDataStructure($column_name)){
						continue;
					}
					$struct = $this->getForeignDataStructure($column_name);
					if(!$struct->getDeleteFlag()){
						if($print){
							Debug::print("{$f} foreign data structure at column \"{$column_name}\" is NOT flagged for deletion");
						}
						continue;
					}elseif($print){
						Debug::print("{$f} foreign data structure at column \"{$column_name}\" IS flagged for deletion");
						if($column_name === "userKey"){
							$fdsk = $this->getColumnValue($column_name);
							Debug::error("{$f} attempting to delete user -- FDS key is \"{$fdsk}\"");
						}
					}
					$this->setColumnValue($column_name, null);
					array_push($delete_us, $struct);
				}else{
					Debug::error("{$f} neither of the above for column \"{$column_name}\"");
				}
			}
			if(!empty($delete_us)){
				foreach($delete_us as $struct){
					$status = $struct->delete($mysqli);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} deleting object returned error status \"{$err}\"");
					}
				}
			}elseif($print){
				Debug::print("{$f} nothing to delete");
			}
			$status = $this->afterDeleteForeignDataStructuresHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} afterDeleteForeignDataSttructuresHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			if($print){
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	protected function afterDeleteForeignDataStructuresHook(mysqli $mysqli): int{
		if($this->hasAnyEventListener(EVENT_AFTER_DELETE_FOREIGN)){
			$this->dispatchEvent(new AfterDeleteForeignDataStructuresEvent());
		}
		return SUCCESS;
	}
	
	protected function beforeInsertForeignDataStructuresHook(mysqli $mysqli, string $when): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_INSERT_FOREIGN)){
			$this->dispatchEvent(new BeforeInsertForeignDataStructuresEvent($when));
		}
		return SUCCESS;
	}
	
	/**
	 * inserts foreign data structures that are flagged for insertion
	 *
	 * @param mysqli $mysqli
	 * @param string $when
	 *        	: "before" or "after" -- specify whether the data structures being inserted are those that must be inserted before this object is inserted (b/c this object has a constrained foreign key that references them) or afterward (b/c the foreign data structures have constrained foreign keys that reference this object, or if it doesn't matter)
	 * @return int
	 */
	public function insertForeignDataStructures(mysqli $mysqli, string $when): int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			switch($when){
				case CONST_BEFORE:
					if($print){
						Debug::print("{$f} inserting foreign data structures that come before this object");
					}
					$columns = $this->getFilteredColumns(CONST_BEFORE);
					break;
				case CONST_AFTER:
					if($print){
						Debug::print("{$f} inserting foreign data structures that come after this object");
					}
					$columns = $this->getFilteredColumns(CONST_AFTER);
					break;
				default:
					Debug::error("{$f} invalid relative insertion sequence \"{$when}\"");
			}
			$status = $this->beforeInsertForeignDataStructuresHook($mysqli, $when);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeInsertForeignDataStructuresHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} beforeInsertForeignDataStructureHook returned success");
			}
			if(empty($columns)){
				if($print){
					Debug::print("{$f} foreign data structure indices array is empty");
				}
				return SUCCESS;
			}
			$structs = [];
			$dupes = [];
			$old_structs = [];
			foreach($columns as $column_name => $column){
				if($print){
					Debug::print("{$f} about to evaluate column \"{$column_name}\"");
				}
				if($column instanceof KeyListDatum){
					$multiple = true;
				}else{
					$multiple = false;
				}
				if($multiple && $this->hasForeignDataStructureList($column_name)){
					$list = $this->getForeignDataStructureList($column_name);
					if(empty($list)){
						Debug::error("{$f} list \"{$column_name}\" returned empty");
					}
					foreach($list as $struct_key => $struct){
						if($struct->getInsertFlag()){
							if($print){
								Debug::print("{$f} insert flag is set on foreign data structure list member at column \"{$column_name}\"");
							}
							if(!$struct->getDeleteFlag()){
								if(!$struct->hasIdentifierValue() || !array_key_exists($struct->getIdentifierValue(), $dupes)){
									array_push($structs, $struct);
									if($struct->hasIdentifierValue()){
										$dupes[$struct->getIdentifierValue()] = $struct;
									}
								}elseif($print){
									Debug::print("{$f} struct has an identifier, and it has already been added to the insertion queue");
								}
							}elseif($print){
								Debug::print("{$f} {$column_name} member with key \"{$struct_key}\" is apoptotic before ever getting inserted");
							}
						}elseif($print){
							Debug::print("{$f} insert flag is not set for foreign data structure list \"{$column_name}\" member \"{$struct_key}\"");
						}
					}
					if($this->hasOldDataStructureList($column_name)){
						foreach($this->getOldDataStructureList($column_name) as $old){
							if($old->getDeleteFlag()){
								$old_structs[$old->getIdentifierValue()] = $old;
							}elseif($print){
								Debug::print("{$f} deletion flag is not set");
							}
						}
					}
				}elseif($this->hasForeignDataStructure($column_name)){
					if($print){
						Debug::print("{$f} yes, this object has a foreign data structure at column \"{$column_name}\"");
					}
					$struct = $this->getForeignDataStructure($column_name);
					if(!$struct->getInsertFlag() || $struct->getDeleteFlag()){
						if($print){
							Debug::print("{$f} structure \"{$column_name}\" does not have its insert flag set");
						}
						continue;
					}
					if(!$struct->hasIdentifierValue() || !array_key_exists($struct->getIdentifierValue(), $dupes)){
						array_push($structs, $struct);
						if($struct->hasIdentifierValue()){
							$dupes[$struct->getIdentifierValue()] = $struct;
						}
					}elseif($print){
						Debug::print("{$f} struct has an identifier, and it has already been added to the insertion queue");
					}
					// array_push($structs, $struct);
					if($print){
						Debug::print("{$f} structure at column \"{$column_name}\" is ready to insert");
					}
					if($this->hasOldDataStructure($column_name)){
						$old_struct = $this->getOldDataStructure($column_name);
						if($old_struct->getDeleteFlag()){
							if($print){
								Debug::print("{$f} object has an old structure at column \"{$column_name}\" -- about to delete it");
							}
							array_push($old_structs, $old_struct);
						}elseif($print){
							Debug::print("{$f} old structure at column \"{$column_name}\" does not have its delete flag set");
						}
					}elseif($print){
						Debug::print("{$f} this object does not have an old foreign data structure for column \"{$column_name}\"");
					}
				}else{
					if($print){
						Debug::print("{$f} data structure with column {$column_name} is undefined");
					}
					continue;
				}
			}
			if(empty($structs)){
				Debug::error("{$f} there are no foreign data structures to insert");
			}
			foreach($structs as $struct_num => $struct){
				$pretty = $struct->getClass();
				if($print){
					$key = $struct->hasIdentifierValue() ? $struct->getIdentifierValue() : "[undefined]";
					Debug::print("{$f} about to insert {$pretty} with ID \"{$key}\" at position {$struct_num}");
				}
				if(!$this->getBlockInsertionFlag()){
					$status = $struct->insert($mysqli);
				}else{
					if($print){
						Debug::print("{$f} block insertion flag is set");
					}
					$status = SUCCESS;
				}
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} writing {$pretty} to database returned error message \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("{$f} successfully wrote new foreign data structure at column \"{$column_name}\"");
				}
			}
			if(!empty($old_structs)){
				if($print){
					Debug::print("{$f} there are old structures to delete");
				}
				foreach($old_structs as $old_struct){
					if(!$this->getBlockInsertionFlag()){
						$status = $old_struct->delete($mysqli);
					}else{
						if($print){
							Debug::print("{$f} block insertion flag is set");
						}
						$status = SUCCESS;
					}
					switch($status){
						// case STATUS_DELETED:
						case SUCCESS:
							if($print){
								Debug::print("{$f} successfully deleted old structure at column \"{$column_name}\"");
							}
							continue 2;
						case RESULT_DELETE_FAILED_IN_USE:
							if($print){
								Debug::print("{$f} did not delete old structure at column \"{$column_name}\", it's still in use");
							}
							continue 2;
						default:
							$err = ErrorMessage::getResultMessage($status);
							$osk = $old_struct->getIdentifierValue();
							$osc = $old_struct->getClass();
							Debug::error("{$f} deleting old structure of class \"{$osc}\" with identifier \"{$osk}\" returned error status \"{$err}\"");
							return $this->setObjectStatus($status);
					}
				}
			}elseif($print){
				Debug::print("{$f} no old data structures to delete");
			}
			$status = $this->afterInsertForeignDataStructuresHook($mysqli, $when);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} afterInsertForeignDataStructuresHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} afterInsertForeignDataStructureHook returned success");
			}
			if($print){
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	protected function afterInsertForeignDataStructuresHook(mysqli $mysqli, string $when): int{
		if($this->hasAnyEventListener(EVENT_AFTER_INSERT_FOREIGN)){
			$this->dispatchEvent(new AfterInsertForeignDataStructuresEvent($when));
		}
		return SUCCESS;
	}
	
	public function setPreInsertForeignDataStructuresFlag(bool $value = true): bool{
		$f = __METHOD__;
		$print = false;
		if($print){
			$did = $this->getDebugId();
			Debug::printStackTraceNoExit("{$f} entered. Debug Id id \"{$did}\"");
		}
		return $this->setFlag(DIRECTIVE_PREINSERT_FOREIGN, $value);
	}
	
	public function getPreInsertForeignDataStructuresFlag(): bool{
		return $this->getFlag(DIRECTIVE_PREINSERT_FOREIGN);
	}
	
	public function debugPrintForeignDataStructures(){
		$f = __METHOD__;
		foreach($this->getForeignDataStructuresArray() as $key => $value){
			if(is_array($value)){
				foreach(array_keys($value) as $subvalue){
					Debug::print("{$f} index \"{$key}\", ID \"{$subvalue}\"");
				}
			}else{
				Debug::print("{$f} index \"{$key}\"");
			}
		}
		Debug::printStackTrace();
	}
	
	public function setDeleteOldDataStructuresFlag(bool $value = true): bool{
		return $this->setFlag("deleteOld", $value);
	}
	
	public function getDeleteOldDataStructuresFlag(): bool{
		return $this->getFlag("deleteOld");
	}
	
	public function setAutoloadFlags(bool $value = true, ?array $column_names = null): bool{
		$f = __METHOD__;
		$print = false;
		if($column_names === null){
			$filter2 = COLUMN_FILTER_AUTOLOAD;
			if($value){
				$filter2 = "!{$filter2}";
			}
			$column_names = $this->getFilteredColumnNames(COLUMN_FILTER_FOREIGN, "!" . COLUMN_FILTER_VOLATILE, $filter2);
		}
		foreach($column_names as $name){
			$c = $this->getColumn($name);
			if(!$value || $c->hasForeignDataStructureClass() || $c->hasForeignDataStructureClassResolver()){
				if($print){
					if($value){
						Debug::print("{$f} flagging column \"{$name}\" for autoload");
					}else{
						Debug::print("{$f} flagging column \"{$name}\" for autoload disabled");
					}
				}
				$c->setAutoloadFlag($value);
			}elseif($print){
				Debug::print("{$f} datum \"{$name}\" does not have a foreign data structure class and thus cannot be autoloaded");
			}
		}
		return $value;
	}
	
	public function beforeExpandHook(mysqli $mysqli): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_EXPAND)){
			$this->dispatchEvent(new BeforeExpandEvent());
		}
		return SUCCESS;
	}
	
	public function afterExpandHook(mysqli $mysqli): int{
		if($this->hasAnyEventListener(EVENT_AFTER_EXPAND)){
			$this->dispatchEvent(new AfterExpandEvent());
		}
		return SUCCESS;
	}
	
	public function setExpandedFlag(bool $value = true): bool{
		return $this->setFlag('expanded', $value);
	}
	
	public function getExpandedFlag(): bool{
		return $this->getFlag('expanded');
	}
	
	protected function flagForeignDataStructuresForRecursiveDeletion(mysqli $mysqli):int{
		$f = __METHOD__;
		try{
			$print = false;
			// flag foreign data structures for recursive deletion, or contract vertices
			foreach($this->getFilteredColumns(COLUMN_FILTER_FOREIGN) as $column_name => $column){
				if($column->getRecursiveDeleteFlag()){
					if($print){
						Debug::print("{$f} column \"{$column_name}\" is flagged for recursive deletion");
					}
					if($column instanceof ForeignKeyDatum){
						$this->getForeignDataStructure($column_name)->setDeleteFlag(true);
					}elseif($column instanceof KeyListDatum){
						if(!$this->hasForeignDataStructureList($column_name)){
							continue;
						}
						foreach($this->getForeignDataStructureList($column_name) as $member){
							$member->setDeleteFlag(true);
						}
					}
					$this->setDeleteForeignDataStructuresFlag(true);
				}elseif($column->getContractVertexFlag()){
					if($print){
						Debug::print("{$f} column \"{$column_name}\" has its contractVertex flag set");
					}
					$status = $column->contractVertex($mysqli);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} contractVertex returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}
				}elseif($print){
					Debug::print("{$f} column \"{$column_name}\" does not have either of its recursiveDelete or contractVertex flag set");
				}
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * insert data stored in intersection tables
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function insertIntersectionData(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false;
			if(!$this->getAllocatedFlag()){
				Debug::error("{$f} allocated flag is not set for this ".$this->getDebugString());
			}elseif($print){
				Debug::print("{$f} entered for a(n) ".$this->getShortClass()." with key ".$this->getIdentifierValue());
			}
			$polys = $this->getFilteredColumns(COLUMN_FILTER_INTERSECTION, COLUMN_FILTER_VALUED, "!".COLUMN_FILTER_ONE_SIDED);
			if(!empty($polys)){
				foreach($polys as $name => $poly){
					if($print){
						if($poly instanceof ForeignKeyDatum){
							$key = $poly->getValue();
							Debug::print("{$f} about to insert intersection data for column \"{$name}\" which has key \"{$key}\"");
							if(strlen($key) == 0){
								$decl = $poly->getDeclarationLine();
								Debug::error("{$f} zero length foreign key. Instantiated {$decl}");
							}
						}elseif($poly instanceof KeyListDatum){
							Debug::print("{$f} about to insert intersection data for column \"{$name}\"");
						}else{
							$class = $poly->getClass();
							Debug::error("{$f} unsupported class \"{$class}\"");
						}
					}
					if($poly instanceof KeyListDatum){
						$vc = $poly->getValueCount();
						$fdsc = $this->getForeignDataStructureCount($name);
						if($vc !== $fdsc){
							Debug::warning("{$f} key list column value count {$vc} does not equal foreign data structure count {$fdsc} for relation \"{$name}\"");
							Debug::printArray($poly->getValue());
							Debug::error("{$f} polymorphic foreign KeyListDatum is ".$poly->getDebugString()."; this is ".$this->getDebugString());
						}
					}
					if(!$this->getBlockInsertionFlag()){
						if(!$poly->getAllocatedFlag()){
							Debug::error("{$f} allocated flag is not set for column {$name} ".$poly->getDebugString()." of this ".$this->getDebugString());
						}elseif(!$poly->hasDataStructure()){
							Debug::error("{$f} ".$poly->getDebugString()." lacks a data structure");
						}
						$status = $poly->insertIntersectionData($mysqli);
					}else{
						if($print){
							Debug::print("{$f} block insertion flag is set");
						}
						$status = SUCCESS;
					}
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} insertIntersectionData for column \"{$name}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print){
						Debug::print("{$f} successfully inserted intersection data for column \"{$name}\"");
					}
				}
			}elseif($print){
				Debug::print("{$f} no polymorphic keys with actual values");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
