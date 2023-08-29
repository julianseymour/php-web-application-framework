<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\throttle;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\DataTypeDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\QuotaDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\select\CountCommand;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;
use mysqli;

abstract class ThrottleMeterData extends DataStructure{

	public static function getDatabaseNameStatic():string{
		return "security";
	}
	
	public static function getDefaultPersistenceModeStatic():int{
		return PERSISTENCE_MODE_UNDEFINED;
	}
	
	public static function getDataType(): string{
		return DATATYPE_LINKCOUNTER;
	}

	public function getLimitPerMinute(){
		return $this->getColumnValue("perMinute");
	}

	public function hasLimitPerMinute(){
		return $this->hasColumnValue("perMinute");
	}

	public function setLimitPerMinute($limit){
		return $this->setColumnValue("perMinute", $limit);
	}

	public function getLimitPerHour(){
		return $this->getColumnValue("perHour");
	}

	public function hasLimitPerHour(){
		return $this->hasColumnValue("perHour");
	}

	public static function throttleOnInsert(): bool{
		return false;
	}

	public function setLimitPerHour($limit){
		return $this->setColumnValue("perHour", $limit);
	}

	public function getLimitPerDay(){
		return $this->getColumnValue("perDay");
	}

	public function hasLimitPerDay(){
		return $this->hasColumnValue("perDay");
	}

	public function setLimitPerDay($limit){
		return $this->setColumnValue("perDay", $limit);
	}

	public function getLimitPerWeek()
	{
		return $this->getColumnValue("perWeek");
	}

	public function hasLimitPerWeek()
	{
		return $this->hasColumnValue("perWeek");
	}

	public function setLimitPerWeek($limit)
	{
		return $this->setColumnValue("perWeek", $limit);
	}

	public function getLimitPerMonth()
	{
		return $this->getColumnValue("perMonth");
	}

	public function hasLimitPerMonth()
	{
		return $this->hasColumnValue("perMonth");
	}

	public function setLimitPerMonth($limit)
	{
		return $this->setColumnValue("perMonth", $limit);
	}

	public function getLimitPerYear()
	{
		return $this->getColumnValue("perYear");
	}

	public function hasLimitPerYear()
	{
		return $this->hasColumnValue("perYear");
	}

	public function setLimitPerYear($limit)
	{
		return $this->setColumnValue("perYear", $limit);
	}

	public function getLimitPerLifetime()
	{
		return $this->getColumnValue("perLifetime");
	}

	public function hasLimitPerLifetime()
	{
		return $this->hasColumnValue("perLifetime");
	}

	public function setLimitPerLifetime($limit)
	{
		return $this->setColumnValue("perLifetime", $limit);
	}

	public function getLimitPerDecade()
	{
		return $this->getColumnValue("perDecade");
	}

	public function hasLimitPerDecade()
	{
		return $this->hasColumnValue("perDecade");
	}

	public function setLimitPerDecade($limit)
	{
		return $this->setColumnValue("perDecade", $limit);
	}

	/**
	 * returns true if the result count of $query satisfies the inequality of the quota for each interval that has one assigned,
	 * false otherwise
	 *
	 * @param mysqli $mysqli
	 * @param int $timestamp
	 * @param string $quota_operator
	 * @param SelectStatement $query
	 */
	public function meter(mysqli $mysqli, int $timestamp, string $quota_operator, SelectStatement $query): bool
	{
		$f = __METHOD__; //ThrottleMeterData::getShortClass()."(".static::getShortClass().")->meter()";
		try {
			$print = false;
			if (! $query instanceof SelectStatement) {
				Debug::error("{$f} query is not an instance of a select query");
			}
			$intervals = [
				"perMinute",
				"perHour",
				"perDay",
				"perWeek",
				"perMonth",
				"perYear",
				"perDecade"
			];
			if ($print) {
				Debug::print("{$f} throttled query is \"{$query}\"");
			}
			$query->select(new CountCommand("*"));
			$insert_ts = new WhereCondition("insertTimestamp", OPERATOR_GREATERTHAN);
			$query->pushWhereConditionParameters($insert_ts);
			$base_typedef = $query->hasTypeSpecifier() ? $query->getTypeSpecifier() : "";
			$base_params = $query->hasParameters() ? $query->getParameters() : [];
			$final_typedef = "";
			$final_params = [];
			$select_columns = [];
			foreach ($intervals as $index) {
				$interval = $this->getColumn($index);
				if (! $interval->hasValue()) {
					continue;
				}
				// extend parameter array for this interval
				$cutoff = $timestamp - $interval->getIntervalSeconds();
				// create alias for that column
				$select = QueryBuilder::select(new CountCommand('*'));
				if ($query->hasTableName()) {
					$select->from($query->getTableName());
				} elseif ($query->hasJoinExpressions()) {
					$select->setJoinExpressions($query->getJoinExpressions());
				} else {
					Debug::error("{$f} query lacks either a table name or join expressions");
				}
				$select->where($query->getWhereCondition());
				$final_typedef = "{$final_typedef}{$base_typedef}i";
				$final_params = array_merge($final_params, $base_params, [
					$cutoff
				]);
				$interval->setPersistenceMode(PERSISTENCE_MODE_ALIAS);
				$interval->setAliasExpression($select);
				array_push($select_columns, $interval->getColumnAlias());
			}
			if (empty($select_columns)) {
				if ($print) {
					Debug::print("{$f} no columns have quotas");
				}
				return true;
			}
			$superquery = new SelectStatement();
			$superquery->select(...$select_columns)
				->withTypeSpecifier($final_typedef)
				->withParameters($final_params);
			if ($print) {
				Debug::print("{$f} generated super query \"{$superquery}\"");
			}
			$results = $superquery->executeGetResult($mysqli // , ''
			)->fetch_all(MYSQLI_ASSOC);
			if ($print) {
				Debug::print("{$f} executing super query returned the following result array:");
				Debug::printArray($results[0]);
			}
			foreach ($select_columns as $interval) {
				$column_name = $interval->getColumnName();
				if ($print) {
					Debug::print("{$f} about to check count for column \"{$column_name}\"");
					// Debug::printArray($results);
				}
				$count = $results[0][$column_name];
				$quota = $this->getColumn($interval->getColumnName())
					->getValue();
				if ($print) {
					Debug::print("{$f} result count is {$count} for interval {$column_name}");
				}
				switch ($quota_operator) {
					case OPERATOR_GREATERTHAN:
						if ($count <= $quota) {
							if ($print) {
								Debug::print("{$f} quota {$quota} failed for item count {$count} and operator {$quota_operator}");
							}
							return false;
						}
						continue 2;
					case OPERATOR_GREATERTHANEQUALS:
						if ($count < $quota) {
							if ($print) {
								Debug::print("{$f} quota {$quota} failed for item count {$count} and operator {$quota_operator}");
							}
							return false;
						}
						continue 2;
					case OPERATOR_LESSTHAN:
						if ($count >= $quota) {
							if ($print) {
								Debug::print("{$f} quota {$quota} failed for item count {$count} and operator {$quota_operator}");
							}
							return false;
						}
						continue 2;
					case OPERATOR_LESSTHANEQUALS:
						if ($count > $quota) {
							if ($print) {
								Debug::print("{$f} quota {$quota} failed for item count {$count} and operator {$quota_operator}");
							}
							return false;
						}
						continue 2;
					case OPERATOR_IS_NULL:
					case OPERATOR_EQUALS:
					case OPERATOR_EQUALSEQUALS:
					case OPERATOR_LESSTHANGREATERTHAN:
					default:
						Debug::error("{$f} invalid operator \"{$quota_operator}\"");
						return false;
				}
			}
			if ($print) {
				Debug::print("{$f} inequality satisfied");
			}
			return true;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		$f = __METHOD__; //ThrottleMeterData::getShortClass()."(".static::getShortClass().")::declareColumns()";
		try {
			parent::declareColumns($columns, $ds);
			$metered_type = new DataTypeDatum("meteredDataType");
			$metered_type->setDefaultValue(DATATYPE_UNKNOWN);
			$per_minute = new QuotaDatum("perMinute", 32);
			$minute = 60;
			$per_minute->setIntervalSeconds($minute);
			$hour = $minute * 60;
			$per_hour = new QuotaDatum("perHour", 64);
			$per_hour->setIntervalSeconds($hour);
			$per_day = new QuotaDatum("perDay", 64);
			$day = $hour * 24;
			$per_day->setIntervalSeconds($day);
			$per_week = new QuotaDatum("perWeek", 64);
			$per_week->setIntervalSeconds($day * 7);
			$per_month = new QuotaDatum("perMonth", 64);
			$per_month->setIntervalSeconds($day * 30);
			$per_quarter = new QuotaDatum("perQuarter", 64);
			$per_quarter->setIntervalSeconds($day * 90);
			$per_year = new QuotaDatum("perYear", 64);
			$year = $day * 365;
			$per_year->setIntervalSeconds($year);
			$per_decade = new QuotaDatum("perDecade", 64);
			$per_decade->setIntervalSeconds($year * 10);
			$per_lifetime = new QuotaDatum("perLifetime", 64);
			$per_lifetime->setIntervalSeconds(time());
			static::pushTemporaryColumnsStatic($columns, $metered_type, $per_minute, $per_hour, $per_day, $per_week, $per_month, $per_year, $per_decade, $per_lifetime);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getPhylumName(): string{
		return "throttleMeters";
	}

	public static function getPrettyClassName():string{
		return _("Throttle settings");
	}

	public static function getPrettyClassNames():string{
		return static::getPrettyClassName();
	}

	public static function getTableNameStatic(): string{
		$f = __METHOD__; 
		return ErrorMessage::unimplemented($f);
	}
}
