<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\column\ShowColumnsStatement;
use JulianSeymour\PHPWebApplicationFramework\query\database\AlterDatabaseStatement;
use JulianSeymour\PHPWebApplicationFramework\query\database\CreateDatabaseStatement;
use JulianSeymour\PHPWebApplicationFramework\query\database\DropDatabaseStatement;
use JulianSeymour\PHPWebApplicationFramework\query\grant\GrantStatement;
use JulianSeymour\PHPWebApplicationFramework\query\grant\RevokeStatement;
use JulianSeymour\PHPWebApplicationFramework\query\index\CreateIndexStatement;
use JulianSeymour\PHPWebApplicationFramework\query\index\DropIndexStatement;
use JulianSeymour\PHPWebApplicationFramework\query\insert\InsertStatement;
use JulianSeymour\PHPWebApplicationFramework\query\insert\ReplaceStatement;
use JulianSeymour\PHPWebApplicationFramework\query\load\LoadDataStatement;
use JulianSeymour\PHPWebApplicationFramework\query\load\LoadXMLStatement;
use JulianSeymour\PHPWebApplicationFramework\query\role\CreateRoleStatement;
use JulianSeymour\PHPWebApplicationFramework\query\role\DropRoleStatement;
use JulianSeymour\PHPWebApplicationFramework\query\role\SetDefaultRoleStatement;
use JulianSeymour\PHPWebApplicationFramework\query\role\SetRoleStatement;
use JulianSeymour\PHPWebApplicationFramework\query\routine\DropRoutineStatement;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\select\TableStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\CreateTableStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\DropTableStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\RenameTableStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\TruncateTableStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterTableStatement;
use JulianSeymour\PHPWebApplicationFramework\query\tablespace\AlterTablespaceStatement;
use JulianSeymour\PHPWebApplicationFramework\query\tablespace\CreateTablespaceStatement;
use JulianSeymour\PHPWebApplicationFramework\query\tablespace\DropTablespaceStatement;
use JulianSeymour\PHPWebApplicationFramework\query\user\AlterUserStatement;
use JulianSeymour\PHPWebApplicationFramework\query\user\CreateUserStatement;
use JulianSeymour\PHPWebApplicationFramework\query\user\DropUserStatement;
use JulianSeymour\PHPWebApplicationFramework\query\user\RenameUserStatement;
use JulianSeymour\PHPWebApplicationFramework\query\view\AlterViewStatement;
use JulianSeymour\PHPWebApplicationFramework\query\view\CreateViewStatement;
use JulianSeymour\PHPWebApplicationFramework\query\view\DropViewStatement;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;

abstract class QueryBuilder extends Basic{

	public static function select(...$vars): SelectStatement{
		return new SelectStatement(...$vars);
	}

	public static function insert(): InsertStatement{
		return new InsertStatement();
	}

	public static function update(...$dbtable): UpdateStatement{
		return new UpdateStatement(...$dbtable);
	}

	public static function createTable(...$dbtable): CreateTableStatement{
		return new CreateTableStatement(...$dbtable);
	}

	public static function delete(): DeleteStatement{
		return new DeleteStatement();
	}

	public static function dropFunction(?string $name = null): DropRoutineStatement{
		return DropRoutineStatement::dropFunction($name);
	}

	public static function dropFunctionIfExists(?string $name = null): DropRoutineStatement
	{
		return DropRoutineStatement::dropFunctionIfExists($name);
	}

	public static function dropProcedure(?string $name = null): DropRoutineStatement{
		return DropRoutineStatement::dropProcedure($name);
	}

	public static function dropProcedureIfExists(?string $name = null): DropRoutineStatement{
		return DropRoutineStatement::dropProcedureIfExists($name);
	}

	public static function createDatabase($databaseName = null): CreateDatabaseStatement{
		return new CreateDatabaseStatement($databaseName);
	}

	public static function alterDatabase($databaseName = null): AlterDatabaseStatement{
		return new AlterDatabaseStatement($databaseName);
	}

	public static function dropDatabase($databaseName = null): DropDatabaseStatement{
		return new DropDatabaseStatement($databaseName);
	}

	public static function alterTable(...$dbtable): AlterTableStatement{
		return new AlterTableStatement(...$dbtable);
	}

	public static function grant(): GrantStatement{ // ...$privileges_or_role_names){
		return new GrantStatement(); // ...$privileges_or_role_names);
	}

	public static function revoke(): RevokeStatement{
		return new RevokeStatement();
	}

	public static function createIndex($indexDefinition = null): CreateIndexStatement{
		return new CreateIndexStatement($indexDefinition);
	}

	public static function dropIndex($indexName = null): DropIndexStatement{
		return new DropIndexStatement($indexName);
	}

	public static function replace(): ReplaceStatement{
		return new ReplaceStatement();
	}

	public static function createRoles(...$roles): CreateRoleStatement{
		return new CreateRoleStatement(...$roles);
	}

	public static function setTemporaryRole(...$roles): SetRoleStatement{
		return new SetRoleStatement(...$roles);
	}

	public static function setDefaultRole(...$roles): SetDefaultRoleStatement{
		return new SetDefaultRoleStatement(...$roles);
	}

	public static function dropRole(...$roles): DropRoleStatement{
		return new DropRoleStatement(...$roles);
	}

	public static function table(...$dbtable): TableStatement{
		return new TableStatement(...$dbtable);
	}

	public static function renameTable(): RenameTableStatement{
		return new RenameTableStatement();
	}

	public static function truncateTable(...$dbtable): TruncateTableStatement{
		return new TruncateTableStatement(...$dbtable);
	}

	public static function dropTable(...$tableNames): DropTableStatement{
		return new DropTableStatement(...$tableNames);
	}

	public static function createTablespace($name = null): CreateTablespaceStatement{
		return new CreateTablespaceStatement($name);
	}

	public static function alterTablespace($name = null): AlterTablespaceStatement{
		return new AlterTablespaceStatement($name);
	}

	public static function dropTablespace($name = null): DropTablespaceStatement{
		return new DropTablespaceStatement($name);
	}

	public static function createUser(...$users): CreateUserStatement{
		return new CreateUserStatement(...$users);
	}

	public static function alterUser(...$users): AlterUserStatement{
		return new AlterUserStatement(...$users);
	}

	public static function renameUser(): RenameUserStatement{
		return new RenameUserStatement();
	}

	public static function dropUser(...$users): DropUserStatement{
		return new DropUserStatement(...$users);
	}

	public static function createView($db = null, $name = null, $selectStatement = null): CreateViewStatement{
		return new CreateViewStatement($db, $name, $selectStatement);
	}

	public static function alterView($db = null, $name = null, $selectStatement = null): AlterViewStatement
	{
		return new AlterViewStatement($db, $name, $selectStatement);
	}

	public static function dropView(...$viewNames): DropViewStatement{
		return new DropViewStatement(...$viewNames);
	}

	public static function do(...$expressions): DoStatement{
		return new DoStatement(...$expressions);
	}

	public static function loadData(?string $infilename = null, ...$dbtable): LoadDataStatement{
		return new LoadDataStatement($infilename, ...$dbtable);
	}

	public static function loadXML($infilename, ...$dbtable): LoadXMLStatement{
		return new LoadXMLStatement($infilename, ...$dbtable);
	}

	public static function flush(): FlushStatement{
		return new FlushStatement();
	}

	public static function tableExists($mysqli, $databaseName, $tableName): bool{
		$f = __METHOD__;
		$print = false;
		if($print) {
			Debug::printStackTraceNoExit("{$f} entered");
		}
		if($mysqli->connect_errno) {
			Debug::error("{$f} Failed to connect to MySQL: ({$mysqli->connect_errno}) {$mysqli->connect_error}");
		}elseif(!$mysqli->ping()) {
			Debug::error("{$f} mysqli connection failed ping test: \"" . $mysqli->error . "\"");
		}elseif($print) {
			Debug::print("{$f} about to ask whether table {$databaseName}.{$tableName} exists");
		}
		$select = new SelectStatement("TABLE_SCHEMA", "TABLE_NAME");
		$select->from("information_schema", "tables")->where(new AndCommand(new WhereCondition("TABLE_SCHEMA", OPERATOR_EQUALS), new WhereCondition("TABLE_NAME", OPERATOR_EQUALS)));

		return $select->prepareBindExecuteGetResultCount($mysqli, 'ss', $databaseName, $tableName) === 1;
	}

	public static function showColumns(): ShowColumnsStatement{
		return new ShowColumnsStatement();
	}

	public static function columnExists($mysqli, $db, $tableName, $columnName){
		return QueryBuilder::showColumns()->from($db, $tableName)
			->where(new WhereCondition("Field", OPERATOR_EQUALS))
			->prepareBindExecuteGetResultCount($mysqli, 's', $columnName) === 1;
	}
}
