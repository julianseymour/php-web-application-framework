<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\table\AbstractTableOptions;

trait StorageEngineTrait
{

	protected $storageEngineName;

	public function engine($name)
	{
		$this->setStorageEngine($name);
		return $this;
	}

	public function setStorageEngine($name)
	{
		$f = __METHOD__; //"StorageEngineTrait(".static::getShortClass().")->setStorageEngine()";
		if($name == null) {
			unset($this->storageEngineName);
			return null;
		}elseif(!is_string($name)) {
			Debug::error("{$f} storage engine name must be a string");
		}
		return $this->storageEngineName = $name;
	}

	public function hasStorageEngine()
	{
		return isset($this->storageEngineName);
	}

	public function getStorageEngine()
	{
		$f = __METHOD__; //"StorageEngineTrait(".static::getShortClass().")->getStorageEngine()";
		if(!$this->hasStorageEngine()) {
			Debug::error("{$f} storage engine name is undefined");
		}
		return $this->storageEngineName;
	}
}