<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

use JulianSeymour\PHPWebApplicationFramework\query\index\IndexDefinition;

class PrimaryKeyConstraint extends IndexDefiningConstraint
{

	public function toSQL(): string
	{
		$indexDefinition = $this->getIndexDefinition();
		$backup = null;
		if ($indexDefinition instanceof IndexDefinition) {
			$backup = $indexDefinition->getHideIndexNameFlag();
			$indexDefinition->setHideIndexNameFlag(true);
			$id = $indexDefinition->toSQL();
		} else {
			$id = $indexDefinition;
		}
		$string = parent::toSQL() . " primary key {$id}";
		if ($indexDefinition instanceof IndexDefinition) {
			$indexDefinition->setHideIndexNameFlag($backup);
		}
		return $string;
	}
}
