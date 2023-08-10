<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\insert;

class ReplaceStatement extends AbstractInsertStatement
{

	public function getQueryStatementString(): string
	{
		return "replace " . $this->getInsertQueryStatementString() . $this->getValueAssignmentString();
	}
}
