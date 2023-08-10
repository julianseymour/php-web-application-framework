<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\role;

use JulianSeymour\PHPWebApplicationFramework\query\IfExistsFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;

class DropRoleStatement extends QueryStatement
{

	use IfExistsFlagBearingTrait;
	use MultipleRolesTrait;

	public function __construct(...$roles)
	{
		parent::__construct();
		$this->requirePropertyType("roles", DatabaseRoleData::class);
		if (isset($roles)) {
			$this->setRoles($roles);
		}
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"if exists"
		]);
	}

	public function getQueryStatementString()
	{
		// DROP ROLE [IF EXISTS] role [, role ] ...
		$string = "drop role ";
		if ($this->getIfExistsFlag()) {
			$string .= "if exists ";
		}
		$string .= implode(',', $this->getRoles());
		return $string;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->properties);
		unset($this->propertyTypes);
	}
}
