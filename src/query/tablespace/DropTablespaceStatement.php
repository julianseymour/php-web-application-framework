<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\tablespace;

class DropTablespaceStatement extends TablespaceStatement
{

	public function getQueryStatementString()
	{
		// DROP
		$string = "drop ";
		// [UNDO]
		if($this->getUndoFlag()){
			$string .= "undo ";
		}
		// TABLESPACE tablespace_name
		$string .= "tablespace " . $this->getTablespaceName();
		// [ENGINE [=] engine_name]
		if($this->hasStorageEngine()){
			$string .= " engine " . $this->getStorageEngine();
		}
		return $string;
	}
}
