<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\arr\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class WithClause extends Basic implements StaticPropertyTypeInterface, SQLInterface
{

	use ArrayPropertyTrait;
	use StaticPropertyTypeTrait;

	/*
	 * public function __construct(){
	 * parent::__construct();
	 * $this->requirePropertyType("commonTableExpressions", CommonTableExpression::class);
	 * }
	 */
	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array
	{
		return [
			"commonTableExpressions" => CommonTableExpression::class
		];
	}

	public function setCommonTableExpressions($ctes)
	{
		return $this->setArrayProperty("commonTableExpressions", $ctes);
	}

	public function hasCommonTableExpressions()
	{
		return $this->hasArrayProperty("commonTableExpressions");
	}

	public function getCommonTableExpressions()
	{
		return $this->getProperty("commonTableExpressions");
	}

	public function pushCommonTableExpressions(...$ctes)
	{
		return $this->pushArrayProperty("commonTableExpressions", ...$ctes);
	}

	public function mergeCommonTableExpressions($ctes)
	{
		return $this->mergeArrayProperty("commonTableExpressions", $ctes);
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"recursive"
		]);
	}

	public function getCommonTableExpression($num): CommonTableExpression
	{
		return $this->getArrayPropertyValue("commonTableExpressions", $num);
	}

	public function setRecursiveFlag($value = true)
	{
		$f = __METHOD__; //WithClause::getShortClass()."(".static::getShortClass().")->setRecursiveFlag()";
		if ($value && $this->hasCommonTableExpressions() && $this->getCommonTableExpressionCount() > 1) {
			Debug::error("{$f} unsupported: syntax for ");
		}
		return $this->setFlag("recursive", $value);
	}

	public function getRecursiveFlag()
	{
		return $this->getFlag("recursive");
	}

	public static function recursive($cteName, $subquery)
	{
		$with = new WithClause();
		$with->setRecursiveFlag(true);
		$with->pushCommonTableExpressions(new CommonTableExpression($cteName, $subquery));
		return $with;
	}

	public function toSQL(): string
	{
		$f = __METHOD__; //WithClause::getShortClass()."(".static::getShortClass().")->toSQL()";
		try {
			// mine:
			/*
			 * with recursive commonTableExpression as (
			 * select * from data.comments where parentKey='0283ab5d0368c91c3f48400d5a97b856740f193a'
			 * union all
			 * select childComment.* from data.comments as childComment
			 * join commonTableExpression as parentComment on childComment.parentKey = parentComment.uniqueKey
			 * )
			 * select * from commonTableExpression;
			 */

			/*
			 * $dbtable = "data.comments"; //name of table containing infinitely recursive hierarchical nodes
			 * $parentKey = "parentKey"; //name of parent key linking those nodes together
			 * $parentKeyName = 'uniqueKey'; //name of key referenced by parentKey in the parent table
			 * $cteName = "commonTableExpression"; //name of common table expression, doesn't really matter
			 * $initialSet = QueryBuilder::select()->from($dbtable)->where(
			 * new WhereCondition($parentKey, OPERATOR_EQUALS)
			 * )->unionAll(
			 * QueryBuilder::select("child.*")->from(
			 * JoinedTable::join(
			 * TableFactor::create()->withTableName($dbtable)->as("child"),
			 * TableFactor::create()->withTableName($cteName)->as("parent"),
			 * new BinaryExpressionCommand(
			 * "child.{$parentKey}",
			 * OPERATOR_EQUALS,
			 * "parent.{$parentKeyName}"
			 * )
			 * )
			 * )
			 * );
			 */
			// with recursive commonTableExpression as (
			// SelectStatement $initialSet;
			// select * from data.comments where parentKey='0283ab5d0368c91c3f48400d5a97b856740f193a'
			// UnionClause $unionClause;
			// union all
			// SelectStatement $recursiveSet;
			// select childComment.* from
			// data.comments as childComment
			// join
			// commonTableExpression as parentComment
			// on childComment.parentKey = parentComment.uniqueKey
			// )
			// select * from commonTableExpression;

			// official:
			/*
			 * WITH [RECURSIVE] cte_name [(col_name [, col_name] ...)] AS (subquery) [, cte_name [(col_name [, col_name] ...)] AS (subquery)] ...
			 */
			$string .= "with ";
			if ($this->getRecursiveFlag()) {
				$string .= "recursive ";
			}
			$ctes = [];
			foreach ($this->getCommonTableExpressions() as $c) {
				if ($c instanceof SQLInterface) {
					$c = $c->toSQL();
				}
				array_push($ctes, $c);
			}
			$string .= implode(',', $ctes);
			return $string;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
