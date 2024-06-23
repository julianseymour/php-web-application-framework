<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use JulianSeymour\PHPWebApplicationFramework\command\data\GetIdentifierNameCommand;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\CreateTableStatement;
use mysqli;

interface TableDefinitionInterface{
	
	static function reorderColumns(array $columns, ?array $order=null):?array;
	function getTableName():string;
	function getDatabaseName():string;
	function getCreateTableStatement():CreateTableStatement;
	static function getCreateTableStatementStatic():CreateTableStatement;
	function createTable(mysqli $mysqli):int;
	static function createTableStatic(mysqli $mysqli):int;
	function beforeCreateTableHook(mysqli $mysqli): int;
	function afterCreateTableHook(mysqli $mysqli): int;
	static function tableExistsStatic(mysqli $mysqli): bool;
	function tableExists(mysqli $mysqli):bool;
	static function hasColumnStatic(string $column_name): bool;
	static function reconfigureColumns(array &$columns, ?DataStructure $ds = null):void;
	static function getDatumClassStatic(string $column_name):string;
	static function getTypeSpecifierStatic(...$column_names): string;
	function getTypeSpecifier(...$column_names): string;
	function select(...$column_names): SelectStatement;
	static function selectStatic(?TableDefinitionInterface $that=null, ...$column_names): SelectStatement;
	static function getColumnStatic(string $column_name): Datum;
	function createAssociatedTables(mysqli $mysqli): int;
	static function getCompositeUniqueColumnNames(): ?array;
	function setIdentifierName(?string $idn): ?string;
	function hasIdentifierName():bool;
	function getIdentifierName(): ?string;
	static function getIdentifierNameStatic():?string;
	function getIdentifierNameCommand(): GetIdentifierNameCommand;
	static function getReorderedColumnIndices(): ?array;
}
