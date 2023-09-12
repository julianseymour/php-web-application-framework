<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\MultipleExpressionsTrait;

class DoStatement extends QueryStatement
{

	use MultipleExpressionsTrait;

	public function __construct(...$expressions)
	{
		parent::__construct();
		$this->requirePropertyType("expressions", ExpressionCommand::class);
		if(isset($expressions)) {
			$this->setExpressions($expressions);
		}
	}

	public function getQueryStatementString()
	{
		// DO expr [, expr] ...
		return "do " . implode(',', $this->getExpressions());
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->properties);
		unset($this->propertyTypes);
	}
}
