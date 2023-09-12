<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\role;

use JulianSeymour\PHPWebApplicationFramework\query\IfNotExistsFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;

class CreateRoleStatement extends QueryStatement
{

	use IfNotExistsFlagBearingTrait;
	use MultipleRolesTrait;

	public function __construct(...$roles)
	{
		parent::__construct();
		$this->requirePropertyType("roles", DatabaseRoleData::class);
		if(isset($roles)) {
			$this->setRoles($roles);
		}
	}

	public function getQueryStatementString()
	{
		// CREATE ROLE [IF NOT EXISTS] role [, role ] ...
		$string = "create role ";
		if($this->getIfNotExistsFlag()) {
			$string .= "if not exists ";
		}
		$string .= implode(',', $this->getRoles());
		return $string;
	}
}
