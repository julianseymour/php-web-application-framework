<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class GetForeignDataStructureCountCommand extends ForeignDataStructureCommand implements ValueReturningCommandInterface{

	public static function getCommandId(): string{
		return "getForeignDataStructureCount";
	}

	public function evaluate(?array $params = null){
		return $this->getDataStructure()->getForeignDataStructureCount($this->getColumnName());
	}

	public function toJavaScript(): string{
		ErrorMessage::unimplemented(f());
	}
}
