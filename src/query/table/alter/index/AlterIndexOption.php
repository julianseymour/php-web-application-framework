<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\index;

use JulianSeymour\PHPWebApplicationFramework\query\column\VisibilityTrait;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class AlterIndexOption extends AlterOption
{

	use IndexNameTrait;
	use VisibilityTrait;

	public function __construct($indexName, $visibility)
	{
		parent::__construct();
		$this->setIndexName($indexName);
		$this->setVisibility($visibility);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->indexName);
		unset($this->visibility);
	}

	public function toSQL(): string
	{
		return "alter index " . $this->getIndexName() . " " . $this->getVisibility();
	}
}
