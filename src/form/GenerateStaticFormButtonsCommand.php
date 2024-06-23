<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class GenerateStaticFormButtonsCommand extends GenerateFormButtonsCommand{
	
	public static function extractAnyway():bool{
		return true;
	}

	public static function getCommandId(): string{
		return "generateStaticFormButtons";
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}
}
