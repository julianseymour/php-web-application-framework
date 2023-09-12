<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\CommentTrait;
use JulianSeymour\PHPWebApplicationFramework\query\StorageEngineTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;
use JulianSeymour\PHPWebApplicationFramework\query\tablespace\TablespaceNameTrait;

abstract class AbstractTableOptions extends AlterOption
{

	use CommentTrait;
	use StorageEngineTrait;
	use TablespaceNameTrait;

	protected $dataDirectoryName;

	protected $indexDirectoryName;

	protected $minimumRowCount;

	protected $maximumRowCount;

	public function dispose(): void
	{
		parent::dispose();
		unset($this->comment);
		unset($this->dataDirectoryName);
		unset($this->indexDirectoryName);
		unset($this->maximumRowCount);
		unset($this->minimumRowCount);
		unset($this->storageEngineName);
		unset($this->tablespaceName);
	}

	public function dataDirectory($name)
	{
		$this->setDataDirectoryName($name);
		return $this;
	}

	public function indexDirectory($name)
	{
		$this->setIndexDirectoryName($name);
		return $this;
	}

	public function setDataDirectoryName($name)
	{
		return $this->dataDirectoryName = $name;
	}

	public function hasDataDirectoryName()
	{
		return isset($this->dataDirectoryName);
	}

	public function getDataDirectoryName()
	{
		$f = __METHOD__; //AbstractTableOptions::getShortClass()."(".static::getShortClass().")->getDataDirectoryName()";
		if(!$this->hasDataDirectoryName()) {
			Debug::error("{$f} data directory name is undefined");
		}
		return $this->dataDirectoryName;
	}

	public function setIndexDirectoryName($name)
	{
		return $this->indexDirectoryName = $name;
	}

	public function hasIndexDirectoryName()
	{
		return isset($this->indexDirectoryName);
	}

	public function getIndexDirectoryName()
	{
		$f = __METHOD__; //AbstractTableOptions::getShortClass()."(".static::getShortClass().")->getIndexDirectoryName()";
		if(!$this->hasIndexDirectoryName()) {
			Debug::error("{$f} index directory name is undefined");
		}
		return $this->indexDirectoryName;
	}

	public function maxRows($count)
	{
		$this->setMaximumRowCount($count);
		return $this;
	}

	public function minRows($count)
	{
		$this->setMinimumRowCount($count);
		return $this;
	}

	public function setMinimumRowCount($count)
	{
		$f = __METHOD__; //AbstractTableOptions::getShortClass()."(".static::getShortClass().")->setMinimumRowCount()";
		if($count === null) {
			unset($this->minimumRowCount);
			return null;
		}elseif(!is_int($count)) {
			Debug::error("{$f} minimum row count must be a positive integer");
		}elseif($count <= 0) {
			Debug::error("{$f} minimum row count must be positive");
		}elseif($this->hasMaximumRowCount() && $count > $this->getMaximumRowCount()) {
			Debug::error("{$f} minimum row count cannot exceed the maximum");
		}
		return $this->minimumRowCount = $count;
	}

	public function hasMinimumRowCount()
	{
		return isset($this->minimumRowCount) && is_int($this->minimumRowCount) && $this->minimumRowCount > 0;
	}

	public function getMinimumRowCount()
	{
		$f = __METHOD__; //AbstractTableOptions::getShortClass()."(".static::getShortClass().")->getMinimumRowCount()";
		if(!$this->hasMinimumRowCount()) {
			Debug::error("{$f} minimum row count is undefined");
		}
		return $this->minimumRowCount;
	}

	public function setMaximumRowCount($count)
	{
		$f = __METHOD__; //AbstractTableOptions::getShortClass()."(".static::getShortClass().")->setMaximumRowCount()";
		if($count === null) {
			unset($this->maximumRowCount);
			return null;
		}elseif(!is_int($count)) {
			Debug::error("{$f} maximum row count must be a positive integer");
		}elseif($count <= 0) {
			Debug::error("{$f} maximum row count must be positive");
		}elseif($this->hasMinimumRowCount() && $count < $this->getMinimumRowCount()) {
			Debug::error("{$f} maximum row count cannot be less than minimum");
		}
		return $this->maximumRowCount = $count;
	}

	public function hasMaximumRowCount()
	{
		return isset($this->maximumRowCount) && is_int($this->maximumRowCount) && $this->maximumRowCount > 0;
	}

	public function getMaximumRowCount()
	{
		$f = __METHOD__; //AbstractTableOptions::getShortClass()."(".static::getShortClass().")->getMaximumRowCount()";
		if(!$this->hasMaximumRowCount()) {
			Debug::error("{$f} maximum row count is undefined");
		}
		return $this->maximumRowCount;
	}
}
