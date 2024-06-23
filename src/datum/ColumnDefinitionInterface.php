<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;

interface ColumnDefinitionInterface{
	
	static function getTypeSpecifier():string;
	static function parseString(string $string);
	function getColumnTypeString():string;
	static function validateStatic($value):int;
	function setGeneratedAlwaysAsExpression($expression);
	function hasGeneratedAlwaysAsExpression():bool;
	function getGeneratedAlwaysAsExpression();
	function generatedAlwaysAs($expression);
	function setColumnFormat($type);
	function hasColumnFormat():bool;
	function getColumnFormat();
	function columnFormat($type);
	function setDatabaseStorage($type);
	function hasDatabaseStorage():bool;
	function getDatabaseStorage();
	function databaseStorage($type);
	function withDefaultValue($value);
	function generateIndexDefinition();
	function toSQL():string;
	function getPrimaryKeyFlag():bool;
	function setReferenceColumn(?ForeignKeyDatum $column):?ForeignKeyDatum;
	function hasReferenceColumn():bool;
	function getReferenceColumn():ForeignKeyDatum;
	function setReferenceColumnName(?string $rcn):?string;
	function hasReferenceColumnName():bool;
	function getReferenceColumnName():string;
	function setIndexFlag(bool $value = true):bool;
	function getIndexFlag():bool;
	function index(bool $value = true):Datum;
	function validate($v):int;
	static function getDatabaseEncodedValueStatic($value);
	function cast($v);
}