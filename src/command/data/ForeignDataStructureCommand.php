<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\data;

abstract class ForeignDataStructureCommand extends DataStructureCommand{

	public function __construct($context=null, $column_name=null){
		parent::__construct($context);
		if($column_name !== null){
			$this->setColumnName($column_name);
		}
	}
}
