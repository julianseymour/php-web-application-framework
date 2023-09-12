<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\CharacterSetTrait;
use JulianSeymour\PHPWebApplicationFramework\query\CollatedTrait;
use JulianSeymour\PHPWebApplicationFramework\query\EncryptionOptionTrait;
use JulianSeymour\PHPWebApplicationFramework\query\KeyBlockSizeTrait;
use JulianSeymour\PHPWebApplicationFramework\query\SecondaryEngineAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\query\tablespace\AutoextendSizeTrait;
use Exception;

class TableOptions extends AbstractTableOptions
{

	use AutoextendSizeTrait;
	use CharacterSetTrait;
	use CollatedTrait;
	use EncryptionOptionTrait;
	use KeyBlockSizeTrait;
	use MultipleTableNamesTrait;
	use SecondaryEngineAttributeTrait;

	protected $autoIncrementValue;

	protected $autoRecalculateStatsValue;

	protected $averageRowLengthValue;

	protected $checksumValue;

	protected $compressionType;

	protected $connectionString;

	protected $delayKeyWriteValue;

	protected $insertMethodType;

	protected $packKeysValue;

	protected $passwordString;

	protected $persistentStatsValue;

	protected $rowFormatType;

	protected $statsSamplePagesValue;

	protected $tablespaceStorageType;

	public function __construct()
	{
		parent::__construct();
		$this->requirePropertyType('tableNames', "table");
	}

	public function dispose(): void
	{
		unset($this->properties);
		unset($this->propertyTypes);
		unset($this->autoIncrementValue);
		unset($this->autoRecalculateStatsValue);
		unset($this->averageRowLengthValue);
		unset($this->characterSet);
		unset($this->checksumValue);
		unset($this->collationName);
		unset($this->compressionType);
		unset($this->delayKeyWriteValue);
		unset($this->encryptionOption);
		unset($this->engineAttributeString);
		unset($this->insertMethodType);
		unset($this->packKeysValue);
		unset($this->persistentStatsValue);
		unset($this->rowFormatType);
		unset($this->secondaryEngineAttributeString);
		unset($this->statsSamplePagesValue);
		unset($this->tablespaceStorageType);
		unset($this->unionTableNames);
	}

	public function setChecksum($value)
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->setChecksum()";
		if($value === null) {
			unset($this->checksumValue);
			return null;
		}elseif(is_bool($value)) {
			if($value) {
				$value = 1;
			}else{
				$value = 0;
			}
		}elseif(!is_int($value)) {
			Debug::error("{$f} invalid non-integer value");
		}elseif($value !== 0 && $value !== 1) {
			Debug::error("{$f} invalid integer value \"{$value}\"");
		}
		return $this->checksumValue = $value;
	}

	public function hasChecksum()
	{
		return isset($this->checksumValue);
	}

	public function getChecksum()
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->getChecksum()";
		if(!$this->hasChecksum()) {
			Debug::error("{$f} checksum is undefined");
		}
		return $this->checksumValue;
	}

	public function checksum($value)
	{
		$this->setChecksum($value);
		return $this;
	}

	public function setDelayKeyWrite($value)
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->setDelayKeyWrite()";
		if($value === null) {
			unset($this->delayKeyWriteValue);
			return null;
		}elseif(is_bool($value)) {
			if($value) {
				$value = 1;
			}else{
				$value = 0;
			}
		}elseif(!is_int($value)) {
			Debug::error("{$f} invalid non-integer value");
		}elseif($value !== 0 && $value !== 1) {
			Debug::error("{$f} invalid integer value \"{$value}\"");
		}
		return $this->delayKeyWriteValue = $value;
	}

	public function hasDelayKeyWrite()
	{
		return isset($this->delayKeyWrite);
	}

	public function getDelayKeyWrite()
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->getDelayKeyWrite()";
		if(!$this->hasDelayKeyWrite()) {
			Debug::error("{$f} delay key write is undefined");
		}
		return $this->delayKeyWriteValue;
	}

	public function delayKeyWrite($value)
	{
		$this->setDelayKeyWrite($value);
		return $this;
	}

	public function setAutoIncrement($value)
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->setAutoIncrement()";
		if(!is_int($value)) {
			Debug::error("{$f} this function accepts integers only");
		}
		return $this->autoIncrementValue = $value;
	}

	public function hasAutoIncrement()
	{
		return isset($this->autoIncrementValue);
	}

	public function getAutoIncrementFlag()
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->getAutoIncrementFlag()";
		if(!$this->hasAutoIncrement()) {
			Debug::error("{$f} autoincrement is undefined");
		}
		return $this->autoIncrementValue;
	}

	public function autoIncrement($value)
	{
		$this->setAutoIncrement($value);
		return $this;
	}

	public function setAverageRowLength($value)
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->setAverageRowLength()";
		if(!is_int($value)) {
			Debug::error("{$f} this function accepts integers only");
		}
		return $this->averageRowLengthValue = $value;
	}

	public function hasAverageRowLength()
	{
		return isset($this->averageRowLengthValue);
	}

	public function getAverageRowLength()
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->getAverageRowLength()";
		if(!$this->hasAverageRowLength()) {
			Debug::error("{$f} average row length is undefined");
		}
		return $this->averageRowLengthValue;
	}

	public function averageRowLength($value)
	{
		$this->setAverageRowLength($value);
		return $this;
	}

	public function setCompression($type)
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->setCompression()";
		if($type == null) {
			unset($this->compressionType);
			return null;
		}elseif(!is_string($type)) {
			Debug::error("{$f} input parameter is not a string");
		}
		$type = strtolower($type);
		switch ($type) {
			case COMPRESSION_TYPE_LZ4:
			case COMPRESSION_TYPE_NONE:
			case COMPRESSION_TYPE_ZLIB:
				break;
			default:
				Debug::error("{$f} invalid compression type \"{$type}\"");
		}
		return $this->compressionType = $type;
	}

	public function hasCompression()
	{
		return isset($this->compressionType);
	}

	public function getCompression()
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->getCompression()";
		if(!$this->hasCompression()) {
			Debug::error("{$f} compression is undefined");
		}
		return $this->compressionType;
	}

	public function compression($type)
	{
		$this->setCompression($type);
		return $this;
	}

	public function setConnection($string)
	{
		return $this->connectionString = $string;
	}

	public function hasConnection()
	{
		return isset($this->connectionString);
	}

	public function getConnection()
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->getConnection()";
		if(!$this->hasConnection()) {
			Debug::error("{$f} connection is undefined");
		}
		return $this->connectionString;
	}

	public function connection($string)
	{
		$this->setConnection($string);
		return $this;
	}

	public function setInsertMethod($type)
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->setInsertMethod()";
		if($type == null) {
			unset($this->insertMethodType);
			return null;
		}elseif(!is_string($type)) {
			Debug::error("{$f} input parameter is not a string");
		}
		$type = strtolower($type);
		switch ($type) {
			case INSERT_METHOD_FIRST:
			case INSERT_METHOD_LAST:
			case INSERT_METHOD_NO:
				break;
			default:
				Debug::error("{$f} invalid insert method \"{$type}\"");
		}
		return $this->insertMethodType = $type;
	}

	public function hasInsertMethod()
	{
		return isset($this->insertMethodType);
	}

	public function getInsertMethod()
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->getInsertMethod()";
		if(!$this->hasInsertMethod()) {
			Debug::error("{$f} insert method is undefined");
		}
		return $this->insertMethodType;
	}

	public function insertMethod($type)
	{
		$this->setInsertMethod($type);
		return $this;
	}

	public function setPackKeys($value)
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->setPackKeys()";
		if(is_string($value)) {
			$value = strtolower($value);
			if($value !== CONST_DEFAULT) {
				Debug::error("{$f} invalid string value \"{$value}\"");
			}
		}elseif(is_int($value)) {
			if($value !== 0 && $value !== 1) {
				Debug::error("{$f} invalid integer value \"{$value}\"");
			}
		}else{
			Debug::error("{$f} this function only accepts 0, 1 and default as its input parameter");
		}
		return $this->packKeysValue = $value;
	}

	public function hasPackKeys()
	{
		return isset($this->packKeysValue);
	}

	public function getPackKeys()
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->getPackKeys()";
		if(!$this->hasPackKeys()) {
			Debug::error("{$f} pack keys is undefined");
		}
		return $this->packKeysValue;
	}

	public function packKeys($string)
	{
		$this->setPackKeys($string);
		return $this;
	}

	public function setPassword($string)
	{
		return $this->passwordString = $string;
	}

	public function hasPassword()
	{
		return isset($this->passwordString) && is_string($this->passwordString) && ! empty($this->passwordString);
	}

	public function getPassword()
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->getPassword()";
		if(!$this->hasPassword()) {
			Debug::error("{$f} password is undefined");
		}
		return $this->passwordString;
	}

	public function password($string)
	{
		$this->setPassword($string);
		return $this;
	}

	public function setRowFormat($type)
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->setRowFormat()";
		if($type == null) {
			unset($this->rowFormatType);
			return null;
		}elseif(!is_string($type)) {
			Debug::error("{$f} input parameter is not a string");
		}
		$type = strtolower($type);
		switch ($type) {
			case ROW_FORMAT_COMPACT:
			case ROW_FORMAT_COMPRESSED:
			case ROW_FORMAT_DEFAULT:
			case ROW_FORMAT_DYNAMIC:
			case ROW_FORMAT_FIXED:
			case ROW_FORMAT_REDUNDANT:
				break;
			default:
				Debug::error("{$f} invalid row format type \"{$type}\"");
		}
		return $this->rowFormatType = $type;
	}

	public function hasRowFormat()
	{
		return isset($this->rowFormatType);
	}

	public function getRowFormat()
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->getRowFormat()";
		if(!$this->hasRowFormat()) {
			Debug::error("{$f} row format is undefined");
		}
		return $this->rowFormatType;
	}

	public function rowFormat($type)
	{
		$this->setRowFormat($type);
		return $this;
	}

	public function setAutoRecalculateStats($value)
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->setAutorecalculateStats()";
		if(is_string($value)) {
			$value = strtolower($value);
			if($value !== CONST_DEFAULT) {
				Debug::error("{$f} invalid string value \"{$value}\"");
			}
		}elseif(is_int($value)) {
			if($value !== 0 && $value !== 1) {
				Debug::error("{$f} invalid integer value \"{$value}\"");
			}
		}else{
			Debug::error("{$f} this function only accepts 0, 1 and default as its input parameter");
		}
		return $this->autoRecalculateStatsValue = $value;
	}

	public function hasAutoRecalculateStats()
	{
		return isset($this->autoRecalculateStatsValue);
	}

	public function getAutoRecalculateStats()
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->getAutoRecalculateStats()";
		if(!$this->hasAutoRecalculateStats()) {
			Debug::error("{$f} autorecalculate status is undefined");
		}
		return $this->autoRecalculateStatsValue;
	}

	public function autoRecalculateStats($value)
	{
		$this->setAutoRecalculateStats($value);
		return $this;
	}

	public function setPersistentStats($value)
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->setPersistentStats()";
		if(is_string($value)) {
			$value = strtolower($value);
			if($value !== CONST_DEFAULT) {
				Debug::error("{$f} invalid string value \"{$value}\"");
			}
		}elseif(is_int($value)) {
			if($value !== 0 && $value !== 1) {
				Debug::error("{$f} invalid integer value \"{$value}\"");
			}
		}else{
			Debug::error("{$f} this function only accepts 0, 1 and default as its input parameter");
		}
		return $this->persistentStatsValue = $value;
	}

	public function hasPersistentStats()
	{
		return isset($this->persistentStatsValue);
	}

	public function getPersistentStats()
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->getPersistentStatss()";
		if(!$this->hasPersistentStats()) {
			Debug::error("{$f} persistent stats is undefined");
		}
		return $this->persistentStatsValue;
	}

	public function persistentStats($value)
	{
		$this->setPersistentStats($value);
		return $this;
	}

	public function setStatsSamplePages($value)
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->setStatsSamplePages()";
		if(!is_int($value)) {
			Debug::error("{$f} this function accepts integers only");
		}
		return $this->statsSamplePagesValue = $value;
	}

	public function hasStatsSamplePages()
	{
		return isset($this->statsSamplePagesValue);
	}

	public function getStatsSamplePages()
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->getStatsSamplePages()";
		if(!$this->hasStatsSamplePages()) {
			Debug::error("{$f} stats sample pages is undefined");
		}
		return $this->statsSamplePagesValue;
	}

	public function statsSamplePages($value)
	{
		return $this->setStatsSamplePages($value);
		return $this;
	}

	public function union($unionTableNames)
	{
		$this->setTableNames($unionTableNames);
		return $this;
	}

	public function setTablespaceStorage($type)
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->setTableSpaceStorage()";
		if($type == null) {
			unset($this->tablespaceStorageType);
			return null;
		}elseif(!is_string($type)) {
			Debug::error("{$f} input parameter is not a string");
		}
		$type = strtolower($type);
		switch ($type) {
			case DATABASE_STORAGE_DISK:
			case DATABASE_STORAGE_MEMORY:
				break;
			default:
				Debug::error("{$f} invalid tablespace storage type \"{$type}\"");
		}
		return $this->tablespaceStorageType = $type;
	}

	public function hasTablespaceStorage()
	{
		return isset($this->tablespaceStorageType);
	}

	public function getTablespaceStorage()
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->getTablespaceStorage()";
		if(!$this->hasTablespaceStorage()) {
			Debug::error("{$f} tablespace storage type is undefined");
		}
		return $this->tablespaceStorageType;
	}

	public function tablespace($name, $type = null)
	{
		if($type !== null) {
			$this->setTablespaceStorage($type);
		}
		return parent::tablespace($name);
	}

	public function toSQL(): string
	{
		$f = __METHOD__; //TableOptions::getShortClass()."(".static::getShortClass().")->toSQL()";
		try{
			$string = "";
			// AUTOEXTEND_SIZE [=] value
			if($this->hasAutoextendSize()) {
				$string .= " autoextend_size " . $this->getAutoextendSize();
			}
			// AUTO_INCREMENT [=] value
			if($this->hasAutoIncrement()) {
				$string .= " auto_increment " . $this->getAutoIncrementFlag();
			}
			// AVG_ROW_LENGTH [=] value
			if($this->hasAverageRowLength()) {
				$string .= " avg_row_length " . $this->getAverageRowLength();
			}
			// [DEFAULT] CHARACTER SET [=] charset_name
			if($this->hasCharacterSet()) {
				$string .= " character set " . $this->getCharacterSet();
			}
			// [DEFAULT] COLLATE [=] collation_name
			if($this->hasCollationName()) {
				$string .= " collate " . $this->getCollationName();
			}
			// CHECKSUM [=] {0 | 1}
			if($this->hasChecksum()) {
				$string .= " checksum " . $this->getChecksum();
			}
			// COMMENT [=] 'string'
			if($this->hasComment()) {
				$string .= " comment " . $this->getComment();
			}
			// COMPRESSION [=] {'ZLIB' | 'LZ4' | 'NONE'}
			if($this->hasCompression()) {
				$string .= " compresion '" . $this->getCompression() . "'";
			}
			// CONNECTION [=] 'connect_string'
			if($this->hasConnection()) {
				$string .= " connection '" . escape_quotes($this->getConnection(), QUOTE_STYLE_SINGLE) . "'";
			}
			// {DATA | INDEX} DIRECTORY [=] 'absolute path to directory'
			if($this->hasDataDirectoryName()) {
				$string .= " data directory " . escape_quotes($this->getDataDirectoryName(), QUOTE_STYLE_SINGLE) . "'";
			}
			if($this->hasIndexDirectoryName()) {
				$string .= " index directory " . escape_quotes($this->getIndexDirectoryName(), QUOTE_STYLE_SINGLE) . "'";
			}
			// DELAY_KEY_WRITE [=] {0 | 1}
			if($this->hasDelayKeyWrite()) {
				$string .= " delay_key_write " . $this->getDelayKeyWrite();
			}
			// ENCRYPTION [=] {'Y' | 'N'}
			if($this->hasEncryption()) {
				$string .= " encryption '" . $this->getEncryption() . "'";
			}
			// ENGINE [=] engine_name
			if($this->hasStorageEngineName()) {
				$string .= " engine " . $this->getStorageEngineName();
			}
			// ENGINE_ATTRIBUTE [=] 'string'
			if($this->hasEngineAttribute()) {
				$string .= " engine_attribute '" . escape_quotes($this->getEngineAttribute(), QUOTE_STYLE_SINGLE) . "'";
			}
			// INSERT_METHOD [=] { NO | FIRST | LAST }
			if($this->hasInsertMethod()) {
				$string .= " insert_method " . $this->getInsertMethod();
			}
			// KEY_BLOCK_SIZE [=] value
			if($this->hasKeyBlockSize()) {
				$string .= " key_block_size " . $this->getKeyBlockSize();
			}
			// MAX_ROWS [=] value
			if($this->hasMaximumRowCount()) {
				$string .= " max_rows " . $this->getMaximumRowCount();
			}
			// MIN_ROWS [=] value
			if($this->hasMinimumRowCount()) {
				$string .= " min_rows " . $this->getMinimumRowCount();
			}
			// PACK_KEYS [=] {0 | 1 | DEFAULT}
			if($this->getPackKeys()) {
				$string .= " pack_keys " . $this->getPackKeys();
			}
			// PASSWORD [=] 'string'
			if($this->hasPassword()) {
				$string .= " password '" . escape_quotes($this->getPassword(), QUOTE_STYLE_SINGLE) . "'";
			}
			// ROW_FORMAT [=] {DEFAULT | DYNAMIC | FIXED | COMPRESSED | REDUNDANT | COMPACT}
			if($this->hasRowFormat()) {
				$string .= " row_format " . $this->getRowFormat();
			}
			// SECONDARY_ENGINE_ATTRIBUTE [=] 'string'
			if($this->hasSecondaryEngineAttribute()) {
				$secondary = escape_quotes($this->getSecondaryEngineAttribute(), QUOTE_STYLE_SINGLE);
				$string .= " secondary_engine_attribute '{$secondary}'";
			}
			// STATS_AUTO_RECALC [=] {DEFAULT | 0 | 1}
			if($this->hasAutoRecalculateStats()) {
				$string .= " stats_auto_recalc " . $this->getAutoRecalculateStats();
			}
			// STATS_PERSISTENT [=] {DEFAULT | 0 | 1}
			if($this->hasPersistentStats()) {
				$string .= " stats_persistent " . $this->getPersistentStats();
			}
			// STATS_SAMPLE_PAGES [=] value
			if($this->hasStatsSamplePages()) {
				$string .= " stats_sample_pages " . $this->getStatsSamplePages();
			}
			// TABLESPACE tablespace_name [STORAGE {DISK | MEMORY}]
			if($this->hasTablespaceName()) {
				$string .= " tablespace " . $this->getTablespaceName();
				if($this->hasTablespaceStorage()) {
					$string . " storage " . $this->getTablespaceStorage();
				}
			}
			// UNION [=] (tbl_name[,tbl_name]...)
			if($this->hasTableNames()) {
				$string .= " union " . implode(',', $this->getTableNames());
			}
			return $string;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
