<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayKeyProviderInterface;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexDefiningTrait;

abstract class IndexDefiningConstraint extends Constraint implements ArrayKeyProviderInterface{

	use IndexDefiningTrait;

	public function __construct($indexDefinition){
		parent::__construct();
		$this->setIndexDefinition($indexDefinition);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->indexDefinition, $deallocate);
	}

	public function getArrayKey(int $count){
		return $this->getIndexDefinition()->getIndexName();
	}
}
