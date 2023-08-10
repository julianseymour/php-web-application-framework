<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\column\UniqueFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\TableNameTrait;

class CreateIndexStatement extends QueryStatement
{

	use IndexDefiningTrait;
	use TableNameTrait;
	use UniqueFlagBearingTrait;

	public function __construct($indexDefinition = null)
	{
		parent::__construct();
		if (isset($indexDefinition)) {
			$this->setIndexDefinition($indexDefinition);
		}
	}

	public function getQueryStatementString()
	{
		$index = $this->getIndexDefinition();
		// CREATE [UNIQUE | FULLTEXT | SPATIAL] INDEX index_name [index_type]
		$string = "create ";
		if ($this->getUniqueFlag()) {
			$string .= "unique ";
		}
		$string .= $index->getIndexDefinitionString();
		// ON tbl_name (key_part,...)
		$string .= " on ";
		if ($this->hasDatabaseName()) {
			$string .= back_quote($this->getDatabaseName()) . ".";
		}
		$string .= back_quote($this->getTableName());
		$string .= $index->getIndexOptionsString();
		return $string;
		// [index_option]
		// [algorithm_option | lock_option] ...

		// key_part: {col_name [(length)] | (expr)} [ASC | DESC]

		// index_option: {
		// KEY_BLOCK_SIZE [=] value
		// | index_type
		// | WITH PARSER parser_name
		// | COMMENT 'string'
		// | {VISIBLE | INVISIBLE}
		// | ENGINE_ATTRIBUTE [=] 'string'
		// | SECONDARY_ENGINE_ATTRIBUTE [=] 'string'
		// }

		// index_type:
		// USING {BTREE | HASH}

		// algorithm_option:
		// ALGORITHM [=] {DEFAULT | INPLACE | COPY}

		// lock_option:
		// LOCK [=] {DEFAULT | NONE | SHARED | EXCLUSIVE}
	}
}