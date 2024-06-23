<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\register;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\pwa\ProgressiveHyperlinkElement;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use Exception;

class SuccessfulRegistrationNotice extends DivElement{

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("lenap_pilf");
		$this->setIdAttribute("lenap_pilf");
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$context = $this->getContext();
			$a = new ProgressiveHyperlinkElement();
			$a->setHrefAttribute('/contact_us');
			$a->setInnerHTML(_("Contact us"));
			$string = substitute(_("You are now registered. An email has been sent to %1% containing instructions on how to activate your account. If cannot find the email in your inbox, check your spam folder. If you cannot find it in your spam folder, please %2%"), getInputParameter('emailAddress'), $a);
			$this->appendChild($string);
			$flip_label = new LabelElement();
			$flip_label->addClassAttribute("flip_label");
			$flip_label->setForAttribute("login_panel_flip");
			$flip_label->setInnerHTML(_("Login"));
			$this->appendChild($flip_label);
			return $this->hasChildNodes() ? $this->getChildNodes() : [];
		}catch(Exception $x){
			x($f, $x);
		}
	}
}


