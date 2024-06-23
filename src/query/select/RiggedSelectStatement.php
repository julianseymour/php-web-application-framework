<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\select;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\TableDefinitionInterface;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAlias;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAliasExpression;
use JulianSeymour\PHPWebApplicationFramework\query\join\TableFactor;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;

class RiggedSelectStatement extends SelectStatement{
	
	public static function fromTableDefinition(TableDefinitionInterface $that, ...$column_names):SelectStatement{
		$f = __METHOD__;
		try{
			$print = false;
			$db_column_names = [];
			$embedded_columns = [];
			$alias_column_names = [];
			if(isset($column_names) && is_array($column_names) && !empty($column_names)){
				foreach($column_names as $column_name){
					if(is_array($column_name)){
						Debug::error("{$f} column name is an array");
					}
					$column = $that->getColumn($column_name);
					if($column->applyFilter(COLUMN_FILTER_DISABLED)){
						if($print){
							Debug::print("{$f} column \"{$column_name}\" is disabled");
						}
						continue;
					}elseif($column->applyFilter(COLUMN_FILTER_DATABASE)){
						array_push($db_column_names, $column_name);
					}elseif($that instanceof DataStructure){
						if($column->applyFilter(COLUMN_FILTER_EMBEDDED)){
							$group = $column->getEmbeddedName();
							if(!array_key_exists($group, $embedded_columns)){
								$embedded_columns[$group] = [];
							}
							array_push($embedded_columns[$group], $column_name);
						}elseif($column->applyFilter(COLUMN_FILTER_ALIAS)){
							array_push($alias_column_names, $column_name);
						}else{
							Debug::error("{$f} attempting to select non-database, non-embedded column \"{$column_name}\"");
						}
					}else{
						Debug::error("{$f} attempting to select a non-database column in something that is not a DataStructure");
					}
				}
			}else{
				if(!$that->hasColumns()){
					Debug::error("{$f} columns are undefined for this ".$that->getDebugString());
				}
				$db_column_names = $that->getFilteredColumnNames(COLUMN_FILTER_DATABASE);
				if($that instanceof DataStructure){
					$alias_column_names = $that->getFilteredColumnNames(COLUMN_FILTER_ALIAS);
					$temp = $that->getFilteredColumns(COLUMN_FILTER_EMBEDDED);
					if(!empty($temp)){
						foreach($temp as $column_name => $column){
							$group = $column->getEmbeddedName();
							if(!array_key_exists($group, $embedded_columns)){
								$embedded_columns[$group] = [];
							}
							array_push($embedded_columns[$group], $column_name);
						}
					}
				}
			}
			$identifierName = $that->getIdentifierName();
			// if there are any embedded or subqueried columns and you did not opt to select the identifier, select it as it is needed to match the embedded/subqueried columns
			if(!empty($embedded_columns) || !empty($alias_column_names)){
				if(false === array_search($identifierName, $db_column_names)){
					array_push($db_column_names, $identifierName);
				}elseif($print){
					Debug::print("{$f} identifier column name is already getting queried");
				}
			}
			$db = $that->getDatabaseName();
			$table = $that->getTableName();
			if(
				$that instanceof DataStructure 
				&& (!empty($embedded_columns) || !empty($alias_column_names))
			){
				$select = new SelectStatement();
				$select->withJoinExpressions(TableFactor::create()->withDatabaseName($db)->withTableName($table)->as("t0"));
				$select_us = [];
				foreach($db_column_names as $column_name){
					$column_name_escaped = back_quote($column_name);
					$gdvc = new GetDeclaredVariableCommand();
					$gdvc->setVariableName("t0.{$column_name_escaped} as {$column_name_escaped}");
					array_push($select_us, $gdvc);
				}
				if(!empty($alias_column_names)){
					foreach($alias_column_names as $column_name){
						$column = $that->getColumn($column_name);
						$alias = $column->getColumnAlias();
						$expr = $alias->getExpression();
						if($expr instanceof SelectStatement){
							if($expr->hasParameters()){
								if($print){
									Debug::print("{$f} column alias \"{$alias}\" has parameters");
								}
								$select->setFlag("unassigned", true);
							}
						}
						array_push($select_us, $alias);
					}
				}
				if(!empty($embedded_columns)){
					if($print){
						Debug::print("{$f} about to create join expressions for embedded data structures");
					}
					$embedded_structures = $that->getEmbeddedDataStructures();
					//if the user passsed column names and one or more of them is an embedded column, only select those columns
					foreach($embedded_structures as $group => $e){
						foreach($embedded_columns[$group] as $column_name){
							array_push($select_us, new ColumnAlias(new ColumnAliasExpression($group, $column_name), $column_name));
						}
						$bexpr = new BinaryExpressionCommand(
							new ColumnAliasExpression($group, "joinKey"),
							OPERATOR_EQUALSEQUALS,
							new ColumnAliasExpression("t0", $identifierName)
						);
						$bexpr->setEscapeType(ESCAPE_TYPE_NONE);
						$select->leftJoin(TableFactor::create()->withDatabaseName($e->getDatabaseName())->withTableName($e->getTableName())->as($group))->on($bexpr);
					}
					if($print){
						Debug::print("{$f} about to deallocate embedded data structures");
					}
					$that->disableDeallocation();
					deallocate($embedded_structures);
					$that->enableDeallocation();
				}
				$select->select(...$select_us);
			}else{
				if($print){
					Debug::print("{$f} there are no embedded data structures");
				}
				$select = new SelectStatement(...$db_column_names);
				$select->from($db, $table);
			}
			if($print){
				Debug::print("{$f} generated ".$select->getDebugString());
			}
			return $select;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public static function getAliasedColumnSelectStatement(DataStructure $that, string $column_name, ?string $key=null):SelectStatement{
		$f = __METHOD__;
		try{
			$aliased_column = $that->getColumn($column_name);
			if($key === null){
				$key = $aliased_column->getValue();
			}
			$subqueryClass = $aliased_column->getSubqueryClass();
			if(!method_exists($subqueryClass, 'getTableNameStatic')){
				Debug::error("{$f} table name cannot be determined statically for subquery class \"{$subqueryClass}\"");
			}
			$converse_keyname = $aliased_column->getConverseRelationshipKeyName();
			$select = new SelectStatement($subqueryClass::getIdentifierNameStatic());
			$select->from(
				$subqueryClass::getDatabaseNameStatic(),
				$subqueryClass::getTableNameStatic()
			)->where(
				new WhereCondition($column_name, OPERATOR_EQUALS)
			)->escape(ESCAPE_TYPE_PARENTHESIS);
			return $that->select()->where(
				new WhereCondition(
					$that->getIdentifierName(),
					OPERATOR_EQUALS,
					null,
					$subqueryClass::generateLazyAliasExpression(
						get_class($that),
						$converse_keyname,
						$select
					)
				)
			)->withTypeSpecifier($aliased_column->getTypeSpecifier()."s")->withParams([
				$key,
				$converse_keyname
			]);
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
