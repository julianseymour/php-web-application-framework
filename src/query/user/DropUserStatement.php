<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\user;

use JulianSeymour\PHPWebApplicationFramework\query\IfExistsFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;

class DropUserStatement extends QueryStatement
{

	use IfExistsFlagBearingTrait;
	use MultipleDatabaseUserDefinitionsTrait;

	public function __construct(...$users)
	{
		parent::__construct();
		if (isset($users)) {
			$this->setUsers($users);
		}
	}

	public function getQueryStatementString()
	{
		// DROP USER [IF EXISTS] user [, user] ...
		$string = "drop user ";
		if ($this->getIfExistsFlag()) {
			$string .= "if exists ";
		}
		$count = 0;
		foreach ($this->getUsers() as $user) {
			if ($count > 0) {
				$string .= ",";
			}
			$string .= $user->toSQL();
			$count ++;
		}
		return $string;
	}
}