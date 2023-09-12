<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait IndexTypeTrait
{

	protected $indexType;

	public function setIndexType($type)
	{
		$f = __METHOD__; //"IndexTypeTrait(".static::getShortClass().")->setIndexType()";
		if($type == null) {
			unset($this->indexType);
			return null;
		}elseif(!is_string($type)) {
			Debug::error("{$f} index type must be a string");
		}
		$type = strtolower($type);
		switch ($type) {
			case INDEX_TYPE_BTREE:
			case INDEX_TYPE_FULLTEXT:
			case INDEX_TYPE_HASH:
			case INDEX_TYPE_SPATIAL:
				return $this->indexType = $type;
			default:
				Debug::error("{$f} invalid index type \"{$type}\"");
		}
	}

	public function getIndexType()
	{
		$f = __METHOD__; //"IndexTypeTrait(".static::getShortClass().")->getIndexType()";
		if(!$this->hasIndexType()) {
			Debug::error("{$f} index type is undefined");
		}
		return $this->indexType;
	}

	public function hasIndexType()
	{
		return isset($this->indexType);
	}

	public function withIndexType($type)
	{
		$this->setIndexType($type);
		return $this;
	}
}