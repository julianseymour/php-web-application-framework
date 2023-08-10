<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\select;

interface SelectStatementInterface
{

	function setSelectStatement(?SelectStatement $obj): ?SelectStatement;

	function hasSelectStatement(): bool;

	function getSelectStatement(): SelectStatement;
}