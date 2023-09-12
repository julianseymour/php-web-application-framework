<?php
namespace JulianSeymour\PHPWebApplicationFramework\event;

abstract class SetForeignDataStructureEvent extends Event
{

	public function __construct($type, $columnName, $struct, $properties = null)
	{
		if($properties === null) {
			$properties = [];
		}
		$properties['columnName'] = $columnName;
		$properties['data'] = $struct;
		parent::__construct($type, $properties);
	}

	public function getColumnName(): ?string
	{
		return $this->getProperty("columnName");
	}

	public function getForeignDataStructure()
	{
		return $this->getProperty("data");
	}
}
