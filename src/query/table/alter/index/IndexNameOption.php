<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\index;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

abstract class IndexNameOption extends AlterOption{

	use IndexNameTrait;

	public function __construct($indexName){
		parent::__construct();
		$this->setIndexName($indexName);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->indexName, $deallocate);
	}
}
