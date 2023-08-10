<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

use JulianSeymour\PHPWebApplicationFramework\common\arr\ArrayKeyProviderInterface;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexDefiningTrait;

abstract class IndexDefiningConstraint extends Constraint implements ArrayKeyProviderInterface
{

	use IndexDefiningTrait;

	public function __construct($indexDefinition)
	{
		parent::__construct();
		$this->setIndexDefinition($indexDefinition);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->indexDefinition);
	}

	public function getArrayKey(int $count)
	{
		return $this->getIndexDefinition()->getIndexName();
	}
}
