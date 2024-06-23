<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

ErrorMessage::deprecated(__FILE__);

class QuestionMark extends ExpressionCommand implements SQLInterface{

	public function toSQL(): string{
		return "?";
	}

	public static function getCommandId(): string{
		return "?";
	}

	public function evaluate(?array $params = null){
		return "?";
	}
}
