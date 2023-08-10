<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\partition;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\AbstractTableOptions;
use Exception;

class PartitionDefinition extends AbstractTableOptions
{

	/*
	 * partition_definition:
	 * PARTITION partition_name
	 * [VALUES
	 * {LESS THAN {(expr | value_list) | MAXVALUE}
	 * |
	 * IN (value_list)}]
	 * [[STORAGE] ENGINE [=] engine_name]
	 * [COMMENT [=] 'string' ]
	 * [DATA DIRECTORY [=] 'data_dir']
	 * [INDEX DIRECTORY [=] 'index_dir']
	 * [MAX_ROWS [=] max_number_of_rows]
	 * [MIN_ROWS [=] min_number_of_rows]
	 * [TABLESPACE [=] tablespace_name]
	 * [(subpartition_definition [, subpartition_definition] ...)]
	 */

	use PartitionedTrait;

	protected $partitionName;

	protected $_values;

	protected $valuesOperator;

	public function __construct($partitionName)
	{
		parent::__construct();
		$this->requirePropertyType("partitionDefinitions", PartitionDefinition::class);
		$this->setPartitionName($partitionName);
	}

	public function setPartitionName($partitionName)
	{
		return $this->partitionName = $partitionName;
	}

	public function hasPartitionName()
	{
		return isset($this->partitionName);
	}

	public function getPartitionName()
	{
		$f = __METHOD__; //PartitionDefinition::getShortClass()."(".static::getShortClass().")->getPartitionName()";
		if (! $this->hasPartitionName()) {
			Debug::error("{$f} partition name is undefined");
		}
		return $this->partitionName;
	}

	public function values($operator, $values)
	{
		$f = __METHOD__; //PartitionDefinition::getShortClass()."(".static::getShortClass().")->values()";
		if (! is_string($operator)) {
			Debug::error("{$f} operator is not a string");
		}
		switch ($operator) {
			case OPERATOR_LESSTHAN:
				$operator = OPERATOR_LESSTHAN_STRING;
			case OPERATOR_LESSTHAN_STRING:
				if (is_string($values)) {
					$values = strtolower($values);
					if ($values === "maxvalue") {
						break;
					}
					Debug::error("{$f} invalid values expression \"{$values}\"");
				} elseif ($values instanceof ExpressionCommand) {
					break;
				}
			case OPERATOR_IN:
				if (! is_array($values)) {
					Debug::error("{$f} values is not an array");
				}
				break;
			default:
				Debug::error("{$f} invalid operator \"{$operator}\"");
		}
		$this->valuesOperator = $operator;
		$this->_values = $values;
		return $this;
	}

	public function hasValuesExpression()
	{
		return isset($this->valuesOperator) && isset($this->_values);
	}

	public function hasValuesOperator()
	{
		return isset($this->valuesOperator);
	}

	public function getValuesOperator()
	{
		$f = __METHOD__; //PartitionDefinition::getShortClass()."(".static::getShortClass().")->getValuesOperator()";
		if (! $this->hasValuesOperator()) {
			Debug::error("{$f} values operator is undefined");
		}
		return $this->valuesOperator;
	}

	public function getValuesExpression()
	{
		$f = __METHOD__; //PartitionDefinition::getShortClass()."(".static::getShortClass().")->getValuesExpression()";
		$operator = $this->getValuesOperator();
		switch ($operator) {
			case OPERATOR_LESSTHAN_STRING:
				if (is_string($this->_values)) {
					return $this->_values;
				} elseif ($this->_values instanceof ExpressionCommand) {
					return "({$this->_values})";
				}
			case OPERATOR_IN:
				return "(" . implode(',', $this->_values) . ")";
			default:
				Debug::error("{$f} invalid operator \"{$operator}\"");
		}
	}

	public function toSQL(): string
	{
		$f = __METHOD__; //PartitionDefinition::getShortClass()."(".static::getShortClass().")->toSQL()";
		try {
			$string = "partition " . $this->getPartitionName();
			if ($this->hasValuesExpression()) {
				$expr = $this->getValuesExpression();
				if ($expr instanceof SQLInterface) {
					$expr = $expr->toSQL();
				}
				$string .= " values ".$this->getValuesOperator()." {$expr}";
			}
			if ($this->hasStorageEngineName()) {
				$string .= " engine " . $this->getStorageEngineName();
			}
			if ($this->hasComment()) {
				$string .= " comment " . single_quote($this->getComment());
			}
			if ($this->hasDataDirectoryName()) {
				$string .= " data directory " . single_quote($this->getDataDirectoryName());
			}
			if ($this->hasIndexDirectoryName()) {
				$string .= " index directory " . single_quote($this->getIndexDirectoryName());
			}
			if ($this->hasMaximumRowCount()) {
				$string .= " max_rows " . $this->getMaximumRowCount();
			}
			if ($this->hasMinimumRowCount()) {
				$string .= " min_rows " . $this->getMinimumRowCount();
			}
			if ($this->hasTablespaceName()) {
				$string .= " tablespace " . $this->getTablespaceName();
			}
			if ($this->hasPartitionDefinitions()) {
				$string .= "(" . $this->getPartitionDefinitionString() . ")";
			}
			return $string;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->properties);
		unset($this->propertyTypes);
		unset($this->partitionName);
		unset($this->_values);
		unset($this->valuesOperator);
	}
}
