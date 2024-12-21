<?php

namespace JulianSeymour\PHPWebApplicationFramework\data\columns;

use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;

trait SortNameColumnTrait{

	private static function getSortNameDatum(string $name = "sortName"){
		$sort_name = new TextDatum($name);
		$sort_name->setAlphanumeric(true);
		$sort_name->setHumanReadableName(_("Invisible string for sorting"));
		$sort_name->setAdminInterfaceFlag(true);
		$sort_name->setNullable(true);
		return $sort_name;
	}

	public function setSortName(string $name):string{
		return $this->setColumnValue("sortName", $name);
	}

	public function hasSortName():bool{
		return $this->hasColumnValue("sortName");
	}

	public function getSortName():string{
		return $this->getColumnValue("sortName");
	}
}
