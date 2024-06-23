<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\CommentTrait;
use JulianSeymour\PHPWebApplicationFramework\query\StorageEngineTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;
use JulianSeymour\PHPWebApplicationFramework\query\tablespace\TablespaceNameTrait;

abstract class AbstractTableOptions extends AlterOption{

	use CommentTrait;
	use StorageEngineTrait;
	use TablespaceNameTrait;

	protected $dataDirectoryName;

	protected $indexDirectoryName;

	protected $minimumRowCount;

	protected $maximumRowCount;

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->comment, $deallocate);
		$this->release($this->dataDirectoryName, $deallocate);
		$this->release($this->indexDirectoryName, $deallocate);
		$this->release($this->maximumRowCount, $deallocate);
		$this->release($this->minimumRowCount, $deallocate);
		$this->release($this->storageEngineName, $deallocate);
		$this->release($this->tablespaceName, $deallocate);
	}

	public function dataDirectory($name):AbstractTableOptions{
		$this->setDataDirectoryName($name);
		return $this;
	}

	public function indexDirectory($name):AbstractTableOptions{
		$this->setIndexDirectoryName($name);
		return $this;
	}

	public function setDataDirectoryName($name){
		if($this->hasDataDirectoryName()){
			$this->release($this->dataDirectoryName);
		}
		return $this->dataDirectoryName = $this->claim($name);
	}

	public function hasDataDirectoryName():bool{
		return isset($this->dataDirectoryName);
	}

	public function getDataDirectoryName(){
		$f = __METHOD__;
		if(!$this->hasDataDirectoryName()){
			Debug::error("{$f} data directory name is undefined");
		}
		return $this->dataDirectoryName;
	}

	public function setIndexDirectoryName($name){
		if($this->hasIndexDirectoryName()){
			$this->release($this->indexDirectoryName);
		}
		return $this->indexDirectoryName = $this->claim($name);
	}

	public function hasIndexDirectoryName():bool{
		return isset($this->indexDirectoryName);
	}

	public function getIndexDirectoryName(){
		$f = __METHOD__;
		if(!$this->hasIndexDirectoryName()){
			Debug::error("{$f} index directory name is undefined");
		}
		return $this->indexDirectoryName;
	}

	public function maxRows($count):AbstractTableOptions{
		$this->setMaximumRowCount($count);
		return $this;
	}

	public function minRows($count):AbstractTableOptions{
		$this->setMinimumRowCount($count);
		return $this;
	}

	public function setMinimumRowCount($count){
		$f = __METHOD__;
		if(!is_int($count)){
			Debug::error("{$f} minimum row count must be a positive integer");
		}elseif($count <= 0){
			Debug::error("{$f} minimum row count must be positive");
		}elseif($this->hasMaximumRowCount() && $count > $this->getMaximumRowCount()){
			Debug::error("{$f} minimum row count cannot exceed the maximum");
		}elseif($this->hasMinimumRowCount()){
			$this->release($this->minimumRowCount);
		}
		return $this->minimumRowCount = $this->claim($count);
	}

	public function hasMinimumRowCount():bool{
		return isset($this->minimumRowCount) && is_int($this->minimumRowCount) && $this->minimumRowCount > 0;
	}

	public function getMinimumRowCount(){
		$f = __METHOD__;
		if(!$this->hasMinimumRowCount()){
			Debug::error("{$f} minimum row count is undefined");
		}
		return $this->minimumRowCount;
	}

	public function setMaximumRowCount($count){
		$f = __METHOD__;
		if(!is_int($count)){
			Debug::error("{$f} maximum row count must be a positive integer");
		}elseif($count <= 0){
			Debug::error("{$f} maximum row count must be positive");
		}elseif($this->hasMinimumRowCount() && $count < $this->getMinimumRowCount()){
			Debug::error("{$f} maximum row count cannot be less than minimum");
		}elseif($this->hasMaximumRowCount()){
			$this->release($this->maximumRowCount);
		}
		return $this->maximumRowCount = $this->claim($count);
	}

	public function hasMaximumRowCount():bool{
		return isset($this->maximumRowCount) && is_int($this->maximumRowCount) && $this->maximumRowCount > 0;
	}

	public function getMaximumRowCount(){
		$f = __METHOD__;
		if(!$this->hasMaximumRowCount()){
			Debug::error("{$f} maximum row count is undefined");
		}
		return $this->maximumRowCount;
	}
}
