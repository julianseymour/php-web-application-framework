<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\join;

use function JulianSeymour\PHPWebApplicationFramework\implode_back_quotes;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnNamesTrait;
use Exception;

class JoinedTable extends JoinExpression implements StaticPropertyTypeInterface{

	use MultipleColumnNamesTrait;
	use StaticPropertyTypeTrait;

	protected $joinType;

	protected $joinExpression;

	protected $searchCondition;

	public function __construct($joinType, $joinExpression, $spec = null){
		$f = __METHOD__;
		parent::__construct();
		// $this->requirePropertyType("columnNames", 's');
		$this->setJoinType($joinType);
		$this->setJoinExpression($joinExpression);
		if ($spec !== null) {
			if (is_array($spec)) {
				$this->setColumnNames($spec);
			} elseif (is_string($spec) || $spec instanceof ExpressionCommand) {
				$this->setSearchCondition($spec);
			} else {
				$gottype = is_object($spec) ? $spec->getClass() : gettype($spec);
				Debug::error("{$f} neither of the above \"{$gottype}\"");
			}
		}
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->properties);
		unset($this->propertyTypes);
		unset($this->joinType);
		unset($this->joinExpression);
		unset($this->searchCondition);
	}

	public function setJoinType($type){
		$f = __METHOD__;
		if ($type == null) {
			unset($this->joinType);
			unset($this->searchCondition);
			return null;
		} elseif (! is_string($type)) {
			Debug::error("{$f} join type must be a string");
		} elseif (empty($type)) {
			Debug::error("{$f} join type cannot be the empty string");
		} else
			switch ($type) {
				case JOIN_TYPE_JOIN: // In MySQL, JOIN, CROSS JOIN, and INNER JOIN are syntactic equivalents (they can replace each other). In standard SQL, they are not equivalent. INNER JOIN is used with an ON clause, CROSS JOIN is used otherwise.
				case JOIN_TYPE_INNER: // INNER JOIN and , (comma) are semantically equivalent in the absence of a join condition: both produce a Cartesian product between the specified tables (that is, each and every row in the first table is joined to each and every row in the second table).
				case JOIN_TYPE_CROSS:
				case JOIN_TYPE_STRAIGHT: // STRAIGHT_JOIN is similar to JOIN, except that the left table is always read before the right table. This can be used for those (few) cases for which the join optimizer processes the tables in a suboptimal order.
				case JOIN_TYPE_LEFT:
				case JOIN_TYPE_RIGHT: // RIGHT JOIN works analogously to LEFT JOIN. To keep code portable across databases, it is recommended that you use LEFT JOIN instead of RIGHT JOIN.
				case JOIN_TYPE_NATURAL:
				case JOIN_TYPE_NATURAL_INNER:
				case JOIN_TYPE_NATURAL_LEFT: // The NATURAL [LEFT] JOIN of two tables is defined to be semantically equivalent to an INNER JOIN or a LEFT JOIN with a USING clause that names all columns that exist in both tables.
				case JOIN_TYPE_NATURAL_RIGHT:
					break;
				default:
					Debug::error("{$f} invalid join type \"{$type}\"");
			}
		return $this->joinType = $type;
	}

	public function hasJoinType():bool{
		return isset($this->joinType) && is_string($this->joinType) && ! empty($this->joinType);
	}

	public function getJoinType(){
		$f = __METHOD__;
		if (! $this->hasJoinType()) {
			Debug::error("{$f} join type is undefined");
		}
		return $this->joinType;
	}

	public function getJoinTypeString(){
		$f = __METHOD__;
		$type = $this->getJoinType();
		switch ($type) {
			case JOIN_TYPE_JOIN:
				return "join";
			case JOIN_TYPE_CROSS:
				return "cross join";
			case JOIN_TYPE_INNER:
				return "inner join";
			case JOIN_TYPE_STRAIGHT:
				return "straight_join";
			case JOIN_TYPE_LEFT:
				return "left join";
			case JOIN_TYPE_RIGHT:
				return "right join";
			case JOIN_TYPE_NATURAL:
				return "natural join";
			case JOIN_TYPE_NATURAL_INNER:
				return "natural inner join";
			case JOIN_TYPE_NATURAL_LEFT:
				return "natural left join";
			case JOIN_TYPE_NATURAL_RIGHT:
				return "natural right join";
			default:
				Debug::error("{$f} invalid join type \"{$type}\"");
		}
	}

	public function hasJoinExpression(){
		return isset($this->joinExpression);
	}

	public function getJoinExpression(){
		$f = __METHOD__;
		if (! $this->hasJoinExpression()) {
			Debug::error("{$f} join expression is undefined");
		}
		return $this->joinExpression;
	}

	public function setJoinExpression($tr){
		$f = __METHOD__;
		if ($tr instanceof JoinedTable) {
			if ($this->hasJoinType()) {
				$type = $this->getJoinType();
				switch ($type) {
					case JOIN_TYPE_LEFT:
					case JOIN_TYPE_RIGHT:
						break;
					default:
						Debug::error("{$f} cannot use a JoinedTable except for non-natural left/right joins");
				}
			}
		} elseif ($tr instanceof TableFactor && $tr->getEscapeFlag()) {
			Debug::error("{$f} you cannot escape table references that are part of a JoinedTable");
		}
		return $this->joinExpression = $tr;
	}

	public function setSearchCondition($sc){
		$f = __METHOD__;
		if ($sc == null) {
			unset($this->searchCondition);
			return null;
		}
		// ErrorMessage::unimplemented($f);
		return $this->searchCondition = $sc;
	}

	public function hasSearchCondition(){
		return isset($this->searchCondition);
	}

	public function getSearchCondition(){
		$f = __METHOD__;
		if (! $this->hasSearchCondition()) {
			Debug::error("{$f} search condition is undefined");
		}
		return $this->searchCondition;
	}

	public function getTableReferenceString(){
		$f = __METHOD__;
		try {
			$join = $this->getJoinExpression();
			if ($join instanceof SQLInterface) {
				$join = $join->toSQL();
			}
			$string = $this->getJoinTypeString() . " {$join}";
			$type = $this->getJoinType();
			switch ($type) {
				case JOIN_TYPE_CROSS:
				case JOIN_TYPE_INNER:
				case JOIN_TYPE_JOIN:
				case JOIN_TYPE_STRAIGHT:
					// table_reference {[INNER | CROSS] JOIN | STRAIGHT_JOIN} table_factor [{ON search_condition | USING (join_column_list)}]
					if ($this->hasSearchCondition()) {
						$sc = $this->getSearchCondition();
						if ($sc instanceof SQLInterface) {
							$sc = $sc->toSQL();
						}
						$string .= " on {$sc}";
					} elseif ($this->hasColumnNames()) {
						$string .= " using " . implode_back_quotes(',', $this->getColumnNames());
					}
					break;
				case JOIN_TYPE_LEFT:
				case JOIN_TYPE_RIGHT:
					// table_reference {LEFT|RIGHT} [OUTER] JOIN table_reference {ON search_condition | USING (join_column_list)}
					if ($this->hasSearchCondition()) {
						$sc = $this->getSearchCondition();
						if ($sc instanceof SQLInterface) {
							$sc = $sc->toSQL();
						}
						$string .= " on {$sc}";
					} elseif ($this->hasColumnNames()) {
						$string .= " using " . implode_back_quotes(',', $this->getColumnNames());
					} else {
						Debug::error("{$f} left/right outer joins must have either a search condition or join column list");
					}
					break;
				case JOIN_TYPE_NATURAL:
				case JOIN_TYPE_NATURAL_INNER:
				case JOIN_TYPE_NATURAL_LEFT:
				case JOIN_TYPE_NATURAL_RIGHT:
					// table_reference NATURAL [INNER | {LEFT|RIGHT} [OUTER]] JOIN table_factor
					break;
				default:
					Debug::error("{$f} invalid join type \"{$type}\"");
			}
			return $string;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function on($sc)
	{
		$this->setSearchCondition($sc);
		return $this;
	}

	public static function join($join_expression, $spec = null)
	{
		return new JoinedTable(JOIN_TYPE_JOIN, $join_expression, $spec);
	}

	public static function crossJoin($join_expression, $spec = null)
	{
		return new JoinedTable(JOIN_TYPE_CROSS, $join_expression, $spec);
	}

	public static function innerJoin($join_expression, $spec = null)
	{
		return new JoinedTable(JOIN_TYPE_INNER, $join_expression, $spec);
	}

	public static function straightJoin($join_expression, $spec = null)
	{
		return new JoinedTable(JOIN_TYPE_STRAIGHT, $join_expression, $spec);
	}

	public static function leftJoin($join_expression, $spec = null)
	{
		return new JoinedTable(JOIN_TYPE_LEFT, $join_expression, $spec);
	}

	public static function rightJoin($join_expression, $spec = null)
	{
		return new JoinedTable(JOIN_TYPE_RIGHT, $join_expression, $spec);
	}

	public static function naturalJoin($join_expression)
	{
		return new JoinedTable(JOIN_TYPE_NATURAL, $join_expression);
	}

	public static function naturalInnerJoin($join_expression)
	{
		return new JoinedTable(JOIN_TYPE_NATURAL_INNER, $join_expression);
	}

	public static function naturalLeftJoin($join_expression)
	{
		return new JoinedTable(JOIN_TYPE_NATURAL_LEFT, $join_expression);
	}

	public static function naturalRightJoin($join_expression)
	{
		return new JoinedTable(JOIN_TYPE_NATURAL_RIGHT, $join_expression);
	}

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array
	{
		return [
			"columnNames" => 's'
		];
	}
}
