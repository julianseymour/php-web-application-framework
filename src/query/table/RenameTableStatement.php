<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use JulianSeymour\PHPWebApplicationFramework\common\MultipleNameChangesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use function JulianSeymour\PHPWebApplicationFramework\release;

class RenameTableStatement extends QueryStatement{

	use MultipleNameChangesTrait;

	public function getQueryStatementString(): string{
		// RENAME TABLE tbl_name TO new_tbl_name [, tbl_name2 TO new_tbl_name2] ...
		$string = "rename table ";
		$i = 0;
		foreach($this->getNameChanges() as $oldname => $newname){
			if($i ++ > 0){
				$string .= ",";
			}
			$string .= "{$oldname} to {$newname}";
		}
		return $string;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->nameChanges, $deallocate);
	}
}
