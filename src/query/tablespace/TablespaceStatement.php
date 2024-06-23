<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\tablespace;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\StorageEngineTrait;

abstract class TablespaceStatement extends QueryStatement{

	use StorageEngineTrait;
	use TablespaceNameTrait;

	public function __construct($tablespaceName = null){
		parent::__construct();
		if($tablespaceName !== null){
			$this->setTablespaceName($tablespaceName);
		}
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->storageEngineName, $deallocate);
		$this->release($this->tablespaceName, $deallocate);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"undo"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"undo"
		]);
	}
	
	public function setUndoFlag(bool $value = true):bool{
		if($value){
			$this->setRequiredMySQLVersion("8.0.14");
		}
		return $this->setFlag("undo", $value);
	}

	public function getUndoFlag():bool{
		return $this->getFlag("undo");
	}

	public function undo(bool $value=true):TablespaceStatement{
		$this->setUndoFlag($value);
		return $this;
	}
}
