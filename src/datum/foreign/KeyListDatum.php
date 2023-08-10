<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum\foreign;

use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionData;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadedFlagTrait;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\json\JsonDatum;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use mysqli;

class KeyListDatum extends JsonDatum implements ForeignKeyDatumInterface
{

	use ForeignKeyDatumTrait;
	use LoadedFlagTrait;

	public function __construct(string $name, ?int $type = null)
	{
		parent::__construct($name);
		if ($type !== null) {
			$this->setRelationshipType($type);
		}
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			COLUMN_FILTER_ADD_TO_RESPONSE,
			COLUMN_FILTER_AUTOLOAD,
			COLUMN_FILTER_CONSTRAIN,
			COLUMN_FILTER_CONTRACT_VERTEX,
			COLUMN_FILTER_RECURSIVE_DELETE,
			COLUMN_FILTER_EAGER,
			COLUMN_FILTER_LOADED,
			COLUMN_FILTER_ONE_SIDED,
			COLUMN_FILTER_PREVENT_CIRCULAR_REF,
			COLUMN_FILTER_TEMPLATE
		]);
	}

	public function configureArrayMembership($value)
	{
		$f = __METHOD__;
		$print = false;
		$column_name = $this->getColumnName();
		if (is_bool($value)) {
			if ($print) {
				Debug::print("{$f} received a boolean value for column \"{$column_name}\"");
			}
			return parent::configureArrayMembership($value);
		} elseif (! is_array($value) && ! is_string($value)) {
			Debug::error("{$f} this function accepts bool, string and array");
		}
		parent::configureArrayMembership(true);
		$this->setAddToResponseFlag(true);
		$ds = $this->getDataStructure();
		if ($ds->hasForeignDataStructureList($column_name)) {
			foreach ($ds->getForeignDataStructureList($column_name) as $fds) {
				$fds->configureArrayMembership($value);
			}
		}
		if ($print) {
			Debug::print("{$f} returning the following:");
			Debug::print($value);
		}
		return $value;
	}

	public function getHumanReadableName()
	{
		if (! $this->hasHumanReadableName() && $this->hasForeignDataStructureClass()) {
			return $this->getForeignDataStructureClass()::getPrettyClassNames();
		}
		return parent::getHumanReadableName();
	}

	public function processInput($input): int
	{
		$f = __METHOD__;
		$print = false;
		$keyvalues = $input->getValueAttribute();
		if (! is_array($keyvalues)) {
			Debug::error("{$f} input->getValueAttribute() must return an array");
		}
		$column_name = $this->getColumnName();
		foreach ($keyvalues as $key => $value) {
			if (is_object($value)) {
				Debug::error("{$f} should not be setting objects as value attributes");
			}
			if ($print) {
				$did = $this->getDebugId();
				Debug::printStackTraceNoExit("{$f} setting {$column_name}[{$key}] = {$value} for column with debug ID \"{$did}\"");
			}
			$update = ! $this->inArray($value);
			$this->value[$key] = $value;
			if ($update && ! $this->getUpdateFlag()) {
				$this->setUpdateFlag(true);
			}
		}
		return SUCCESS;
	}

	public static function getDatabaseEncodedValueStatic($arr)
	{
		if (! empty($arr)) {
			foreach ($arr as $key => $value) {
				if (is_object($value)) {
					$arr[$key] = $value->toArray();
				}
			}
		}
		return parent::getDatabaseEncodedValueStatic($arr);
	}

	public function setRelationshipType(int $type): int
	{
		$f = __METHOD__;
		switch ($type) {
			case RELATIONSHIP_TYPE_ONE_TO_ONE:
			case RELATIONSHIP_TYPE_MANY_TO_ONE:
				Debug::error("{$f} X to one relationships only work with ForeignKeyDatum");
			case RELATIONSHIP_TYPE_ONE_TO_MANY:
			case RELATIONSHIP_TYPE_MANY_TO_MANY:
				break;
			default:
				Debug::error("{$f} invalid relationship type \"{$type}\"");
		}
		return $this->relationshipType = $type;
	}

	public function getPersistenceMode(): int
	{
		$p = parent::getPersistenceMode();
		switch ($p) {
			case PERSISTENCE_MODE_DATABASE:
				$type = $this->getRelationshipType();
				switch ($type) {
					case RELATIONSHIP_TYPE_ONE_TO_MANY:
					case RELATIONSHIP_TYPE_MANY_TO_MANY:
						return PERSISTENCE_MODE_INTERSECTION;
					default:
				}
			default:
				return $p;
		}
	}

	public function getRetainOriginalValueFlag(): bool
	{
		if ($this->getTemplateFlag()) {
			return true;
		}
		$pm = $this->getPersistenceMode();
		switch ($pm) {
			case PERSISTENCE_MODE_INTERSECTION:
				return true;
			default:
				return parent::getRetainOriginalValueFlag();
		}
	}

	public function getValueCount(): int
	{
		if (! $this->hasValue()) {
			return 0;
		}
		return count($this->value);
	}

	public function updateIntersectionTables(mysqli $mysqli): int
	{
		$f = __METHOD__;
		try {
			if ($this->getPersistenceMode() === PERSISTENCE_MODE_VOLATILE) {
				Debug::error("{$f} this should not get called on volatile relationships");
			}
			$originals = $this->hasOriginalValue() ? $this->getOriginalValue() : [];
			$ds = $this->getDataStructure();
			$name = $this->getColumnName();
			$print = false;
			if (! $this->hasValue()) {
				if ($print) {
					Debug::error("{$f} values is undefined");
				}
			}
			$values = $this->getValue();
			if ($print) {
				Debug::print("{$f} about to update intersection tables with the following values:");
				Debug::print($values);
				if (! $ds->hasForeignDataStructureList($name)) {
					Debug::print("{$f} host data structure does not have any list members for relationship \"{$name}\"");
				} else {
					$count = $ds->getForeignDataStructureCount($name);
					Debug::print("{$f} data structure has {$count} foreign structures for relationship \"{$name}\"");
				}
			}
			// insert intersection tables for all relationships that are not already in the database
			if (! empty($values)) {
				if ($print) {
					if (defined("DEBUG_IP_ADDRESS") && $_SERVER['REMOTE_ADDR'] === DEBUG_IP_ADDRESS) {
						$value_count = $this->getValueCount();
						$struct_count = $ds->getForeignDataStructureCount($name);
						if ($value_count !== $struct_count) {
							Debug::error("{$f} value count {$value_count} differs from structure count {$struct_count} for column \"{$name}\"");
						}
					}
				}
				foreach ($values as $key) {
					if (in_array($key, $originals, true)) {
						if ($print) {
							Debug::print("{$f} key \"{$key}\" is in the original values, no need to insert a new intersection");
						}
						continue;
					} elseif ($ds->hasForeignDataStructureListMember($name, $key)) {
						if ($print) {
							Debug::print("{$f} data structure has a new {$name} with key \"{$key}\"");
						}
						$fds = $ds->getForeignDataStructureListMember($name, $key);
					} elseif ($ds->getInsertedFlag()) {
						if ($print) {
							Debug::print("{$f} data structure was already inserted");
						}
						if (! registry()->has($key)) {
							Debug::error("{$f} registry does not know about an object with key \"{$key}\"");
						} elseif ($print) {
							Debug::print("{$f} host was inserted inserted; going to get the foreign data structure list member from registry");
						}
						$fds = registry()->get($key);
					} else {
						Debug::warning("{$f} host data structure lacks foreign data structure list \"{$name}\" member with key \"{$key}\", and it is was not just inserted");
						continue;
					}
					$dsc = $ds->getClass();
					$fdsc = $fds->getClass();
					if ($print) {
						Debug::print("{$f} about to insert intersection data for relationship \"{$name}\" between {$dsc} and {$fdsc}");
					}
					$intersection = new IntersectionData($dsc, $fdsc, $name);
					if ($this->getDataStructure()->hasIdentifierValue()) {
						$intersection->setHostKey($ds->getIdentifierValue());
					}
					if ($this->hasValue()) {
						$intersection->setForeignKey($key);
					}
					$status = $intersection->insert($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} inserting intersection data for foreign key \"{$key}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					} elseif ($print) {
						Debug::print("{$f} successfully inserted intersection data for foreign key \"{$key}\"");
					}
				}
			}
			// return early is this is happening during an insert
			if ($ds->getInsertingFlag()) {
				if ($print) {
					Debug::print("{$f} host data structure is being inserted; returning early");
				}
				return SUCCESS;
			}
			// delete intersection tables for all relationships that are only part of the original values
			if (! empty($originals)) {
				$delete_us = array_diff($originals, $values);
				if (! empty($delete_us)) {
					$intersections = $this->getAllPossibleIntersectionData();
					foreach ($intersections as $intersection) {
						$db = $intersection->getDatabaseName();
						$table = $intersection->getTableName();
						$where = new WhereCondition("foreignKey", OPERATOR_IN);
						$query = QueryBuilder::delete()->from($db, $table)
							->where(new AndCommand(new WhereCondition("hostKey", OPERATOR_EQUALS), new WhereCondition("relationship", OPERATOR_EQUALS), $where))
							->withTypeSpecifier(str_pad('sss', 2 + count($delete_us), $this->getTypeSpecifier()))
							->withParameters([
							$ds->getIdentifierValue(),
							$name,
							...array_values($delete_us)
						]);
						$where->setParameterCount($query->getParameterCount() - 2);
						if ($print) {
							Debug::print("{$f} deletion query from table \"{$table}\" is \"{$query}\" with the following parameters:");
							Debug::printArray($query->getParameters());
						}
						$status = $query->executeGetStatus($mysqli);
						if ($status !== SUCCESS) {
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} executing deletion query \"{$query}\" returned error status \"{$err}\"");
							return $this->setObjectStatus($status);
						} elseif ($print) {
							Debug::print("{$f} successfully executed deletion query \"{$query}\"");
						}
					}
				}
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function insertIntersectionData(mysqli $mysqli): int
	{
		return $this->updateIntersectionTables($mysqli);
	}

	/**
	 * If true, this relationship's intersection data will not be inserted in DataStructure->insertIntersectionData
	 *
	 * @param bool $value
	 * @return bool
	 */
	public function setOneSidedFlag(bool $value = true): bool
	{
		return $this->setFlag(COLUMN_FILTER_ONE_SIDED, $value);
	}

	public function getOneSidedFlag(): bool
	{
		return $this->getFlag(COLUMN_FILTER_ONE_SIDED);
	}

	public function oneSided(bool $value = true): KeyListDatum
	{
		$this->setOneSidedFlag($value);
		return $this;
	}

	public function inArray($value): bool
	{
		return isset($this->value) && is_array($this->value) & in_array($value, $this->value, true);
	}

	public function pushValue(...$values): int
	{
		$f = __METHOD__;
		$print = false;
		if (count($values) === 1 && is_array($values[0])) {
			$values = $values[0];
		}
		if (count($values) === 0) {
			Debug::error("{$f} don't call this function without parameters");
		} elseif (! isset($this->value) || ! is_array($this->value)) {
			$this->value = [];
		}
		if ($print) {
			$column_name = $this->getColumnName();
			$did = $this->getDebugId();
			// Debug::printStackTraceNoExit("{$f} pushing the following values to datum with debug ID {$did}:");
			// Debug::printArray(array(...$values));
			Debug::print("{$f} column \"{$column_name}\" with debug ID \"{$did}\" before pushing values:");
			Debug::printArray($this->value);
		}
		$pushed = array_push($this->value, ...$values);
		if (! $this->hasValue()) {
			Debug::error("{$f} immediately after pushing values, hasValue returned false");
		} elseif ($print) {
			foreach ($values as $value) {
				if (! $this->inArray($value)) {
					Debug::warning("{$f} immedately after pushing values, value {$value} is not in the array. About to print values");
					Debug::printArray($this->value);
					Debug::printStackTrace();
				}
			}
			Debug::print("{$f} values after pushing {$pushed} new ones:");
			Debug::printArray($this->value);
		}
		$this->setUpdateFlag(true);
		return $pushed;
	}

	public final function pushValueFromQueryResult(...$values): int
	{
		$f = __METHOD__;
		try {
			$print = false;
			if (count($values) === 1 && is_array($values[0])) {
				$values = $values[0];
			}
			if ($this->getRetainOriginalValueFlag()) {
				if ($print) {
					Debug::print("{$f} retain original value flag is set");
				}
				$this->pushOriginalValue(...$values);
			} elseif ($print) {
				Debug::print("{$f} retain original value flag is not set");
			}
			if (! isset($this->value) || ! is_array($this->value)) {
				$this->value = [];
			}
			if ($print) {
				$did = $this->getDebugId();
				Debug::printStackTraceNoExit("{$f} pushing the following values to datum with debug ID {$did}:");
				Debug::printArray(array(
					...$values
				));
			}
			return array_push($this->value, ...$values);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function pushOriginalValue(...$values): int
	{
		$f = __METHOD__; //KeyListDatum::getShortClass()."(".static::getShortClass().")->pushOriginalValue()";
		if (count($values) === 1 && is_array($values[0])) {
			$values = $values[0];
		}
		if (! isset($this->originalValue) || ! is_array($this->originalValue)) {
			$this->originalValue = [];
		}
		return array_push($this->originalValue, ...$values);
	}
}
