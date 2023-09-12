<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait IndexNameTrait
{

	protected $indexName;

	public function setIndexName($indexName)
	{
		return $this->indexName = $indexName;
	}

	public function hasIndexName()
	{
		return isset($this->indexName);
	}

	public function getIndexName()
	{
		$f = __METHOD__; //"IndexNameTrait(".static::getShortClass().")->getIndexName()";
		if(!$this->hasIndexName()) {
			Debug::error("{$f} index name is undefined");
		}
		return $this->indexName;
	}
}
