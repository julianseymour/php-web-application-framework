<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;

trait MultipleIndexDefiningTrait
{

	use ArrayPropertyTrait;

	public function setIndexDefinitions($indexDefinitions)
	{
		return $this->setArrayProperty("indexDefinitions", $indexDefinitions);
	}

	public function hasIndexDefinitions()
	{
		return $this->hasArrayProperty("indexDefinitions");
	}

	public function pushIndexDefinitions(...$indexDefinitions)
	{
		return $this->pushArrayProperty("indexDefinitions", ...$indexDefinitions);
	}

	public function mergeIndexDefinitions($indexDefinitions)
	{
		return $this->mergeArrayProperty("indexDefinitions", $indexDefinitions);
	}

	public function getIndexDefinitions()
	{
		return $this->getProperty("indexDefinitions");
	}

	public function withIndexDefinitions($indexDefinitions)
	{
		$this->setIndexDefinitions($indexDefinitions);
		return $this;
	}
}
