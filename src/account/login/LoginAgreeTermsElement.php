<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\login;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\pwa\ProgressiveHyperlinkElement;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use Exception;

class LoginAgreeTermsElement extends DivElement{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("text-align_center");
		$this->setStyleProperty("margin-top", "0.5rem");
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try {
			$a1 = new ProgressiveHyperlinkElement();
			$a1->setInnerHTML(_("Terms of service"));
			$a1->setHrefAttribute('/terms');
			$innerHTML = substitute(_("By signing in, you agree to our %1%"), $a1);
			$this->setInnerHTML($innerHTML);
			return [
				$innerHTML
			];
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
