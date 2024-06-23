<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\tablespace;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use function JulianSeymour\PHPWebApplicationFramework\hasMinimumMySQLVersion;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\CommentTrait;

class CreateTablespaceStatement extends DefineTablespaceStatement{

	use CommentTrait;

	protected $extentSizeValue;

	protected $fileBlockSizeValue;

	protected $logfileGroup;

	protected $maxSizeValue;// MAX_SIZE: Currently ignored by MySQL; reserved for possible future use. Has no effect in any release of MySQL 8.0 or MySQL NDB Cluster 8.0, regardless of the storage engine used.

	protected $nodegroupId;

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->comment, $deallocate);
		$this->release($this->extentSizeValue, $deallocate);
		$this->release($this->fileBlockSizeValue, $deallocate);
		$this->release($this->logfileGroup, $deallocate);
		$this->release($this->maxSizeValue, $deallocate);
		$this->release($this->nodegroupId, $deallocate);
	}

	public function setFileBlockSize($value){
		$f = __METHOD__;
		if(is_string($value)){
			if(! preg_match('/^[1-9]+[0-9]*[Kk]?/', $value)){ // Values can be specified in bytes or kilobytes. For example, an 8 kilobyte file block size can be specified as 8192 or 8K.
				Debug::error("{$f} patch mismatch");
			}
		}elseif(!is_int($value)){
			Debug::error("{$f} file block size must be a positive integer");
		}elseif($value < 1){
			Debug::error("{$f} file block size must be positive");
		}
		if($this->hasFileBlockSize()){
			$this->release($this->fileBlockSizeValue);
		}
		return $this->fileBlockSizeValue = $this->claim($value);
	}

	public function hasFileBlockSize():bool{
		return isset($this->fileBlockSizeValue);
	}

	public function getFileBlockSize(){
		$f = __METHOD__;
		if(!$this->hasFileBlockSize()){
			Debug::error("{$f} file block size value is undefined");
		}
		return $this->fileBlockSizeValue;
	}

	public function fileBlockSize($value):CreateTablespaceStatement{
		$this->setFileBlockSize($value);
		return $this;
	}

	public function setLogfileGroup($group){
		$f = __METHOD__;
		if(!is_string($group)){
			Debug::error("{$f} logfile group is undefined");
		}elseif($this->hasLogfileGroup()){
			$this->release($this->logfileGroup);
		}
		return $this->logfileGroup = $this->claim($group);
	}

	public function hasLogfileGroup():bool{
		return isset($this->logfileGroup);
	}

	public function getLogfileGroup(){
		$f = __METHOD__;
		if(!$this->hasLogfileGroup()){
			Debug::error("{$f} logfile group is undefined");
		}
		return $this->logfileGroup;
	}

	public function useLogfileGroup($group):CreateTablespaceStatement{
		$this->setLogfileGroup($group);
		return $this;
	}

	public function setExtentSize($value){
		$f = __METHOD__;
		if(is_string($value)){
			if(! preg_match('/^[1-9]+[0-9]*[TtGgMmKk]?/', $value)){ // When setting EXTENT_SIZE or INITIAL_SIZE, you may optionally follow the number with a one-letter abbreviation for an order of magnitude, similar to those used in my.cnf. Generally, this is one of the letters M (for megabytes) or G (for gigabytes).
				Debug::error("{$f} pattern mismatch");
			}
		}elseif(!is_int($value)){
			Debug::error("{$f} extent size must be a positive integer");
		}elseif($value < 0){ // EXTENT_SIZE is rounded up to the nearest whole multiple of 32K
			Debug::error("{$f} extent size must be positive");
		}
		if($this->hasExtentSize()){
			$this->release($this->extentSizeValue);
		}
		return $this->extentSizeValue = $this->claim($value);
	}

	public function hasExtentSize():bool{
		return isset($this->extentSizeValue);
	}

	public function getExtentSize(){
		$f = __METHOD__;
		if(!$this->hasExtentSize()){
			Debug::error("{$f} extent size is undefined");
		}
		return $this->extentSizeValue;
	}

	public function extentSize($value):CreateTablespaceStatement{
		$this->setExtentSize($value);
		return $this;
	}

	public function setMaxSize($value){
		$f = __METHOD__;
		if(is_string($value)){
			if(! preg_match('/^[1-9]+[0-9]*[TtGgMmKk]?/', $value)){
				Debug::error("{$f} pattern mismatch");
			}
		}elseif(!is_int($value)){
			Debug::error("{$f} max size must be a positive integer");
		}elseif($value < 0){
			Debug::error("{$f} max size must be positive");
		}elseif($this->hasInitialSize() && $value < $this->getInitialSize()){
			Debug::error("{$f} max size cannot exceed initial size");
		}
		if($this->hasMaxSize()){
			$this->release($this->maxSizeValue);
		}
		return $this->maxSizeValue = $this->claim($value);
	}

	public function hasMaxSize():bool{
		return isset($this->maxSizeValue);
	}

	public function getMaxSize(){
		$f = __METHOD__;
		if(!$this->hasMaxSize()){
			Debug::error("{$f} initial size is undefined");
		}
		return $this->maxSizeValue;
	}

	public function maxSize($value):CreateTablespaceStatement{
		$this->setMaxSize($value);
		return $this;
	}

	public function setNodegroup($id){
		$f = __METHOD__;
		if(!is_string($id)){
			Debug::error("{$f} nodegroup ID must be a string");
		}elseif($this->hasNodegroup()){
			$this->release($this->nodegroupId, false);
		}
		return $this->nodegroupId = $this->claim($id);
	}

	public function hasNodegroup():bool{
		return isset($this->nodegroupId);
	}

	public function getNodegroup(){
		$f = __METHOD__;
		if(!$this->hasNodegroup()){
			Debug::error("{$f} nodegroup ID is undefined");
		}
		return $this->nodegroupId;
	}

	public function nodegroup($id):CreateTablespaceStatement{
		$this->setNodegroup($id);
		return $this;
	}

	public function getQueryStatementString():bool{
		// CREATE
		$string = "create ";
		// [UNDO]
		if($this->getUndoFlag() && hasMinimumMySQLVersion("8.0.14")){
			$string .= "undo ";
		}
		// TABLESPACE tablespace_name
		$string .= "tablespace '" . escape_quotes($this->getTablespaceName(), QUOTE_STYLE_SINGLE) . "'";
		$engine = $this->getStorageEngine();
		if($engine === STORAGE_ENGINE_INNODB || $engine === STORAGE_ENGINE_NDB){ // InnoDB and NDB:
		                                                                           // [ADD DATAFILE 'file_name']
			if($this->hasDatafilename()){
				$dfn = escape_quotes($this->getDatafilename(), QUOTE_STYLE_SINGLE);
				$string .= " add datafile '{$dfn}'";
			}
			// [AUTOEXTEND_SIZE [=] value]
			if($this->hasAutoextendSize()){
				$string .= " autoextend_size " . $this->getAutoextendSize();
			}
			if($engine === STORAGE_ENGINE_INNODB){ // InnoDB only:
			                                         // [FILE_BLOCK_SIZE = value]
				if($this->hasFileBlockSizeValue()){
					$string .= " file_block_size " . $this->getFileBlockSize();
				}
				// [ENCRYPTION [=] {'Y' | 'N'}]
				if($this->hasEncryption()){
					$string .= " encryption '" . $this->getEncryption() . "'";
				}
			}elseif($engine === STORAGE_ENGINE_NDB){ // NDB only:
			                                            // USE LOGFILE GROUP logfile_group
				if($this->hasLogfileGroup()){
					$string .= " use logfile group " . $this->getLogfileGroup();
				}
				// [EXTENT_SIZE [=] extent_size]
				if($this->hasExtentSize()){
					$string .= " extent_size " . $this->getExtentSize();
				}
				// [INITIAL_SIZE [=] initial_size]
				if($this->hasInitialSize()){
					$string .= " initial_size " . $this->getInitialSize();
				}
				// [MAX_SIZE [=] max_size]
				if($this->hasMaxSize()){
					$string .= " max_size " . $this->getMaxSize();
				}
				// [NODEGROUP [=] nodegroup_id]
				if($this->hasNodegroup()){
					$string .= " nodegroup " . $this->getNodegroup();
				}
				// [WAIT]
				if($this->getWaitFlag()){
					$string .= " wait";
				}
				// [COMMENT [=] 'string']
				if($this->hasComment()){
					$string .= " comment '" . escape_quotes($this->getComment(), QUOTE_STYLE_SINGLE) . "'";
				}
			}
			// InnoDB and NDB: [ENGINE [=] engine_name]
			$string .= " engine {$engine}";
		}
		return $string;
	}
}
