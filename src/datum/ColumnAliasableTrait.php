<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\datum\ColumnDefinitionTrait\getTypeSpecifier;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAlias;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAliasExpression;
use JulianSeymour\PHPWebApplicationFramework\query\join\TableFactor;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;

trait ColumnAliasableTrait{
	
	protected $columnAlias;
	
	/**
	 * SelectStatement for columns whose values are selected via subquery
	 *
	 * @var SelectStatement
	 */
	protected $aliasExpression;
	
	// these are for generating the aliasExpression if it is not set explicitly
	protected $subqueryClass;
	
	protected $subqueryColumnName;
	
	protected $subqueryDatabaseName;
	
	protected $subqueryTableName;
	
	protected $subqueryTableAlias;
	
	protected $subqueryExpression;
	
	protected $subqueryLimit;
	
	protected $subqueryOrderBy;
	
	protected $subqueryParameters;
	
	protected $subqueryTypeSpecifier;
	
	protected $subqueryWhereCondition;
	
	public function hasAliasExpression(): bool{
		return isset($this->aliasExpression);
	}
	
	public function setAliasExpression($st){
		if($this->hasAliasExpression()){
			$this->release($this->aliasExpression);
		}
		$this->setPersistenceMode(PERSISTENCE_MODE_ALIAS);
		return $this->aliasExpression = $this->claim($st);
	}
	
	public function alias($st): Datum{
		$this->setAliasExpression($st);
		return $this;
	}
	
	public function hasSubqueryWhereCondition(): bool{
		return isset($this->subqueryWhereCondition);
	}
	
	public function getSubqueryWhereCondition(){
		$f = __METHOD__;
		if(!$this->hasSubqueryWhereCondition()){
			$name = $this->getName();
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} subquery where condition is undefined for coulmn \"{$name}\", declared {$decl}");
		}
		return $this->subqueryWhereCondition;
	}
	
	public function setSubqueryWhereCondition($where){
		if($this->hasSubqueryWhereCondition()){
			$this->release($this->subqueryWhereCondition);
		}
		$this->setPersistenceMode(PERSISTENCE_MODE_ALIAS);
		return $this->subqueryWhereCondition = $this->claim($where);
	}
	
	public function hasSubqueryColumnName(): bool{
		return isset($this->subqueryColumnName);
	}
	
	public function setSubqueryColumnName(?string $sqcn): ?string{
		if($this->hasSubqueryColumnName()){
			$this->release($this->subqueryColumnName);
		}
		return $this->subqueryColumnName = $this->claim($sqcn);
	}
	
	public function getSubqueryColumnName(): string{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasSubqueryColumnName()){
			if($print){
				Debug::warning("{$f} subquery column name is undefined, assuning it's the same at this column's name");
			}
			return $this->getName();
		}
		return $this->subqueryColumnName;
	}
	
	public function setSubqueryExpression($expr){
		$f = __METHOD__;
		if($this->hasSubqueryExpression()){
			$this->release($this->subqueryExpression);
		}
		$this->setPersistenceMode(PERSISTENCE_MODE_ALIAS);
		return $this->subqueryExpression = $this->claim($expr);
	}
	
	public function hasSubqueryExpression(): bool{
		return isset($this->subqueryExpression);
	}
	
	public function getSubqueryExpression(){
		$f = __METHOD__;
		$print = false;
		$name = $this->getName();
		if(!$this->hasSubqueryExpression()){
			if($this->hasSubqueryTableName() || $this->hasSubqueryClass()){
				if($print){
					Debug::print("{$f} subquery table name or class is defined");
				}
				return new ColumnAlias(new ColumnAliasExpression($this->getSubqueryTableAlias(), $this->getSubqueryColumnName()), $name);
			}
			Debug::error("{$f} subquery expression and table name are undefined for column \"{$name}\"");
		}elseif($print){
			Debug::print("{$f} subquery expression was already assigned");
		}
		return $this->subqueryExpression;
	}
	
	public function setSubqueryClass(?string $class): ?string{
		$f = __METHOD__;
		if(!class_exists($class)){
			Debug::error("{$f} class \"{$class}\" does not exist");
		}elseif($this->hasSubqueryClass()){
			$this->release($this->subqueryClass);
		}
		return $this->subqueryClass = $class;
	}
	
	public function hasSubqueryClass(): bool{
		return isset($this->subqueryClass);
	}
	
	public function getSubqueryClass(): string{
		$f = __METHOD__;
		if(!$this->hasSubqueryClass()){
			Debug::error("{$f} subquery class is undefined");
		}
		return $this->subqueryClass;
	}
	
	public function hasSubqueryDatabaseName(): bool{
		return isset($this->subqueryDatabaseName);
	}
	
	public function setSubqueryDatabaseName(?string $db): ?string{
		if($this->hasSubqueryDatabaseName()){
			$this->release($this->subqueryDatabaseName);
		}
		return $this->subqueryDatabaseName = $this->claim($db);
	}
	
	public function getSubqueryDatabaseName(): string{
		$f = __METHOD__;
		if($this->hasSubqueryDatabaseName()){
			return $this->subqueryDatabaseName;
		}elseif($this->hasSubqueryClass()){
			return $this->getSubqueryClass()::getDatabaseNameStatic();
		}
		Debug::error("{$f} subquery database name and class are undefined");
	}
	
	public function hasSubqueryTableName(): bool{
		return isset($this->subqueryTableName);
	}
	
	public function setSubqueryTableName(?string $table): ?string{
		if($this->hasSubqueryTableName()){
			$this->release($this->subqueryTableName);
		}
		return $this->subqueryTableName = $this->claim($table);
	}
	
	public function getSubqueryTableName(): string{
		$f = __METHOD__;
		if($this->hasSubqueryTableName()){
			return $this->subqueryTableName;
		}elseif($this->hasSubqueryClass()){
			$sqc = $this->getSubqueryClass();
			if(!method_exists($sqc, 'getTableNameStatic')){
				Debug::error("{$f} table name cannot be determined statically for subquery class \"{$sqc}\"");
			}
			return $sqc::getTableNameStatic();
		}
		Debug::error("{$f} subquery table name and class are undefined");
	}
	
	public function hasSubqueryTableAlias(): bool{
		return isset($this->subqueryTableAlias);
	}
	
	public function setSubqueryTableAlias(?string $alias): ?string{
		if($this->hasSubqueryTableAlias()){
			$this->release($this->subqueryTableAlias);
		}
		return $this->subqueryTableAlias = $this->claim($alias);
	}
	
	public function getSubqueryTableAlias(): string{
		if($this->hasSubqueryTableAlias()){
			return $this->subqueryTableAlias;
		}
		return $this->getSubqueryTableName() . "_alias";
	}
	
	public function hasSubqueryOrderBy(): bool{
		return isset($this->subqueryOrderBy);
	}
	
	public function setSubqueryOrderBy($ob): ?array{
		if($this->hasSubqueryOrderBy()){
			$this->release($this->subqueryOrderBy);
		}
		if(!is_array($ob)){
			$ob = [$ob];
		}
		return $this->subqueryOrderBy = $this->claim($ob);
	}
	
	public function getSubqueryOrderBy(): array{
		$f = __METHOD__;
		if(!$this->hasSubqueryOrderBy()){
			Debug::error("{$f} subquery order by is undefined");
		}
		return $this->subqueryOrderBy;
	}
	
	public function hasSubqueryLimit(): bool{
		return isset($this->subqueryLimit);
	}
	
	public function getSubqueryLimit(): int{
		$f = __METHOD__;
		if(!$this->hasSubqueryLimit()){
			Debug::error("{$f} subquery limit is undefined");
		}
		return $this->subqueryLimit;
	}
	
	public function setSubqueryLimit(?int $limit): ?int{
		if($this->hasSubqueryLimit()){
			$this->release($this->subqueryLimit);
		}
		return $this->subqueryLimit = $this->claim($limit);
	}
	
	public function hasSubqueryParameters(): bool{
		return isset($this->subqueryParameters);
	}
	
	public function getSubqueryParameters(): array{
		$f = __METHOD__;
		if(!$this->hasSubqueryParameters()){
			Debug::error("{$f} subquery parameters are undefined");
		}
		return $this->subqueryParameters;
	}
	
	public function setSubqueryParameters($params): ?array{
		if($this->hasSubqueryParameters()){
			$this->release($this->subqueryParameters);
		}
		if(!is_array($params)){
			$params = [
				$params
			];
		}
		return $this->subqueryParameters = $this->claim($params);
	}
	
	public function hasSubqueryTypeSpecifier(): bool{
		return isset($this->subqueryTypeSpecifier);
	}
	
	public function setSubqueryTypeSpecifier(?string $ts): ?string{
		if($this->hasSubqueryTypeSpecifier()){
			$this->release($this->subqueryTypeSpecifier);
		}
		return $this->subqueryTypeSpecifier = $this->claim($ts);
	}
	
	public function getSubqueryTypeSpecifier(): string{
		$f = __METHOD__;
		if($this->hasSubqueryTypeSpecifier()){
			return $this->subqueryTypeSpecifier;
		}elseif($this->hasSubqueryClass() && $this->hasSubqueryWhereCondition()){
			return $this->getSubqueryClass()::getTypeSpecifierStatic($this->getSubqueryWhereCondition()->getConditionalColumnNames());
		}
		Debug::warning("{$f} explicit type specifier or subquery class and where condition are undefined -- inferring type specifier from parameters");
		return getTypeSpecifier($this->getSubqueryParameters());
	}
	
	public function getAliasExpression(){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($this->hasAliasExpression()){
			if($print){
				Debug::print("{$f} alias expression is already defined");
			}
			return $this->aliasExpression;
		}elseif($print){
			Debug::print("{$f} alias expression is not already defined, creating one now");
		}
		$select = new SelectStatement();
		$select->setExpressions([$this->getSubqueryExpression()]);
		$select->withJoinExpressions(
			TableFactor::create()->withDatabaseName(
				$this->getSubqueryDatabaseName()
			)->withTableName(
				$this->getSubqueryTableName()
			)->as($this->getSubqueryTableAlias())
		)->where($this->getSubqueryWhereCondition());
		if($this->hasSubqueryOrderBy()){
			$select->setOrderBy(...$this->getSubqueryOrderBy());
		}
		if($this->hasSubqueryLimit()){
			$select->limit($this->getSubqueryLimit());
		}
		if($this->hasSubqueryParameters()){
			$select->withTypeSpecifier(
				$this->getSubqueryTypeSpecifier()
			)->withParameters($this->getSubqueryParameters());
		}elseif($print){
			$decl = $this->getDeclarationLine();
			Debug::print("{$f} no subquery parameters. Instantiated {$decl}");
		}
		return $select; //$this->setAliasExpression($select);
	}
	
	public function hasColumnAlias(): bool{
		return isset($this->columnAlias);
	}
	
	public function setColumnAlias($alias){
		if($this->hasColumnAlias()){
			$this->release($this->columnAlias);
		}
		return $this->columnAlias = $this->claim($alias);
	}
	
	public function getColumnAlias(): ColumnAlias{
		if($this->hasColumnAlias()){
			return $this->columnAlias;
		}
		$alias = new ColumnAlias($this->getAliasExpression(), $this->getName());
		return $alias;
	}
	
	public function antialias(int $persistence_mode = PERSISTENCE_MODE_DATABASE): Datum{
		$this->release($this->columnAlias);
		$this->release($this->subqueryClass);
		$this->release($this->subqueryColumnName);
		$this->release($this->subqueryDatabaseName);
		$this->release($this->subqueryExpression);
		$this->release($this->subqueryLimit);
		$this->release($this->subqueryOrderBy);
		$this->release($this->subqueryParameters);
		$this->release($this->subqueryTableAlias);
		$this->release($this->subqueryTableName);
		$this->release($this->subqueryTypeSpecifier);
		$this->release($this->subqueryWhereCondition);
		$this->setPersistenceMode($persistence_mode);
		return $this;
	}
}
