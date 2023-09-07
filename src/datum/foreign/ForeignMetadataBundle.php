<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum\foreign;

use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\ElementBindableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\HumanReadableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructuralTrait;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\DataTypeDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\DatumBundle;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterSetForeignDataStructureEvent;
use Exception;
use mysqli;

/**
 * A foreign key that potentially references one of many parent tables.
 * In order to use foreign key constraints the value of these keys are stored in intersection tables.
 * The child table stores a type hint and subtype to automatically determine intersection table names.
 * These keys require an IntersectionTableResolver as its ForeignDataStructureClassResolver to function.
 * If the constraint flag is not set, there is no intersection table and the foreign key is stored
 * in the same table as the DataStructure that contains it.
 *
 * @author j
 */
class ForeignMetadataBundle extends DatumBundle implements ForeignKeyDatumInterface{

	use DataStructuralTrait;
	use ElementBindableTrait;
	use ForeignKeyDatumTrait;
	use HumanReadableNameTrait;

	protected $defaultDataType;

	protected $intersectionHostKeyName;

	protected $intersectionForeignKeyName;

	protected $validDataTypes;

	public function __construct($name, $ds){
		parent::__construct($name, $ds);
		$this->setDataStructure($ds);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			COLUMN_FILTER_ADD_TO_RESPONSE,
			COLUMN_FILTER_AUTOLOAD,
			COLUMN_FILTER_CONSTRAIN,
			COLUMN_FILTER_CONTRACT_VERTEX,
			COLUMN_FILTER_EAGER,
			COLUMN_FILTER_INTERSECTION,
			COLUMN_FILTER_RECURSIVE_DELETE
		]);
	}

	public function setHostKeyName($name){
		return $this->intersectionHostKeyName = $name;
	}

	public function hasIntersectionHostKeyName(){
		return isset($this->intersectionHostKeyName);
	}

	public function getIntersectionHostKeyName(){
		$f = __METHOD__;
		if (! $this->hasIntersectionHostKeyName()) {
			Debug::error("{$f} host key name is undefined");
		}
		return $this->intersectionHostKeyName;
	}

	public function setIntersectionForeignKeyName(?string $name):?string{
		return $this->intersectionForeignKeyName = $name;
	}

	public function hasIntersectionForeignKeyName():bool{
		return isset($this->intersectionForeignKeyName);
	}

	public function getIntersectionForeignKeyName():string{
		$f = __METHOD__;if (! $this->hasIntersectionForeignKeyName()) {
			Debug::error("{$f} foreign key name is undefined");
		}
		return $this->intersectionForeignKeyName;
	}

	public function hasDefaultDataType():bool{
		return isset($this->defaultDataType);
	}

	public function getDefaultDataType():string{
		if ($this->hasDefaultDataType()) {
			return $this->defaultDataType;
		}
		return DATATYPE_UNKNOWN;
	}

	public function setDefaultDataType(?string $type):?string{
		return $this->defaultDataType = $type;
	}

	public function setValidDataTypes(?array $types):?array{
		return $this->validDataTypes = $types;
	}

	public function hasValidDataTypes():bool{
		return isset($this->validDataTypes);
	}

	public function getValidDataTypes():array{
		$f = __METHOD__;
		if (! $this->hasValidDataTypes()) {
			Debug::error("{$f} valid datatypes undefined");
		}
		return $this->validDataTypes;
	}

	protected function generateForeignKeyDatum(){
		$f = __METHOD__;
		try {
			$name = $this->getName();
			$foreignKeyName = "{$name}Key";
			$foreign_key = new ForeignKeyDatum($foreignKeyName);
			if ($this->getConstraintFlag()) {
				$foreign_key->setConstraintFlag(true);
				if ($this->hasKeyParts()) {
					$foreign_key->setKeyParts($this->getKeyParts());
				}
			}
			if ($this->hasForeignDataStructureClassResolver()) {
				$fdscr = $this->getForeignDataStructureClassResolver();
				$foreign_key->setForeignDataStructureClassResolver($fdscr);
			} elseif ($this->hasForeignDataStructureClass()) {
				$fdsc = $this->getForeignDataStructureClass();
				$foreign_key->setForeignDataStructureClass($fdsc);
			}
			// XXX moved the thing at the bottom from up here
			if ($this->hasConverseRelationshipKeyName()) {
				$foreign_key->setConverseRelationshipKeyName($this->getConverseRelationshipKeyName());
			}
			if ($this->getAutoloadFlag()) {
				$foreign_key->setAutoloadFlag(true);
			}
			if ($this->isNullable()) {
				$foreign_key->setNullable(true);
				$foreign_key->setDefaultValue(null);
			}
			if ($this->hasUpdateBehavior()) {
				$foreign_key->setUpdateBehavior($this->getUpdateBehavior());
			}
			if ($this->hasRelationshipType()) {
				$foreign_key->setRelationshipType($this->getRelationshipType());
			}
			if ($this->hasElementClass()) {
				$foreign_key->setElementClass($this->getElementClass());
			}
			if ($this->hasEmbeddedName()) {
				$foreign_key->embed($this->getEmbeddedName());
			}
			if ($this->getConstraintFlag() && !$this->hasForeignDataStructureClass()) { // XXX testing: this used to not be conditional; moved it from line 131
				$foreign_key->setPersistenceMode(PERSISTENCE_MODE_INTERSECTION);
				$foreign_key->setRetainOriginalValueFlag(true);
			}
			if ($this->getRecursiveDeleteFlag()) {
				$foreign_key->setRecursiveDeleteFlag(true);
			}
			if ($this->hasTimeToLive()) {
				$foreign_key->setTimeToLive($this->getTimeToLive());
			}
			return $foreign_key;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function generateComponents(?DataStructure $ds = null): array{
		$f = __METHOD__;
		try {
			$name = $this->getName();
			$print = false;
			// foreign key
			$foreign_key = $this->generateForeignKeyDatum();
			$foreignKeyName = "{$name}Key";
			$components = [
				$foreign_key
			];
			// foreign data type hint
			// directly assign foreign data type for e.g. addresses
			$typehint_name = "{$name}DataType";

			$datatype = new DataTypeDatum($typehint_name);
			$datatype->setForeignKeyName($foreignKeyName);
			$datatype->setRetainOriginalValueFlag(true);
			if ($this->isNullable()) {
				$datatype->setNullable(true);
			}
			if ($this->hasDefaultDataType()) {
				$datatype->setDefaultValue($this->getDefaultDataType());
			}
			if ($this->hasValidDataTypes()) {
				$datatype->setValidEnumerationMap($this->getValidDataTypes());
			}
			$foreign_key->setForeignDataTypeName($typehint_name);
			if ($this->hasOnDelete()) {
				$foreign_key->setOnDelete($this->getOnDelete());
			}
			if ($this->hasOnUpdate()) {
				$foreign_key->setOnUpdate($this->getOnUpdate());
			}
			if ($this->hasForeignDataType()) {
				if ($print) {
					Debug::print("{$f} foreign data type is already defined and static");
				}
				$type = $this->getForeignDataType();
				$foreign_key->setForeignDataType($type);
				$foreign_key->setForeignDataTypeName($typehint_name);
				$datatype->volatilize(); // XXX deal with fallout from embedding
				$datatype->setValue($type);
				$datatype->setOriginalValue($type); // needed otherwise it will think the intersection table has changed even when it hasn't
			} else {
				if ($print) {
					Debug::print("{$f} dynamic foreign data type");
				}
				if ($this->hasEmbeddedName()) {
					$datatype->embed($this->getEmbeddedName());
				}
				if ($this->hasDataStructure()) {
					if ($print) {
						Debug::print("{$f} data structure is defined; adding a afterSetForeignDataStructure event listener to automatically set foreign datatype");
					}
					$ds = $this->getDataStructure();
					$closure = function ($event, $target) use ($ds, $foreignKeyName, $typehint_name, $f, $print) {
						$print = false;
						$columnName = $event->getProperty("columnName");
						if ($columnName !== $foreignKeyName) {
							if ($print) {
								Debug::print("{$f} column name \"{$columnName} is not \"{$foreignKeyName}\"; skipping datatype update");
							}
							return;
						} elseif ($print) {
							Debug::print("{$f} column name is \"{$columnName}\"; about to automatically update foreign datatype");
						}
						$struct = $event->getProperty("data");
						$type = $struct->getDataType();
						$class = $ds->getClass();
						if ($print) {
							Debug::print("About to call {$class}->setColumnValue('{$typehint_name}', '{$type}')");
						}
						$ds->setColumnValue($typehint_name, $type);
					};
					$ds->addEventListener(EVENT_AFTER_SET_FOREIGN, $closure);
				} elseif ($print) {
					Debug::print("{$f} data structure is undefined");
				}
			}

			array_push($components, $datatype);
			// subtype hint
			$subtype_name = "{$name}Subtype";
			$subtype = new StringEnumeratedDatum($subtype_name);
			$subtype->setRetainOriginalValueFlag(true);
			if ($this->isNullable()) {
				$subtype->setNullable(true);
			}
			// if($this->hasForeignDataSubtypeName()){
			// $foreign_key->setForeignDataSubtypeName($this->getForeignDataSubtypeName());
			// }else{
			$foreign_key->setForeignDataSubtypeName($subtype_name);
			// }
			if ($this->hasDataStructure()) {
				if ($print) {
					Debug::print("{$f} foreign data structure is defined; about to add afterSetForeignDataStructure event listener to update foreign object subtype");
				}
				$ds = $this->getDataStructure();
				$closure = function (AfterSetForeignDataStructureEvent $event, DataStructure $target) use ($foreignKeyName, $subtype_name, $f, $print) {
					$columnName = $event->getProperty("columnName");
					if ($columnName !== $foreignKeyName) {
						if ($print) {
							Debug::print("{$f} column name \"{$columnName}\" is not \"{$foreignKeyName}\", skipping event");
						}
						return;
					} elseif ($print) {
						Debug::print("{$f} column name is \"{$columnName}\"; about to set subtype");
					}
					$struct = $event->getProperty("data");
					if ($struct->hasColumnValue('subtype') || $struct instanceof StaticSubtypeInterface) {
						$subtype = $struct->getSubtype();
						if ($print) {
							$class = $target->getClass();
							Debug::print("About to call {$class}->setColumnValue('{$subtype_name}', '{$subtype}')");
						}
						$target->setColumnValue($subtype_name, $subtype);
					} elseif ($print) {
						Debug::print("{$f} object does not have a subtype");
					}
				};
				$ds->addEventListener(EVENT_AFTER_SET_FOREIGN, $closure);
			} elseif ($print) {
				Debug::print("{$f} foreign data structure is undefined, skipping event listener");
			}
			if ($this->hasEmbeddedName()) {
				$subtype->embed($this->getEmbeddedName());
			} elseif ($print) {
				Debug::print("{$f} this bundle does not embed itself");
			}
			array_push($components, $subtype);
			return $components;
		} catch (Exception $x) {
			x($x, $f);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->defaultDataType);
		unset($this->intersectionForeignKeyName);
		unset($this->intersectionHostKeyName);
		unset($this->validDataTypes);
	}

	public function updateIntersectionTables(mysqli $mysqli): int
	{
		ErrorMessage::unimplemented(f());
	}
}
