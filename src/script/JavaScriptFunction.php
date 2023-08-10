<?php
namespace JulianSeymour\PHPWebApplicationFramework\script;

use JulianSeymour\PHPWebApplicationFramework\command\Routine;

class JavaScriptFunction extends Routine
{

	public function __toString(): string
	{
		return $this->toJavaScript();
	}
}
