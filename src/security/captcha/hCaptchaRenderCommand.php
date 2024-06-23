<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\captcha;

use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;

class hCaptchaRenderCommand extends ElementCommand{

	public static function getCommandId(): string{
		return "hcaptcha.render";
	}

	public function toJavaScript(): string{
		return $this->getCommandId() . "()";
	}
}
