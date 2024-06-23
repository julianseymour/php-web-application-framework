<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

use JulianSeymour\PHPWebApplicationFramework\query\index\IndexDefinition;

class UniqueConstraint extends IndexDefiningConstraint
{

	public function toSQL(): string
	{
		$id = $this->getIndexDefinition();
		if($id instanceof IndexDefinition){
			$id = $id->toSQL();
		}
		return parent::toSQL() . " unique {$id}";
	}
}
