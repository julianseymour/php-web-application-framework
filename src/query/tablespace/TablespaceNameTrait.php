<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\tablespace;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait TablespaceNameTrait
{

	protected $tablespaceName;

	public function tablespace($name)
	{
		$this->setTablespaceName($name);
		return $this;
	}

	public function setTablespaceName($name)
	{
		return $this->tablespaceName = $name;
	}

	public function hasTablespaceName()
	{
		return isset($this->tablespaceName);
	}

	public function getTablespaceName()
	{
		$f = __METHOD__; //"TablespaceNameTrait(".static::getShortClass().")->getTablespaceName()";
		if (! $this->hasTablespaceName()) {
			Debug::error("{$f} tablespace name is undefined");
		}
		return $this->tablespaceName;
	}
}