<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\database\DatabaseNameTrait;

trait FullTableNameTrait{

	use DatabaseNameTrait;
	use TableNameTrait;

	public function unpackTableName($dbtable){
		$f = __METHOD__;
		if(!isset($dbtable)) {
			Debug::error("{$f} received null parameter");
		}
		$count = count($dbtable);
		switch ($count) {
			case 1:
				$this->setTableName($dbtable[0]);
				break;
			case 2:
				$this->setDatabaseName($dbtable[0]);
				$this->setTableName($dbtable[1]);
				break;
			default:
				Debug::error("{$f} {$count} parameters");
		}
	}
}