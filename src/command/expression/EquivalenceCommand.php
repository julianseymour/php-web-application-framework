<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\expression;

class EquivalenceCommand extends BinaryExpressionCommand
{

	public function __construct($lhs, $rhs)
	{
		parent::__construct($lhs, OPERATOR_EQUALSEQUALS, $rhs);
	}
}
