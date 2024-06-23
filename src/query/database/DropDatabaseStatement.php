<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\database;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\query\IfExistsFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;

class DropDatabaseStatement extends QueryStatement{

	use DatabaseNameTrait;
	use IfExistsFlagBearingTrait;

	public function __construct($databaseName = null){
		parent::__construct();
		if($databaseName !== null){
			$this->setDatabaseName($databaseName);
		}
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"if exists"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"if exists"
		]);
	}
	
	public function getQueryStatementString(){
		// DROP {DATABASE | SCHEMA} [IF EXISTS] db_name
		$string = "drop database ";
		if($this->getIfExistsFlag()){
			$string .= "if exists ";
		}
		$string .= $this->getDatabaseName();
		return $string;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->databaseName, $deallocate);
	}
}
