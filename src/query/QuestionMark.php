<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;

class QuestionMark extends ExpressionCommand implements SQLInterface
{

	public function toSQL(): string
	{
		return "?";
	}

	public static function getCommandId(): string
	{
		return "?";
	}

	public function evaluate(?array $params = null)
	{
		return "?";
	}
}
