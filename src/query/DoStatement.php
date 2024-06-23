<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\MultipleExpressionsTrait;
use function JulianSeymour\PHPWebApplicationFramework\release;

class DoStatement extends QueryStatement{

	use MultipleExpressionsTrait;

	public function __construct(...$expressions){
		parent::__construct();
		$this->requirePropertyType("expressions", ExpressionCommand::class);
		if(isset($expressions)){
			$this->setExpressions($expressions);
		}
	}

	public function getQueryStatementString():string{
		return "do ".implode(',', $this->getExpressions());
	}
}
