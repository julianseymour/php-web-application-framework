<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait IndexDefiningTrait
{

	protected $indexDefinition;

	public function setIndexDefinition($indexDefinition)
	{
		$f = __METHOD__; //"IndexDefiningTrait(".static::getShortClass().")->setIndexDefinition()";
		if (! $indexDefinition instanceof IndexDefinition) {
			Debug::error("{$f} input parameter must be an index definition");
		}
		return $this->indexDefinition = $indexDefinition;
	}

	public function hasIndexDefintion()
	{
		return isset($this->indexDefinition);
	}

	public function getIndexDefinition()
	{
		$f = __METHOD__; //"IndexDefiningTrait(".static::getShortClass().")->getIndexDefintion()";
		if (! $this->hasIndexDefintion()) {
			Debug::error("{$f} index definition is undefined");
		}
		return $this->indexDefinition;
	}
}