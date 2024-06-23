<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\index;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\query\column\VisibilityTrait;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class AlterIndexOption extends AlterOption{

	use IndexNameTrait;
	use VisibilityTrait;

	public function __construct($indexName, $visibility){
		parent::__construct();
		$this->setIndexName($indexName);
		$this->setVisibility($visibility);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->indexName, $deallocate);
		$this->release($this->visibility, $deallocate);
	}

	public function toSQL(): string{
		return "alter index " . $this->getIndexName() . " " . $this->getVisibility();
	}
}
