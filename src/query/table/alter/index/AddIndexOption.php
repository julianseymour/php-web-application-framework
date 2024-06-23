<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\index;

use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexDefiningTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;
use function JulianSeymour\PHPWebApplicationFramework\release;

class AddIndexOption extends AlterOption{

	use IndexDefiningTrait;

	public function __construct($indexDefinition){
		parent::__construct();
		$this->setIndexDefinition($indexDefinition);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->indexDefinition, $deallocate);
	}

	public function toSQL(): string{
		$index = $this->getIndexDefinition();
		if($index instanceof SQLInterface){
			$index = $index->toSQL();
		}
		return "add {$index}";
	}
}
