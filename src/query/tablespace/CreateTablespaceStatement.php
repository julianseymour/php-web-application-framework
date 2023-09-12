<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\tablespace;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\hasMinimumMySQLVersion;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\CommentTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\CreateTableStatement;

class CreateTablespaceStatement extends DefineTablespaceStatement
{

	use CommentTrait;

	protected $extentSizeValue;

	protected $fileBlockSizeValue;

	protected $logfileGroup;

	protected $maxSizeValue;

	// MAX_SIZE: Currently ignored by MySQL; reserved for possible future use. Has no effect in any release of MySQL 8.0 or MySQL NDB Cluster 8.0, regardless of the storage engine used.
	protected $nodegroupId;

	public function dispose(): void
	{
		parent::dispose();
		unset($this->commentString);
		unset($this->extentSizeValue);
		unset($this->fileBlockSizeValue);
		unset($this->logfileGroup);
		unset($this->maxSizeValue);
		unset($this->nodegroupId);
	}

	public function setFileBlockSize($value)
	{
		$f = __METHOD__; //CreateTableStatement::getShortClass()."(".static::getShortClass().")->setFileBlockSize()";
		if($value == null) {
			unset($this->fileBlockSizeValue);
			return null;
		}elseif(is_string($value)) {
			if(! preg_match('/^[1-9]+[0-9]*[Kk]?/', $value)) { // Values can be specified in bytes or kilobytes. For example, an 8 kilobyte file block size can be specified as 8192 or 8K.
				Debug::error("{$f} patch mismatch");
			}
		}elseif(!is_int($value)) {
			Debug::error("{$f} file block size must be a positive integer");
		}elseif($value < 1) {
			Debug::error("{$f} file block size must be positive");
		}
		return $this->fileBlockSizeValue = $value;
	}

	public function hasFileBlockSize()
	{
		return isset($this->fileBlockSizeValue);
	}

	public function getFileBlockSize()
	{
		$f = __METHOD__; //CreateTablespaceStatement::getShortClass()."(".static::getShortClass().")->getFileBlockSize()";
		if(!$this->hasFileBlockSize()) {
			Debug::error("{$f} file block size value is undefined");
		}
		return $this->fileBlockSizeValue;
	}

	public function fileBlockSize($value)
	{
		$this->setFileBlockSize($value);
		return $this;
	}

	public function setLogfileGroup($group)
	{
		$f = __METHOD__; //CreateTablespaceStatement::getShortClass()."(".static::getShortClass().")->setLogfileGroup()";
		if($group == null) {
			unset($this->logfileGroup);
			return null;
		}elseif(!is_string($group)) {
			Debug::error("{$f} logfile group is undefined");
		}
		return $this->logfileGroup = $group;
	}

	public function hasLogfileGroup()
	{
		return isset($this->logfileGroup);
	}

	public function getLogfileGroup()
	{
		$f = __METHOD__; //CreateTablespaceStatement::getShortClass()."(".static::getShortClass().")->getLogfileGroup()";
		if(!$this->hasLogfileGroup()) {
			Debug::error("{$f} logfile group is undefined");
		}
		return $this->logfileGroup;
	}

	public function useLogfileGroup($group)
	{
		$this->setLogfileGroup($group);
		return $this;
	}

	public function setExtentSize($value)
	{
		$f = __METHOD__; //CreateTablespaceStatement::getShortClass()."(".static::getShortClass().")->setExtentSize()";
		if($value == null) {
			unset($this->extentSizeValue);
			return null;
		}elseif(is_string($value)) {
			if(! preg_match('/^[1-9]+[0-9]*[TtGgMmKk]?/', $value)) { // When setting EXTENT_SIZE or INITIAL_SIZE, you may optionally follow the number with a one-letter abbreviation for an order of magnitude, similar to those used in my.cnf. Generally, this is one of the letters M (for megabytes) or G (for gigabytes).
				Debug::error("{$f} pattern mismatch");
			}
		}elseif(!is_int($value)) {
			Debug::error("{$f} extent size must be a positive integer");
		}elseif($value < 0) { // EXTENT_SIZE is rounded up to the nearest whole multiple of 32K
			Debug::error("{$f} extent size must be positive");
		}
		return $this->extentSizeValue = $value;
	}

	public function hasExtentSize()
	{
		return isset($this->extentSizeValue);
	}

	public function getExtentSize()
	{
		$f = __METHOD__; //CreateTablespaceStatement::getShortClass()."(".static::getShortClass().")->getExtentSize()";
		if(!$this->hasExtentSize()) {
			Debug::error("{$f} extent size is undefined");
		}
		return $this->extentSizeValue;
	}

	public function extentSize($value)
	{
		$this->setExtentSize($value);
		return $this;
	}

	public function setMaxSize($value)
	{
		$f = __METHOD__; //CreateTablespaceStatement::getShortClass()."(".static::getShortClass().")->setMaxSize()";
		if($value !== 0 && $value == null) {
			unset($this->maxSizeValue);
			return null;
		}elseif(is_string($value)) {
			if(! preg_match('/^[1-9]+[0-9]*[TtGgMmKk]?/', $value)) {
				Debug::error("{$f} pattern mismatch");
			}
		}elseif(!is_int($value)) {
			Debug::error("{$f} max size must be a positive integer");
		}elseif($value < 0) {
			Debug::error("{$f} max size must be positive");
		}elseif($this->hasInitialSize() && $value < $this->getInitialSize()) {
			Debug::error("{$f} max size cannot exceed initial size");
		}
		return $this->maxSizeValue = $value;
	}

	public function hasMaxSize()
	{
		return isset($this->maxSizeValue);
	}

	public function getMaxSize()
	{
		$f = __METHOD__; //CreateTablespaceStatement::getShortClass()."(".static::getShortClass().")->getMaxSize()";
		if(!$this->hasMaxSize()) {
			Debug::error("{$f} initial size is undefined");
		}
		return $this->maxSizeValue;
	}

	public function maxSize($value)
	{
		$this->setMaxSize($value);
		return $this;
	}

	public function setNodegroup($id)
	{
		$f = __METHOD__; //CreateTablespaceStatement::getShortClass()."(".static::getShortClass().")->setNodegroup()";
		if($id == null) {
			unset($this->nodegroupId);
			return null;
		}elseif(!is_string($id)) {
			Debug::error("{$f} nodegroup ID must be a string");
		}
		return $this->nodegroupId = $id;
	}

	public function hasNodegroup()
	{
		return isset($this->nodegroupId);
	}

	public function getNodegroup()
	{
		$f = __METHOD__; //CreateTablespaceStatement::getShortClass()."(".static::getShortClass().")->getNodegroup()";
		if(!$this->hasNodegroup()) {
			Debug::error("{$f} nodegroup ID is undefined");
		}
		return $this->nodegroupId;
	}

	public function nodegroup($id)
	{
		$this->setNodegroup($id);
		return $this;
	}

	public function getQueryStatementString()
	{
		// CREATE
		$string = "create ";
		// [UNDO]
		if($this->getUndoFlag() && hasMinimumMySQLVersion("8.0.14")) {
			$string .= "undo ";
		}
		// TABLESPACE tablespace_name
		$string .= "tablespace '" . escape_quotes($this->getTablespaceName(), QUOTE_STYLE_SINGLE) . "'";
		$engine = $this->getStorageEngine();
		if($engine === STORAGE_ENGINE_INNODB || $engine === STORAGE_ENGINE_NDB) { // InnoDB and NDB:
		                                                                           // [ADD DATAFILE 'file_name']
			if($this->hasDatafilename()) {
				$dfn = escape_quotes($this->getDatafilename(), QUOTE_STYLE_SINGLE);
				$string .= " add datafile '{$dfn}'";
			}
			// [AUTOEXTEND_SIZE [=] value]
			if($this->hasAutoextendSize()) {
				$string .= " autoextend_size " . $this->getAutoextendSize();
			}
			if($engine === STORAGE_ENGINE_INNODB) { // InnoDB only:
			                                         // [FILE_BLOCK_SIZE = value]
				if($this->hasFileBlockSizeValue()) {
					$string .= " file_block_size " . $this->getFileBlockSize();
				}
				// [ENCRYPTION [=] {'Y' | 'N'}]
				if($this->hasEncryption()) {
					$string .= " encryption '" . $this->getEncryption() . "'";
				}
			}elseif($engine === STORAGE_ENGINE_NDB) { // NDB only:
			                                            // USE LOGFILE GROUP logfile_group
				if($this->hasLogfileGroup()) {
					$string .= " use logfile group " . $this->getLogfileGroup();
				}
				// [EXTENT_SIZE [=] extent_size]
				if($this->hasExtentSize()) {
					$string .= " extent_size " . $this->getExtentSize();
				}
				// [INITIAL_SIZE [=] initial_size]
				if($this->hasInitialSize()) {
					$string .= " initial_size " . $this->getInitialSize();
				}
				// [MAX_SIZE [=] max_size]
				if($this->hasMaxSize()) {
					$string .= " max_size " . $this->getMaxSize();
				}
				// [NODEGROUP [=] nodegroup_id]
				if($this->hasNodegroup()) {
					$string .= " nodegroup " . $this->getNodegroup();
				}
				// [WAIT]
				if($this->getWaitFlag()) {
					$string .= " wait";
				}
				// [COMMENT [=] 'string']
				if($this->hasComment()) {
					$string .= " comment '" . escape_quotes($this->getComment(), QUOTE_STYLE_SINGLE) . "'";
				}
			}
			// InnoDB and NDB: [ENGINE [=] engine_name]
			$string .= " engine {$engine}";
		}
		return $string;
	}
}
