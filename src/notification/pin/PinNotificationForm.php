<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification\pin;

use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;

class PinNotificationForm extends SortNotificationForm{

	public static function getActionAttributeStatic(): ?string{
		return '/pin';
	}

	public static function getLabelInnerHTML(){
		$p = new ConcatenateCommand("📌", _("Pin"));
		return $p;
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_UPDATE
		];
	}

	protected static function getButtonValueAttributeStatic(){
		return "pin";
	}
}
