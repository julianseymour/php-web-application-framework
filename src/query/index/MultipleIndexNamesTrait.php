<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use JulianSeymour\PHPWebApplicationFramework\common\arr\ArrayPropertyTrait;

trait MultipleIndexNamesTrait
{

	use ArrayPropertyTrait;

	public function setIndexNames($indexNames)
	{
		return $this->setArrayProperty("indexNames", $indexNames);
	}

	public function pushIndexNames(...$indexNames)
	{
		return $this->pushArrayProperty("indexNames", ...$indexNames);
	}

	public function hasIndexNames()
	{
		return $this->hasArrayProperty("indexNames");
	}

	public function getIndexNames()
	{
		return $this->getProperty("indexNames");
	}

	public function mergeIndexNames($indexNames)
	{
		return $this->mergeArrayProperty("indexNames", $indexNames);
	}

	public function withIndexNames($indexNames)
	{
		$this->setIndexNames($indexNames);
		return $this;
	}
}
