<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\index;

use JulianSeymour\PHPWebApplicationFramework\query\index\IndexNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

abstract class IndexNameOption extends AlterOption
{

	use IndexNameTrait;

	public function __construct($indexName)
	{
		parent::__construct();
		$this->setIndexName($indexName);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->indexName);
	}
}
