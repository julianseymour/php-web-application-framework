<?php
namespace JulianSeymour\PHPWebApplicationFramework\data\columns;

use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;

trait SortNameColumnTrait
{

	public static function getSortNameDatum($name = "sortName")
	{
		$sort_name = new TextDatum($name);
		$sort_name->setAlphanumeric(true);
		$sort_name->setHumanReadableName(_("Invisible string for sorting"));
		$sort_name->setAdminInterfaceFlag(true);
		$sort_name->setNullable(true);
		return $sort_name;
	}

	public function setSortName($name)
	{
		return $this->setColumnValue("sortName", $name);
	}

	public function hasSortName()
	{
		return $this->hasColumnValue("sortName");
	}

	public function getSortName()
	{
		return $this->getColumnValue("sortName");
	}
}
