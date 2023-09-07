<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\data;

abstract class ForeignDataStructureCommand extends DataStructureCommand{

	public function __construct($context, $index){
		parent::__construct($context);
		$this->setColumnName($index);
	}
}
