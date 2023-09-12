<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\tablespace;

use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\StorageEngineTrait;

abstract class TablespaceStatement extends QueryStatement
{

	use StorageEngineTrait;
	use TablespaceNameTrait;

	public function __construct($tablespaceName = null)
	{
		parent::__construct();
		if($tablespaceName !== null) {
			$this->setTablespaceName($tablespaceName);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->storageEngineName);
		unset($this->tablespaceName);
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"undo"
		]);
	}

	public function setUndoFlag($value = true)
	{
		if($value) {
			$this->setRequiredMySQLVersion("8.0.14");
		}
		return $this->setFlag("undo", $value);
	}

	public function getUndoFlag()
	{
		return $this->getFlag("undo");
	}

	public function undo()
	{
		$this->setUndoFlag(true);
		return $this;
	}
}
