<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayKeyProviderInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\CommentTrait;
use JulianSeymour\PHPWebApplicationFramework\query\KeyBlockSizeTrait;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\SecondaryEngineAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\PrimaryKeyFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\VisibilityTrait;
use Exception;

class IndexDefinition extends Basic implements ArrayKeyProviderInterface, SQLInterface
{

	use CommentTrait;
	use IndexNameTrait;
	use IndexTypeTrait;
	use KeyBlockSizeTrait;
	use KeyPartsTrait;
	use PrimaryKeyFlagBearingTrait;
	use SecondaryEngineAttributeTrait;
	use VisibilityTrait;

	protected $parserName;

	public function __construct($indexName)
	{
		parent::__construct();
		$this->requirePropertyType("keyParts", KeyPart::class);
		$this->setIndexName($indexName);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->comment);
		unset($this->engineAttributeString);
		unset($this->secondaryEngineAttributeString);
		unset($this->indexName);
		unset($this->indexType);
		unset($this->keyBlockSizeValue);
		unset($this->keyParts);
		unset($this->parserName);
		unset($this->visibility);
	}

	public function getIndexDefinitionString()
	{
		$string = "";
		if($this->hasIndexType()) {
			$index_type = $this->getIndexType();
			if($index_type === INDEX_TYPE_FULLTEXT || $index_type === INDEX_TYPE_SPATIAL) {
				$string = "{$index_type} ";
				if($this->hasIndexName() && ! $this->getHideIndexNameFlag()) {
					$string .= back_quote($this->getIndexName()) . " ";
				}
			}else{
				if(!$this->isPrimaryKey()) {
					$string = "index ";
				}
				if($this->hasIndexName() && ! $this->getHideIndexNameFlag()) {
					$string .= back_quote($this->getIndexName()) . " ";
				}
				$string .= "using {$index_type} ";
			}
		}else{
			if(!$this->isPrimaryKey()) {
				$string = "index ";
			}
			if($this->hasIndexName() && ! $this->getHideIndexNameFlag()) {
				$string .= back_quote($this->getIndexName()) . " ";
			}
		}
		return $string;
	}

	public function getIndexOptionsString()
	{
		$f = __METHOD__; //IndexDefinition::getShortClass()."(".static::getShortClass().")->getIndexOptionsString()";
		$string = " (";
		$i = 0;
		foreach($this->getKeyParts() as $keypart) {
			if($i ++ > 0) {
				$string .= ",";
			}
			if($keypart instanceof SQLInterface) {
				$keypart = $keypart->toSQL();
			}elseif(is_string($keypart)) {
				$keypart = back_quote($keypart);
			}else{
				Debug::error("{$f} keypart \"{$keypart}\" is neither string nor SQLInterface");
			}
			$string .= $keypart;
		}
		$string .= ")";
		if($this->hasKeyBlockSize()) {
			// For MyISAM tables, KEY_BLOCK_SIZE optionally specifies the size in bytes to use for index key blocks. The value is treated as a hint; a different size could be used if necessary. A KEY_BLOCK_SIZE value specified for an individual index definition overrides a table-level KEY_BLOCK_SIZE value.
			// KEY_BLOCK_SIZE is not supported at the index level for InnoDB tables. See Section 13.1.20, “CREATE TABLE Statement”.
			$string .= " key block size " . $this->getKeyBlockSize();
		}
		if($this->hasIndexType() && $this->getIndexType() === INDEX_TYPE_FULLTEXT && $this->hasParserName()) {
			// This option can be used only with FULLTEXT indexes. It associates a parser plugin with the index if full-text indexing and searching operations need special handling. InnoDB and MyISAM support full-text parser plugins. If you have a MyISAM table with an associated full-text parser plugin, you can convert the table to InnoDB using ALTER TABLE. See Full-Text Parser Plugins and Writing Full-Text Parser Plugins for more information.
			$string .= " parser name " . $this->getParserName();
		}
		if($this->hasComment()) {
			$string .= " comment " . single_quote($this->getComment());
		}
		if($this->hasVisibility()) { // Specify index visibility. Indexes are visible by default. An invisible index is not used by the optimizer. Specification of index visibility applies to indexes other than primary keys (either explicit or implicit). For more information, see Section 8.3.12, “Invisible Indexes”.
			$string .= " " . $this->getVisibility();
		}
		if($this->hasEngineAttribute()) {
			$string .= " engine attribute " . single_quote($this->getEngineAttribute());
			if($this->hasSecondaryEngineAttribute()) {
				$string .= " secondary engine attribute " . single_quote($this->getSecondaryEngineAttribute());
			}
		}
		return $string;
	}

	public function toSQL(): string
	{
		$f = __METHOD__; //IndexDefinition::getShortClass()."(".static::getShortClass().")->__toString()";
		try{

			// from alter table:
			// {INDEX | KEY} [index_name] [index_type] (key_part,...) [index_option] ...
			// {FULLTEXT | SPATIAL} [INDEX | KEY] [index_name] (key_part,...) [index_option] ...
			// [CONSTRAINT [symbol]] PRIMARY KEY [index_type] (key_part,...) [index_option] ...
			// [CONSTRAINT [symbol]] UNIQUE [INDEX | KEY] [index_name] [index_type] (key_part,...) [index_option] ...
			// [CONSTRAINT [symbol]] FOREIGN KEY [index_name] (col_name,...) reference_definition

			// from create table:
			// {INDEX | KEY} [index_name] [index_type] (key_part,...) [index_option] ...
			// {FULLTEXT | SPATIAL} [INDEX | KEY] [index_name] (key_part,...) [index_option] ...
			// [CONSTRAINT [symbol]] PRIMARY KEY [index_type] (key_part,...) [index_option] ...
			// [CONSTRAINT [symbol]] UNIQUE [INDEX | KEY] [index_name] [index_type] (key_part,...) [index_option] ...
			// [CONSTRAINT [symbol]] FOREIGN KEY [index_name] (col_name,...) reference_definition
			// reference_definition:
			// REFERENCES tbl_name (key_part,...) [MATCH FULL | MATCH PARTIAL | MATCH SIMPLE] [ON DELETE ref_opt] [ON UPDATE ref_opt]
			// reference_option:
			// RESTRICT | CASCADE | SET NULL | NO ACTION | SET DEFAULT
			return $this->getIndexDefinitionString() . $this->getIndexOptionsString();
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"hideIndexName",
			COLUMN_FILTER_PRIMARY_KEY
		]);
	}

	public function setHideIndexNameFlag($value = true)
	{
		return $this->setFlag("hideIndexName", $value);
	}

	public function getHideIndexNameFlag()
	{
		return $this->getFlag("hideIndexName");
	}

	public function setParserName($name)
	{
		$f = __METHOD__; //"IndexDefiningTrait(".static::getShortClass().")->setParserName()";
		if($this->hasIndexType() && $this->getIndexType() !== INDEX_TYPE_FULLTEXT) {
			Debug::error("{$f} parser name is supported only by fulltext indexes");
		}
		return $this->parserName = $name;
	}

	public function hasParserName()
	{
		return isset($this->parserName);
	}

	public function getParserName()
	{
		$f = __METHOD__; //IndexDefinition::getShortClass()."(".static::getShortClass().")->getParserName()";
		if(!$this->hasParserName()) {
			Debug::error("{$f} parser name is undefined");
		}
		return $this->parserName;
	}

	public function getArrayKey(int $count)
	{
		return $this->getIndexName();
	}
}
